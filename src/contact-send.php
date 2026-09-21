<?php
// contact-send.php — THE ONE FILE IN THIS TREE THAT IS NOT WRITTEN IN THE
// PHP 5.2-SAFE DIALECT, and the only file that may ever become one.
//
// WHY THE QUARANTINE EXISTS (RESEARCH A-3). Every other file here avoids
// closures, namespaces, short array syntax and __DIR__, because D4-01 — the
// move off the 5.2 handler — is reversible and its own reversibility note
// says a rollback is costly but possible. The failure mode of a rollback is
// what decides where the modern code is allowed to live: a 5.2 interpreter
// meeting a namespaced file fails at COMPILE time, before a single byte of
// output. Not a warning, not a 500 on one request — the file does not parse.
// Put a `use` statement in footer.php and a rollback takes all 20 pages down
// at once, silently, with no page left to explain it. Confine it to this one
// POST endpoint and a rollback breaks the contact form and nothing else, on a
// site whose phone numbers are on every page and are the faster route anyway.
//
// THE RULE THAT KEEPS THAT TRUE: no include chain from header.php or
// footer.php may reach this file, or reach src/vendor/phpmailer/ when 04-05
// vendors it. This file includes the chrome; the chrome never includes this
// file. A plan-level grep asserts it in both directions.
//
// ANTI-ANALOG — site-current/mailer.php:84-99, the handler this replaces.
// Three defects, all three live on the production site today, none repeated
// here:
//   1. `From:` built by concatenating $_POST — header injection and spoofing
//      in one line (and htmlentities() does not strip CR or LF);
//   2. the return value of the bare send discarded — the handler never
//      learns that it failed;
//   3. header("Location: msg.html") sent unconditionally — every visitor is
//      told their message was sent, including the ones whose was not.
// D4-10 is the rule against the third: the handler owns its errors and never
// redirects blindly.
//
// The legacy send function is described above rather than NAMED, and that is
// not squeamishness. A plan-level acceptance check greps this file for that
// function's call syntax and requires zero matches, which is how the bare-mail
// path is kept from creeping back in during a later edit. A comment quoting
// the string would trip that gate and, worse, would teach whoever fixed the
// gate to weaken it. Same discipline as brand-row.php:34-36 and
// .htaccess:56-62, where a banned literal is likewise discussed without being
// written.
//
// CONTACT-03 / CONTACT-05. Threats T-04-06 (relay-shaped endpoint),
// T-04-07 (reflected XSS), T-04-08 (credential), T-04-09 (leaked exception
// text), T-04-11 (channel outage).

declare(strict_types=1);

// ── POST-ONLY GATE ──────────────────────────────────────────────────────────
// The legacy handler attempts a send on a bare GET, which makes it an
// open-relay-shaped endpoint that anyone can fire by loading a URL. Nothing
// below this line runs for any other method (T-04-06). The HMAC time-trap and
// the per-IP rate limiter land in 04-05 and complete this mitigation; they are
// named there as the rest of this gate, not as new scope.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(405);
    echo "Формулярът се изпраща от страницата с контактите: kontakti.html\n";
    exit;
}

require_once dirname(__FILE__) . '/includes/site-config.php';
require_once dirname(__FILE__) . '/includes/notify.php';
require_once dirname(__FILE__) . '/includes/upload.php';
require_once dirname(__FILE__) . '/includes/spam-guard.php';

// A per-request id that appears in the log and, on the failure page, nowhere.
// It exists so the owner can be told «it failed» and the developer can find
// the matching line, WITHOUT the log carrying any submitted content (P-14,
// T-04-09). Nothing derived from user input is ever logged below.
$torin_rid = bin2hex(random_bytes(4));

// ── OVERSIZED POST, FIRST ───────────────────────────────────────────────────
// When post_max_size is exceeded PHP discards the body: $_POST and $_FILES
// both arrive EMPTY, with no error the handler can see. Without this explicit
// test the visitor gets «fill in the required fields» about a form they did
// fill in, and the handler cannot tell «too big» from «no submission at all».
//
// IT RUNS BEFORE THE SPAM GUARD, AND THE ORDER IS LOAD-BEARING RATHER THAN
// TIDY. 04-02 placed this second, behind the honeypot, which was harmless
// while the decoy was the only check: an emptied body has an empty decoy and
// fell through. It stops being harmless the moment the signed time trap is
// added below — an emptied body also has no `t` field, so a visitor whose five
// photographs exceeded the ceiling would be judged a forger and told nothing
// useful. The guard must never be asked to rule on a request whose fields the
// engine already threw away.
//
// THIS STOPPED BEING A NARROW CASE IN 04-03. The tracer note here read «the
// ceiling is 200M on this host, so this is a narrow case today» — that was the
// raw 8.5 ini. src/.user.ini now holds this directory at post_max_size 60M, a
// deliberate tightening sized above five maximum photographs plus framing, and
// photographs are what a visitor actually posts. Five originals straight off a
// modern phone with the browser script blocked is the shape that lands here.
//
// The logged length is a byte count the SERVER observed, never a submitted
// value (P-14) — it is the one number that distinguishes this branch from a
// bug, and without it the log cannot tell them apart either.
$torin_len = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
if ($torin_len > 0 && count($_POST) === 0) {
    error_log('torin contact ' . $torin_rid . ': refused reason=oversized-post len=' . $torin_len);
    torin_send_fail_page(
        'Файловете са твърде големи и не стигнаха до сървъра. Намалете броя или размера на снимките.',
        []
    );
}

// ── THE SPAM GUARD (CONTACT-03) ─────────────────────────────────────────────
// Decoy, then signed time trap, then per-address throttle — ALL OF IT ABOVE
// FIELD VALIDATION AND ABOVE EVERY OUTBOUND CALL, so a bot never reaches the
// network, never opens the secrets file and never costs this account a photo
// re-encode. includes/spam-guard.php holds the mechanism and the reasoning;
// what lives here is the ORDER and what each rejection shows the visitor.
//
// EVERY REJECTION BELOW WRITES ONE CORRELATION LINE NAMING ITS REASON AND NO
// SUBMITTED CONTENT (P-14, T-04-27). That is not logging hygiene, it is the
// only recourse a false positive has: D4-07 keeps no server-side copy, so a
// wrongly-discarded enquiry is unrecoverable — the log line at least makes it
// recoverable as a FACT, which is what a customer saying «I wrote to you last
// Tuesday» needs somebody to be able to check.
$torin_secret = torin_guard_secret();
if ($torin_secret === '') {
    // The guard is running degraded — it still rejects the sub-three-second
    // bot, because both sides compute the same keyless HMAC, but the token is
    // forgeable by anyone who reads this tree. Said out loud here rather than
    // left as a silence in spam-guard.php, because «the trap is not protecting
    // you today» is exactly the fact that otherwise goes unnoticed for months.
    error_log('torin contact ' . $torin_rid . ': guard degraded reason=no-signing-key');
}

// ── HONEYPOT ────────────────────────────────────────────────────────────────
// A non-empty decoy means stop.
//
// The visitor is NOT told the trap fired, and the response is byte-identical
// to a success: same 303, same destination. A bot that can tell rejection from
// acceptance tunes its way past the trap on the next run; a bot that sees
// success every time has nothing to learn from.
//
// The cost of being wrong here is the reason UI-SPEC C-6 puts autocomplete=off
// on the decoy and this comment repeats it: a false positive silently discards
// a real customer's enquiry and nobody — not the shop, not the customer — ever
// finds out. That is why the trap is a single unambiguous signal and not a
// score.
//
// The decoy test moved into spam-guard.php in 04-05 and is no longer read
// through torin_post(): that helper treats a non-string as ABSENT, which is
// right for a real field and wrong for this one, where posting `website[]=x`
// is nothing a rendering of our form can produce.
if (!torin_verify_honeypot($_POST)) {
    error_log('torin contact ' . $torin_rid . ': guard reject reason=honeypot');
    header('Location: msg.html', true, 303);
    exit;
}

// ── THE SIGNED TIME TRAP ────────────────────────────────────────────────────
// The hidden `t` field carries the moment contact-form.php rendered the page,
// signed. A submission faster than a human could type is a bot; one older than
// a couple of hours is a replay. No session and no cookie is involved, which
// is what keeps the consent question D4-18 closed (T-04-28).
//
// THE TWO REJECTIONS ARE NOT SHOWN THE SAME FACE, and the split is the whole
// judgement in this block:
//
// · «too-fast» is absorbed SILENTLY, with the same 303 to the same page a
//   success gets. A bot that can tell rejection from acceptance tunes past the
//   trap on its next run. Nothing a human does produces this verdict — three
//   seconds is not enough to read seven fields, let alone answer them — so the
//   silent branch carries no risk of swallowing a real enquiry.
//
// · «forged», «malformed» and «stale» get the HONEST page instead, because
//   each of them has a mundane non-bot cause: an intermediary that cached the
//   contact page and served a stale token (RESEARCH P-11), our own signing key
//   rotating between render and submit, a proxy mangling a hidden field, or
//   simply a tab left open over lunch. Telling that visitor «sent» when
//   nothing was sent is the one thing this plan's prohibitions forbid
//   outright, and it would be unobservable on both ends.
$torin_ts_verdict = torin_verify_timestamp(
    (isset($_POST['t']) && is_string($_POST['t'])) ? $_POST['t'] : '',
    $torin_secret,
    3,
    7200
);
if ($torin_ts_verdict === 'too-fast') {
    error_log('torin contact ' . $torin_rid . ': guard reject reason=timestamp-too-fast');
    header('Location: msg.html', true, 303);
    exit;
}
if ($torin_ts_verdict !== 'ok') {
    error_log('torin contact ' . $torin_rid . ': guard reject reason=timestamp-' . $torin_ts_verdict);
    torin_send_fail_page(
        // ONE SENTENCE FOR ALL THREE VERDICTS, and it names the likeliest
        // cause without asserting it. «The form sat open too long» is true for
        // «stale» and a fair guess for the other two; what matters is that the
        // ACTION is the same in every case and is one the visitor can take.
        // Naming the verdict would be telling a bot which knob to turn.
        'Изпращането не беше прието — възможно е формулярът да е стоял отворен твърде дълго. Отворете страницата наново и опитайте отново.',
        []
    );
}

// ── THE PER-ADDRESS THROTTLE ────────────────────────────────────────────────
// Read-only here; the counterpart that WRITES a record runs further down, only
// once a channel has actually accepted the enquiry. spam-guard.php's own
// comment carries the reasoning — counting every POST would throttle the
// visitor who mistyped their email and corrected it ten seconds later, and the
// one re-sending after a failure page that told them to try again.
//
// REMOTE_ADDR, never a forwarded-for header: that header arrives with the
// request, so trusting it would let a bot mint a fresh identity per request
// and exhaust somebody else's quota by claiming their address.
//
// One delivered enquiry per quarter of an hour. A visitor with a genuine
// second enquiry is not turned away — they are told, in the same sentence,
// that the phone is open.
$torin_rl = ['window' => 900, 'max' => 1];
$torin_ip = (isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR']))
    ? $_SERVER['REMOTE_ADDR']
    : '';
if (!torin_rate_limit_ok($torin_ip, $torin_secret, $torin_rl)) {
    error_log('torin contact ' . $torin_rid . ': guard reject reason=rate-limit');
    torin_send_fail_page(
        'Вече получихме запитване от вас. Обадете се на ' . $site['phones'][0] .
        ', ако има какво да добавите, или изпратете нов формуляр след няколко минути.',
        [],
        429
    );
}

// ── VALIDATION ──────────────────────────────────────────────────────────────
// Every message below is the string UI-SPEC's copy contract specifies, and it
// is written HERE and nowhere else: the markup carries the same strings in
// data-err so a client-side validator can render them without re-authoring
// them, which keeps PHP the one writer and makes drift impossible rather than
// unlikely.
$torin_in = [
    'device' => torin_post('device'),
    'fault'  => torin_post('fault'),
    'name'   => torin_post('name'),
    'phone'  => torin_post('phone'),
    'email'  => torin_post('email'),
];

$torin_errors = [];

// Length bounds are enforced here unconditionally and independently of the
// maxlength attributes in the markup. maxlength is a courtesy to a browser;
// this is the enforcement point, and a POST does not have to come from our
// form at all.
if ($torin_in['device'] === '' || torin_chars($torin_in['device']) > 120) {
    $torin_errors['device'] = 'Моля, попълнете полето.';
}
if ($torin_in['fault'] === '' || torin_chars($torin_in['fault']) > 1024) {
    $torin_errors['fault'] = 'Моля, опишете повредата.';
}
if ($torin_in['name'] === '' || torin_chars($torin_in['name']) > 120) {
    $torin_errors['name'] = 'Моля, въведете името си.';
}
if ($torin_in['phone'] === '' || torin_chars($torin_in['phone']) > 40) {
    $torin_errors['phone'] = 'Моля, въведете телефон за връзка.';
}
if ($torin_in['email'] === '') {
    $torin_errors['email'] = 'Моля, въведете имейл.';
} elseif (strlen($torin_in['email']) > 254 || filter_var($torin_in['email'], FILTER_VALIDATE_EMAIL) === false) {
    // FILTER_VALIDATE_EMAIL rather than a hand-rolled regular expression: the
    // address grammar is genuinely hairy and every hand-rolled version this
    // industry has produced rejects somebody's real address. The 254-byte cap
    // is the RFC 5321 path limit and is checked in BYTES on purpose — it is a
    // protocol limit, not a display one.
    $torin_errors['email'] = 'Имейлът изглежда сгрешен. Проверете адреса.';
}

// CONSENT IS REQUIRED AND IS CHECKED LIKE ANY OTHER FIELD (CONTACT-06). The
// declaration on uslovia.html is what the visitor is agreeing to, and the box
// renders unchecked on every render, so arriving here without it is a genuine
// «not agreed», never a lost default.
if (torin_post('consent') === '') {
    $torin_errors['consent'] = 'За да изпратите запитването, трябва да приемете условията.';
}

if (count($torin_errors) > 0) {
    torin_send_fail_page('Проверете отбелязаните полета и опитайте отново.', $torin_errors);
}

// ── PHOTOGRAPHS (CONTACT-05) ────────────────────────────────────────────────
// AFTER the text fields and BEFORE the secrets file, deliberately. A rejected
// photograph should cost a visitor as little as possible, and nothing below
// this line is reached by a submission that is going to be refused anyway.
//
// THE CEILINGS ARE POLICY AND THEY LIVE HERE, not in the pipeline. The
// per-file figure is the one the form's own copy advertises («всяка до 10 MB»)
// and the one src/.user.ini sets at the SAPI boundary; three places, one
// number, and the day it changes all three have to move together. The long
// edge of 1600px matches the browser-side downscale, so a photograph that took
// the scripted path and one that did not arrive looking alike.
//
// EVERY ONE OF THESE IS ENFORCED WHETHER OR NOT THE BROWSER SCRIPT RAN
// (P-6). js/photo-resize.js is an optimisation that saves a visitor's mobile
// data; it is not a limit, because anyone can post straight to this file. No
// check below may ever be weakened on the grounds that the script already did
// it.
$torin_limits = [
    'max_files'       => 5,
    'max_bytes'       => 10 * 1024 * 1024,
    'max_total_bytes' => 50 * 1024 * 1024,
    'max_edge'        => 1600,
];
$torin_uploads = torin_collect_uploads($_FILES['photos'] ?? null, $torin_limits);
$torin_photos = $torin_uploads['paths'];

// A FATAL BETWEEN HERE AND THE RELEASE BELOW WOULD OTHERWISE LEAVE A
// VISITOR'S PHOTOGRAPH ON DISK. D4-07 buys this site a short privacy note and
// no retention obligation precisely because nothing is kept; one surviving
// temp file makes that a false statement on the terms page, in a directory
// nobody ever looks at. The handler registers the cleanup with the engine so
// the guarantee does not depend on this file reaching its own last line.
// torin_release_uploads() is idempotent, so the ordinary path below still
// unlinks immediately and this is a no-op.
register_shutdown_function('torin_release_uploads', $torin_photos);

// A REFUSED PHOTOGRAPH IS A FIELD-LEVEL ERROR ON THE PHOTO CONTROL, never a
// nameless failure (T-04-17). The reason names the photo by its POSITION in
// the list and never by its filename — that string arrived with the request
// and would be landing in a page (T-04-07).
//
// Only the first reason is shown. The fail page renders one line per field,
// and the second refusal is almost always the same refusal; the visitor needs
// to know what to change, not a catalogue.
if (count($torin_uploads['errors']) > 0) {
    torin_release_uploads($torin_photos);
    // The rejection path is where MORE temp files exist, not fewer: five
    // photographs can normalise successfully and still be thrown away because
    // a sixth was attached. So it gets the same leftover count the success
    // path gets — counts and the request id only, never a filename or a
    // reason string, both of which came from the request.
    $torin_stale = 0;
    foreach ($torin_photos as $torin_photo) {
        if (file_exists($torin_photo)) {
            $torin_stale++;
        }
    }
    error_log('torin contact ' . $torin_rid . ': refused photos=' . count($torin_photos) .
        ' rejected=' . count($torin_uploads['errors']) . ' leftover=' . $torin_stale);
    torin_send_fail_page(
        'Проверете отбелязаните полета и опитайте отново.',
        ['photos' => $torin_uploads['errors'][0]]
    );
}

// ── SECRETS ─────────────────────────────────────────────────────────────────
// Read by ABSOLUTE PATH from outside the document root (T-04-08). This is the
// only file in the tree that resolves this key; notify.php takes the resulting
// array as an argument and never touches the filesystem, so no include chain
// from the page shell can reach a credential.
//
// GUARDED, not a bare require. A bare require of a missing file is a FATAL —
// which on this host, with display_errors still On in php85-fcgi.ini
// (04-HOST-CAPABILITIES BLOCKER 1), would print the absolute server path into
// the response for the visitor to read. The guard turns a missing credential
// into the honest failure page it should be.
$torin_secrets_path = (string) ($site['secrets_path'] ?? '');
if ($torin_secrets_path === '' || !is_file($torin_secrets_path)) {
    error_log('torin contact ' . $torin_rid . ': secrets file missing');
    torin_send_fail_page(torin_fail_copy(), []);
}
$torin_secrets = require $torin_secrets_path;
if (!is_array($torin_secrets)) {
    error_log('torin contact ' . $torin_rid . ': secrets file malformed');
    torin_send_fail_page(torin_fail_copy(), []);
}

// ── NOTIFY ──────────────────────────────────────────────────────────────────
// The tracer passed an empty array here and this is the one line 04-03
// changed. The driver branches on the photo count internally; the handler does
// not know or care which of the three shapes went out, which is what keeps the
// fan-out interface the same one 04-02 shipped.
$torin_result = torin_notify($torin_in, $torin_photos, $torin_secrets);

// RELEASED IMMEDIATELY, ON THE ONE LINE BOTH OUTCOMES PASS THROUGH. This sits
// above the branch on purpose: putting an unlink inside each arm is how the
// arm added next year is the one that leaks. The photographs have left the
// machine by now, and D4-07's promise is that no copy of them stays on it.
foreach ($torin_photos as $torin_photo) {
    @unlink($torin_photo);
}

// THE POST-SEND LEFTOVER COUNT, LOGGED. Two integers and the request id; no
// filename, no path, no submitted value, nothing a visitor typed (P-14).
//
// It exists because «no copy of a visitor's photograph survives the request»
// is otherwise an unobservable promise: the visitor sees a redirect, the owner
// sees a message, and a temp file that outlived the loop above would sit in a
// directory neither of them can see. `leftover=0` is the only form of that
// promise anyone outside this process can check.
$torin_leftover = 0;
foreach ($torin_photos as $torin_photo) {
    if (file_exists($torin_photo)) {
        $torin_leftover++;
    }
}
error_log('torin contact ' . $torin_rid . ': photos=' . count($torin_photos) . ' leftover=' . $torin_leftover);
$torin_photos = [];

// BRANCH ON THE OVERALL FLAG ONLY, NEVER ON A CHANNEL (D4-08). Reading
// $torin_result['channels']['telegram'] here would hard-code the current
// channel into the success test and quietly un-do D4-05's reversibility the
// day 04-05 adds the mail leg.
if ($torin_result['ok'] === true) {
    // THE THROTTLE RECORD IS WRITTEN HERE AND ONLY HERE — after a channel has
    // accepted the enquiry, not when the guard checked the quota above. The
    // window then means what its message says («вече получихме запитване от
    // вас»): it counts enquiries that reached the shop, so no rejected,
    // mistyped or undelivered attempt can lock a visitor out of retrying the
    // one that would have worked.
    torin_rate_limit_record($torin_ip, $torin_secret, $torin_rl);

    // 303 See Other — POST/Redirect/GET. The method is downgraded to GET for
    // the redirect, so a refresh on the confirmation page re-fetches msg.html
    // and cannot resubmit the enquiry or re-notify the owner. A 302 is not a
    // reliable substitute: its method-rewriting behaviour is a de-facto
    // convention rather than a specified one.
    header('Location: msg.html', true, 303);
    exit;
}

// EVERY CHANNEL FAILED. The visitor is told the truth and given the phone
// number, because D4-07 keeps no server-side copy — a false success here is an
// enquiry that nobody, on either end, knows was lost.
error_log('torin contact ' . $torin_rid . ': all notification channels failed');
torin_send_fail_page(torin_fail_copy(), []);

// ── PAGE RENDERING ──────────────────────────────────────────────────────────
// Declared after use; PHP hoists function declarations at the top level of a
// file, so this is well-defined and it keeps the handler's control flow
// readable from the top down.

// One guarded read of one POST field, trimmed, or '' — and it is the ONLY
// way this file touches $_POST.
//
// THE is_string() TEST IS THE WHOLE POINT, and it is not defensive padding.
// PHP builds $_POST from the request body, so a submitter chooses the SHAPE of
// every value as freely as its content: posting `website[]=x` makes
// $_POST['website'] an ARRAY. Casting an array to string yields the literal
// 'Array' and raises an «Array to string conversion» warning — which, with
// display_errors still On in this subtree (04-HOST-CAPABILITIES BLOCKER 1),
// prints into the response BEFORE the honeypot's header() call and turns a
// redirect into a «headers already sent» failure. One request shape would
// therefore have broken the endpoint and leaked a warning to whoever sent it.
//
// Treating a non-string as absent is the right answer rather than merely the
// safe one: no rendering of this form can produce one, so an array here is
// never a customer's enquiry. The required-field checks below then reject it
// on its own merits, with the ordinary Bulgarian message.
function torin_post(string $key): string
{
    if (!isset($_POST[$key]) || !is_string($_POST[$key])) {
        return '';
    }
    return trim($_POST[$key]);
}

// Character count, with a byte fallback.
//
// The fallback is not defensive padding — it is a measured risk on this
// specific host. Over the course of plan 04-01 ext-curl was present, then
// absent, then present again as the account moved between PHP builds
// (04-HOST-CAPABILITIES records all three readings). mbstring is measured
// present today; an extension vanishing under this tree once more would
// otherwise turn every length check on this page into a FATAL, on the one
// endpoint the business depends on.
//
// The byte branch is deliberately STRICTER, not equivalent: Cyrillic is two
// bytes per character in UTF-8, so a byte count over the same limit can only
// reject inputs a character count would also have accepted at up to half the
// length. Being wrong in the strict direction produces a visible «too long»
// message the visitor can act on; being wrong in the loose direction produces
// a message Telegram silently refuses to deliver.
function torin_chars(string $value): int
{
    if (function_exists('mb_strlen')) {
        return (int) mb_strlen($value, 'UTF-8');
    }
    return strlen($value);
}

// The all-channels-failed sentence, with the shop's main number COMPOSED from
// $site rather than typed. site-config.php owns the phone list, footer.php
// renders it at the bottom of this very page, and a literal here is the copy
// that gets forgotten the day the number changes.
function torin_fail_copy(): string
{
    global $site;
    return 'Съобщението не беше изпратено. Обадете се на ' . $site['phones'][0] . ' или опитайте отново.';
}

// The honest failure page (D4-10, UI-SPEC C-8). Reuses .notice--error, which
// Phase 2 built and whose own source comment says its appearance is «a
// carried-forward visual check» — this is where that check is discharged. No
// second band component is authored.
//
// WHAT THIS DELIBERATELY IS NOT: the full error re-render. Repopulating the
// form from $values is 04-05's, together with the spam guard that produces
// most of the errors worth repopulating for. What ships here is the honest
// half — the visitor is never told a failed submission succeeded, and is never
// redirected to a confirmation page for something that did not happen. Adding
// the re-render now would mean writing it against a validation set that is
// still half-built.
//
// NO EXCEPTION TEXT, NO PATH, NO SUBMITTED VALUE reaches this page (T-04-09,
// P-14). $summary and $errors are developer-authored literals; $errors keys
// are compared against a fixed list rather than echoed, so nothing
// attacker-controlled can reach the markup even through the id of an anchor.
// $status DEFAULTS TO 422 so the three 04-02/04-03 call sites are untouched.
// It exists because 04-05 added a branch that is not «what you sent is
// unprocessable» but «you have sent one already»: a throttle answering 422
// would be a lie told to every machine reading the response, including the
// host's own logs, for no saving at all. The visible page is the same one; the
// status line is the part that has to stay honest.
function torin_send_fail_page(string $summary, array $errors, int $status = 422): void
{
    global $site;

    http_response_code($status);
    // Same no-store contract as kontakti.html (RESEARCH P-11), and for the
    // same reason: this page links back to a form whose hidden timestamp
    // 04-05 will verify.
    header('Cache-Control: no-store');

    // category-page.php owns torin_esc(); required BEFORE the chrome so every
    // helper this template calls exists before a byte of output is produced.
    //
    // `global $site` above is load-bearing and must not be deleted as
    // redundant. This function is the only place in the project where
    // header.php and footer.php are included from inside a FUNCTION scope, so
    // site-config.php's top-level `$site = array(…)` does not land where the
    // chrome can see it — and that assignment does not run again in any case,
    // because require_once already loaded the file at global scope. Without
    // the import, footer.php reads an undefined $site and prints warnings into
    // the page instead of the shop's address.
    require_once dirname(__FILE__) . '/includes/category-page.php';

    $torin_title = 'Запитването не беше изпратено · Торин';
    $torin_desc  = 'Запитването до Торин Компютърс не беше изпратено. Проверете данните и опитайте отново или се обадете на сервиза.';
    require_once dirname(__FILE__) . '/includes/header.php';
?>

<main>
	<section class="section">
		<div class="container">
			<p class="notice notice--error"><?php echo torin_icon('alert'); ?><span><?php echo torin_esc($summary); ?></span></p>

			<h1>Запитването не беше изпратено</h1>

<?php   if (count($errors) > 0) { ?>
			<?php // In-page links to each failing control on the contact page.
			      // On a phone the first invalid field is usually off-screen, and
			      // a list of links is what makes the band actionable instead of
			      // decorative.
			      //
			      // The fragment is built from a FIXED whitelist of field names,
			      // never from an array key that arrived with the request. The
			      // keys here are all set by this file's own validation block, so
			      // the whitelist is belt-and-braces — which is the correct
			      // amount of care for a string that ends up in an href. ?>
			<ul>
<?php       $torin_fields = array('device', 'fault', 'photos', 'name', 'phone', 'email', 'consent');
            foreach ($torin_fields as $torin_field) {
                if (!isset($errors[$torin_field])) {
                    continue;
                } ?>
				<li><a href="kontakti.html#<?php echo torin_esc($torin_field); ?>"><?php echo torin_esc($errors[$torin_field]); ?></a></li>
<?php       } ?>
			</ul>
<?php   } ?>

			<p>Обаждането е най-бързият начин да стигнете до нас — телефоните и работното време са в долната част на страницата.</p>

			<p><a class="btn btn--primary" href="kontakti.html#contact-form">Обратно към формата</a></p>
		</div>
	</section>
</main>

<?php
    require_once dirname(__FILE__) . '/includes/footer.php';
    exit;
}
?>
