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
// footer.php may reach this file, or reach the vendored mail library beneath
// src/vendor/. This file includes the chrome; the chrome never includes this
// file. A plan-level grep asserts it in both directions.
//
// AS OF 04-05 THAT IS NO LONGER HYPOTHETICAL. The vendor directory exists and
// holds three files of namespaced library code, required from exactly one
// function in this file (torin_send_mail, at the bottom) and from nowhere else
// in the tree. includes/notify.php registers a mail driver that DELEGATES to
// that function by name rather than loading anything itself, so the library
// path appears zero times under src/includes/. Keep it that way: the moment a
// partial in includes/ requires the library directly, the rollback story at
// the top of this comment stops being true for the whole site rather than for
// one endpoint.
//
// THE THREE REQUIRE LINES AT THE BOTTOM ARE THE ONLY PLACE THAT PATH IS
// WRITTEN IN THIS FILE, and the comments here describe it rather than spell
// it, deliberately: a plan-level check counts the occurrences and expects
// exactly three. The same discipline the note below applies to the legacy send
// function, applied to a path instead of a function name.
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
        'Изпращането не беше прието — възможно е формулярът да е стоял отворен твърде дълго. Опитайте отново — това, което сте написали, е запазено.',
        [],
        422,
        // THE SENTENCE CHANGED WITH THE PAGE. It used to say «отворете
        // страницата наново», because the page it led to had no form on it and
        // reopening kontakti.html was the only way forward. The failure page
        // now RETURNS the form, with a freshly-minted token and the visitor's
        // own text in it, so that instruction would send them away from the
        // thing that fixes their problem. A stale token is the likeliest
        // non-bot cause of this branch and it is exactly the one a re-render
        // repairs in a single click.
        torin_values()
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
        429,
        // Repopulated like every other rejection. The visitor is being asked
        // to WAIT, not to retype — and a throttled visitor who came back with
        // something to add is the likeliest genuine case this branch sees.
        torin_values()
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
    // $torin_in, not torin_values(): identical content, but this is the array
    // the validation above actually ruled on, so the page cannot show a value
    // that differs from the one that was judged.
    torin_send_fail_page('Проверете отбелязаните полета и опитайте отново.', $torin_errors, 422, $torin_in);
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
    // WINDOWS ENTRY 22, CLOSED HERE. The per-file rejection reason already
    // surfaced as a field-level error on the photo control; what it cost the
    // visitor was everything they had typed, because this branch rendered a
    // page with no form on it and the only way back was an empty one. A
    // refused photograph is the cheapest thing in this submission to redo and
    // the fault description is the most expensive, so losing the second to
    // reject the first was exactly backwards.
    torin_send_fail_page(
        'Проверете отбелязаните полета и опитайте отново.',
        ['photos' => $torin_uploads['errors'][0]],
        422,
        $torin_in
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
    torin_send_fail_page(torin_fail_copy(), [], 422, $torin_in);
}
$torin_secrets = require $torin_secrets_path;
if (!is_array($torin_secrets)) {
    error_log('torin contact ' . $torin_rid . ': secrets file malformed');
    torin_send_fail_page(torin_fail_copy(), [], 422, $torin_in);
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

    // ── THE CUSTOMER CONFIRMATION (D4-09) ───────────────────────────────────
    // A SECOND, SEPARATE MESSAGE, and it lives INSIDE THE SUCCESS BRANCH
    // rather than inside the mail driver. That placement is the whole
    // judgement here and it is not an implementation detail:
    //
    // · Inside the driver it would be sent whenever the EMAIL channel worked,
    //   which is not the same question. It would also NOT be sent when
    //   Telegram alone carried the enquiry — so the visitor whose enquiry
    //   arrived perfectly well would get no confirmation, purely because of
    //   which internal channel happened to be up.
    // · Here it is sent exactly when the enquiry REACHED THE SHOP by any
    //   route, which is what «получихме запитването ви» actually claims.
    //   Sending it on a failed submission would be the prohibition this plan
    //   states outright — telling a visitor something was received when it was
    //   not — in its most convincing possible form, because it would arrive in
    //   their inbox.
    //
    // ITS FAILURE IS NOT THE SUBMISSION'S FAILURE. The return value is logged
    // and then dropped: the enquiry has already been delivered, and refusing
    // to redirect because a courtesy message bounced would turn a successful
    // submission into a visible failure. The bounce is the POINT of D4-09 —
    // it is what makes a mistyped address visible in minutes instead of after
    // a lost week — and a bounce arrives at office@torin.bg, not here.
    //
    // NO ATTACHMENT. The visitor has the photographs; they are on the phone
    // that took them. Sending them back costs the visitor mobile data to
    // receive their own files and is the one part of this message that could
    // plausibly push it into a spam folder.
    $torin_confirm_via = torin_send_mail([
        'to'          => $torin_in['email'],
        'subject'     => 'Получихме запитването ви — Торин Компютърс',
        // Composed from $site rather than typed, exactly like the failure
        // copy: site-config.php owns the phone list, and a literal here is the
        // copy that gets forgotten the day the number changes.
        'body'        => "Здравейте,\n\n"
            . "получихме запитването ви и ще се свържем с вас в работното време на сервиза.\n"
            . 'Работно време: ' . $site['hours'] . ".\n\n"
            . "Ако въпросът не търпи отлагане, обадете се на " . $site['phones'][0] . ".\n\n"
            . "Това съобщение е изпратено автоматично — няма нужда да отговаряте на него.\n\n"
            . "Торин Компютърс\n"
            . $site['address'] . "\n",
        // NO reply-to. The shop's own address is already the sender, so a
        // visitor who replies anyway reaches a real mailbox; pointing reply-to
        // back at the visitor's own address would make a reply go to
        // themselves.
        'reply_to'    => '',
        'attachments' => [],
    ], $torin_secrets);
    if ($torin_confirm_via === false) {
        error_log('torin contact ' . $torin_rid . ': confirmation to the customer failed');
    }

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
// The channel-by-channel verdict, logged ONCE and only on this branch. The
// success test above still reads the overall flag and nothing else (D4-08) —
// this is a diagnostic, not a decision, and it must never become one. It
// exists because «all notification channels failed» on its own does not say
// whether Telegram and the mail leg failed for one shared reason or two
// different ones, and with two channels that is now the first question anybody
// debugging this asks. Channel NAMES are developer-authored constants; no
// submitted value goes near it (P-14).
$torin_verdicts = [];
foreach ($torin_result['channels'] as $torin_name => $torin_ok) {
    $torin_verdicts[] = $torin_name . '=' . ($torin_ok ? 'ok' : 'fail');
}
error_log('torin contact ' . $torin_rid . ': all notification channels failed (' .
    implode(' ', $torin_verdicts) . ')');
torin_send_fail_page(torin_fail_copy(), [], 422, $torin_in);

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

// The five text fields, read for REPOPULATION rather than for validation.
//
// It exists because the guard branches above run before $torin_in is built,
// and a visitor rejected by the time trap or the throttle has typed exactly as
// much as one rejected by validation — losing it costs them exactly as much.
// Composed through torin_post(), so a non-string value is treated as absent
// here for the same reason it is everywhere else in this file.
//
// The consent box and the photographs are deliberately absent: consent must be
// a deliberate act on every submission, and a file input's value cannot be set
// from markup in any browser.
function torin_values(): array
{
    return [
        'device' => torin_post('device'),
        'fault'  => torin_post('fault'),
        'name'   => torin_post('name'),
        'phone'  => torin_post('phone'),
        'email'  => torin_post('email'),
    ];
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
// ── THE ERROR RE-RENDER, WHICH 04-02 DEFERRED TO HERE ───────────────────────
// 04-02 shipped the honest HALF of this page: the visitor was never told a
// failed submission had succeeded. What it could not ship — because the
// validation set was still half-built — was the visitor's own text coming back
// with them. Until now every failure branch replaced the form with a link back
// to an EMPTY one, so a customer who attached a sixth photograph lost the
// model number and the fault description they had just typed, and the only way
// to find that out was to be that customer (WINDOWS entry 22).
//
// The partial that renders the empty form on kontakti.html renders the
// populated one here — ONE markup source with TWO callers, which is the whole
// contract contact-form.php:6-15 states and the reason that file is one
// function and no output. A second copy of a seven-field form is how the two
// renderings start to disagree about their own field names, and a field name
// that disagrees with this handler is a silently dropped enquiry.
//
// EVERY REDISPLAYED VALUE IS ESCAPED BY THE PARTIAL, on both of its branches
// (T-04-07). The consent box renders unchecked regardless of what was
// submitted, because contact-form.php does not read $values for it at all —
// that omission is the feature, and restoring a previously-ticked box would be
// pre-ticked consent by another name.
//
// THE HIDDEN TIMESTAMP IS RE-MINTED by the partial on every render, including
// this one, so the returned form is immediately submittable rather than
// carrying a token that is already seconds into its own window.
//
// NO EXCEPTION TEXT, NO PATH AND NO SUBMITTED VALUE REACHES THE BAND OR THE
// LINK LIST (T-04-09, P-14). $summary and $errors are developer-authored
// literals; $errors keys are compared against a fixed list rather than echoed,
// so nothing attacker-controlled can reach the markup even through the id of
// an anchor. $values reaches the FORM CONTROLS only, through the partial's
// escaping.
//
// $status DEFAULTS TO 422 so the 04-02/04-03 call sites are untouched. It
// exists because 04-05 added a branch that is not «what you sent is
// unprocessable» but «you have sent one already»: a throttle answering 422
// would be a lie told to every machine reading the response, including the
// host's own logs, for no saving at all. $values is likewise defaulted and
// last, so a call site with nothing to repopulate — the oversized POST, where
// the engine threw the fields away before this file ran — says so by omission.
function torin_send_fail_page(string $summary, array $errors, int $status = 422, array $values = []): void
{
    global $site;

    http_response_code($status);
    // Same no-store contract as kontakti.html (RESEARCH P-11), and for the
    // same reason — but now it is not merely consistency. This page CARRIES a
    // form whose hidden timestamp this same file verifies on the next request.
    // A cached copy of this response would hand two visitors the same signed
    // token, and every one of them past the first would be told the form had
    // sat open too long.
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
    // The form partial. It emits nothing on include — one function definition
    // and no top-level output — so requiring it here, after the response code
    // and the cache header but before the chrome, cannot produce a «headers
    // already sent» failure.
    require_once dirname(__FILE__) . '/includes/contact-form.php';

    $torin_title = 'Запитването не беше изпратено · Торин';
    $torin_desc  = 'Запитването до Торин Компютърс не беше изпратено. Проверете данните и опитайте отново или се обадете на сервиза.';

    // The «which field rejects people» event (UI-SPEC C-9, wired in 04-07).
    // header.php emits this as a data attribute and js/analytics.js fires one
    // event per token. It answers the single most actionable question this
    // form can raise: a field that rejects a large share of the people who
    // reach it is a field whose rules or wording are wrong, not a population
    // of careless visitors.
    //
    // ONLY THE FIELD NAME TRAVELS — never the value the visitor typed, never
    // the server's message, never anything about who they are. The names are
    // taken from the SAME fixed whitelist the in-page link list below uses, so
    // an array key that somehow arrived with the request cannot reach the
    // attribute, and analytics.js independently drops any name outside that
    // list. Two gates on one string, because this is the one place in the
    // project where a submitted-data structure meets a third-party beacon.
    $torin_track = '';
    $torin_track_fields = array('device', 'fault', 'photos', 'name', 'phone', 'email', 'consent');
    foreach ($torin_track_fields as $torin_track_field) {
        if (isset($errors[$torin_track_field])) {
            $torin_track .= ($torin_track === '' ? '' : ' ') . 'form-error:' . $torin_track_field;
        }
    }

    require_once dirname(__FILE__) . '/includes/header.php';
?>

<main>
	<section class="section">
		<div class="container">
			<?php // tabindex="-1" makes the band programmatically focusable so a
			      // keyboard or screen-reader user is sent to the explanation
			      // rather than to the top of the document (UI-SPEC C-8).
			      //
			      // BOTH HALVES NOW SHIP. 04-05 placed this attribute and the id
			      // while the .focus() that uses them belonged to a file it did
			      // not touch; 04-07 added that call to js/analytics.js, which
			      // finds this band by the id below on every rendering of this
			      // page. The id is therefore load-bearing in a second file and
			      // must not be renamed here alone. ?>
			<p class="notice notice--error" id="form-error" tabindex="-1"><?php echo torin_icon('alert'); ?><span><?php echo torin_esc($summary); ?></span></p>

			<h1>Запитването не беше изпратено</h1>

<?php   if (count($errors) > 0) { ?>
			<?php // In-page links to each failing control. On a phone the first
			      // invalid field is usually off-screen, and a list of links is
			      // what makes the band actionable instead of decorative.
			      //
			      // The fragments are BARE now («#device», not
			      // «kontakti.html#device») and that is the substantive half of
			      // this edit rather than a tidy-up: the form is on THIS page, so
			      // the old href navigated away from the populated re-render to an
			      // empty form — throwing away the very values this function now
			      // exists to preserve.
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
				<li><a href="#<?php echo torin_esc($torin_field); ?>"><?php echo torin_esc($errors[$torin_field]); ?></a></li>
<?php       } ?>
			</ul>
<?php   } ?>

			<p>Обаждането е най-бързият начин да стигнете до нас — телефоните и работното време са в долната част на страницата.</p>

			<?php // THE FORM ITSELF, repopulated. The photographs are NOT carried
			      // back and cannot be: a file input's value is not settable from
			      // markup, by design, in every browser. The help text under the
			      // control already says the photographs are optional, and the
			      // text a visitor typed — which is the expensive part — survives.
			      // Saying this out loud here so that a future reader does not
			      // "fix" the omission by inventing a server-side upload cache,
			      // which is precisely what D4-07 promises this site does not
			      // keep. ?>
			<?php torin_render_contact_form($values, $errors); ?>
		</div>
	</section>
</main>

<?php
    require_once dirname(__FILE__) . '/includes/footer.php';
    exit;
}

// ── THE EMAIL TRANSPORT (CONTACT-03, D4-11) ─────────────────────────────────
//
// THE ONLY PLACE IN THIS TREE THAT LOADS THE MAIL LIBRARY. Three require lines
// and nothing else reaches src/vendor/ — a grep asserts it in both directions:
// zero occurrences of the library path under src/includes/, exactly three in
// this file. The library is namespaced modern PHP; a 5.2 interpreter meeting
// it fails at COMPILE time, before a byte of output, so confining it here
// means a runtime rollback breaks the contact form and nothing else.
//
// THE REQUIRES ARE INSIDE THE FUNCTION, not at the top of the file. A bot
// stopped by the spam guard never parses 150 KB of library it was never going
// to use, and the quarantine's blast radius shrinks from «any POST to this
// endpoint» to «a POST that got as far as having something to deliver». Class
// declarations from a require inside a function land in the global scope, so
// nothing about the namespace resolution below depends on this placement.
//
// ── THE CASCADE, AND WHY IT EXISTS ──────────────────────────────────────────
// Authenticated SMTP against the host's own relay is attempted first when a
// credential exists; ANY failure before the relay has accepted the message
// falls through to the local sendmail binary, which needs no credential at
// all. If no credential is present the connection is not attempted — going
// straight to sendmail rather than paying a 15-second timeout on every single
// send, on the one form this business depends on.
//
// The owner's instruction, in their words: a missing or wrong password must
// never cost the business an enquiry. Both paths leave the same machine, and
// both are therefore covered by the same published SPF (+a, +ip4 for this
// box) and the same default._domainkey.torin.bg DKIM key, so the fallback
// gives up no deliverability — which is the usual reason not to have one.
//
// ┌────────────────────────────────────────────────────────────────────────┐
// │ THE PRE-ACCEPTANCE BOUNDARY. READ THIS BEFORE CHANGING ANYTHING BELOW. │
// └────────────────────────────────────────────────────────────────────────┘
// The fall-through may fire ONLY on a failure that happened before the relay
// took responsibility for the message. Once the relay has accepted it, the
// message is queued and WILL be delivered; retrying through sendmail at that
// point sends the enquiry TWICE. Two identical enquiries arriving is a worse
// outcome than one error line in a log, because the owner cannot tell them
// apart — they may be one customer who wrote twice, or one customer whose
// enquiry we duplicated, and quoting a price twice for one laptop is a real
// cost to a real business.
//
// The boundary is placed by OBSERVATION, not by parsing an exception message.
// PHPMailer raises one exception class for every failure and localises its
// text, so a string match would silently stop matching the day the library
// updates or the language changes. Instead the SMTP conversation is watched
// for the server's `354` reply — the «start mail input» that answers the DATA
// command and is the exact instant the relay begins taking the message. Seen
// it, and this attempt is post-acceptance and is NEVER retried. Not seen it —
// connection refused, TLS failure, AUTH rejected, MAIL FROM refused, RCPT TO
// refused, timeout before DATA — and the message provably never got in, so
// sendmail is safe.
//
// THE DEFAULT, WHEN THERE IS NO EVIDENCE EITHER WAY, IS «DO NOT RETRY» for
// everything from the 354 onwards. That includes the genuinely ambiguous case
// the SMTP protocol cannot resolve: the terminating dot is written and the
// connection dies before the relay's reply arrives, where the message may or
// may not have been queued. Not retrying risks losing one enquiry, which the
// visitor is TOLD about on the failure page and can act on. Retrying risks
// silently duplicating it, which nobody is told about at all.
//
// WHY WATCHING THE CONVERSATION IS SAFE HERE. SMTP::client_send() replaces the
// AUTH payload with the literal '[credentials hidden]' at every debug level
// below DEBUG_LOWLEVEL (SMTP.php:1240-1251); this runs at DEBUG_SERVER, which
// is two levels below it, so the mailbox password cannot reach the callback.
// The callback ALSO never stores, logs or returns the strings it is handed —
// it sets one boolean and drops everything else on the floor. Do not add a
// diagnostic here that keeps them.
function torin_send_mail(array $spec, array $secrets)
{
    global $torin_rid;

    require_once dirname(__FILE__) . '/vendor/phpmailer/Exception.php';
    require_once dirname(__FILE__) . '/vendor/phpmailer/PHPMailer.php';
    require_once dirname(__FILE__) . '/vendor/phpmailer/SMTP.php';

    // ── THE PER-REQUEST CIRCUIT BREAKER ─────────────────────────────────────
    // A single submission sends TWO messages: the enquiry to the shop and the
    // confirmation to the customer. Without this flag, a relay that is down —
    // or a password that is wrong — costs the visitor the full connect timeout
    // TWICE while they sit on a submitted form, and the second wait buys
    // nothing, because the first attempt already established the answer.
    //
    // It is deliberately NOT persisted anywhere. A flat file or an APC entry
    // would mean a five-minute outage kept SMTP switched off long after it
    // came back, and the state would need its own expiry, its own storage
    // outside the web root and its own failure mode. Per request, in memory,
    // dead at the end of the request: the only cost of being wrong is one
    // extra attempt on the next submission.
    //
    // Only a PRE-ACCEPTANCE failure sets it. A post-acceptance failure means
    // the connection and the credential both worked, which is the opposite of
    // what this flag records.
    static $torin_smtp_down = false;

    $torin_cred = (isset($secrets['smtp_password']) && is_string($secrets['smtp_password']))
        ? trim($secrets['smtp_password'])
        : '';

    if ($torin_cred !== '' && $torin_smtp_down) {
        error_log('torin contact ' . $torin_rid .
            ': mail smtp already failed this request, going straight to sendmail');
    } elseif ($torin_cred !== '') {
        $torin_verdict = torin_mail_attempt($spec, $torin_cred, true);
        if ($torin_verdict === 'sent') {
            error_log('torin contact ' . $torin_rid . ': mail delivered via=smtp');
            return 'smtp';
        }
        if ($torin_verdict === 'accepted') {
            // The relay took the message and then something went wrong. It is
            // queued; a second send would duplicate it. This channel reports
            // failure, D4-08 lets the other one carry the enquiry, and the log
            // line is deliberately distinct from the one above it so that the
            // day an owner says «I got two of these» this branch can be found.
            error_log('torin contact ' . $torin_rid .
                ': mail smtp failed AFTER the relay accepted — not retried, message may be queued');
            return false;
        }
        $torin_smtp_down = true;
        error_log('torin contact ' . $torin_rid .
            ': mail smtp failed before acceptance, falling through to sendmail');
    } else {
        error_log('torin contact ' . $torin_rid . ': mail no smtp credential, using sendmail');
    }

    $torin_verdict = torin_mail_attempt($spec, '', false);
    if ($torin_verdict === 'sent') {
        error_log('torin contact ' . $torin_rid . ': mail delivered via=sendmail');
        return 'sendmail';
    }
    error_log('torin contact ' . $torin_rid . ': mail sendmail failed');
    return false;
}

// ONE ATTEMPT ON ONE TRANSPORT. Returns 'sent', 'accepted' (the relay took the
// message and the attempt failed afterwards — never retry) or 'pre' (nothing
// was accepted — retrying is safe).
//
// $password IS PASSED SEPARATELY rather than the whole secrets array, so that
// the only credential this function can see is the one it needs. The Telegram
// token is in that array and has no business being in scope here.
function torin_mail_attempt(array $spec, string $password, bool $useSmtp): string
{
    // Set by the observer below, read in both exits. Declared before the try
    // so that a throw on the very first line still finds it defined.
    $torin_accepted = false;

    $torin_m = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        if ($useSmtp) {
            // Settings from the host's own documentation, verified at research:
            // the host's server, port 25, AUTH yes, ENCRYPTION none. Outbound
            // 25, 26 and 465 to EXTERNAL servers are blocked on this shared
            // hosting, so a transactional relay is not reachable over SMTP at
            // all and this is effectively the only SMTP option.
            $torin_m->isSMTP();
            $torin_m->Host     = 'torin.bg';
            $torin_m->Port     = 25;
            $torin_m->SMTPAuth = true;
            $torin_m->Username = 'office@torin.bg';
            $torin_m->Password = $password;
            // Unencrypted, and the second line is what makes that stick.
            // Without SMTPAutoTLS = false the library opportunistically issues
            // STARTTLS whenever the server advertises it and then fails the
            // handshake — a failure that reads like a network fault and is
            // nothing of the kind. It is a same-machine hop.
            $torin_m->SMTPSecure  = '';
            $torin_m->SMTPAutoTLS = false;
            // Bounded, because a visitor is holding a submitted form open for
            // the duration (T-04-11). 15s against the host's own relay, which
            // is either on this machine's network or not reachable at all.
            $torin_m->Timeout = 15;

            // THE PRE-ACCEPTANCE OBSERVER. See the long note above
            // torin_send_mail(); this is its entire implementation.
            //
            // The prefix match is anchored at position 0 and includes the
            // literal PHPMailer writes before every server reply
            // (SMTP.php:380, :1148), so a '354' appearing inside a message id
            // or a banner cannot trip it. Being wrong in the eager direction
            // would suppress a legitimate fall-through and lose one enquiry;
            // being wrong in the lax direction would duplicate one. The
            // anchoring makes the first unlikely and the ordering below makes
            // the second impossible.
            $torin_m->SMTPDebug   = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
            $torin_m->Debugoutput = function ($torin_str, $torin_level) use (&$torin_accepted) {
                // Sets a boolean. Stores nothing, logs nothing, returns
                // nothing. Do not make this function helpful.
                if (strpos($torin_str, 'SERVER -> CLIENT: 354') === 0) {
                    $torin_accepted = true;
                }
            };
        } else {
            // The local binary, confirmed present at /usr/sbin/sendmail and
            // configured as sendmail_path = '/usr/sbin/sendmail -t -i'
            // (04-HOST-CAPABILITIES:94, :98). isSendmail() reads that same ini
            // value, so the command this runs is the host's own, not a guess.
            //
            // NOTE FOR ANYONE READING 04-HOST-CAPABILITIES BLOCKER 2: the
            // «smtp:localhost:25 FAIL Connection refused» reading there rules
            // out a LOCAL MTA LISTENING ON A SOCKET. It says nothing about
            // this path, which pipes to a binary, and nothing about the relay
            // above, which is a different host.
            $torin_m->isSendmail();
        }

        // UTF-8 IS MANDATORY, not a preference: every string in this message
        // is Bulgarian, and the default 8-bit charset would deliver mojibake
        // to the one person who needs to read it.
        $torin_m->CharSet = 'UTF-8';

        // THE SENDER IS A FIXED, DEVELOPER-AUTHORED LITERAL (T-04-22). This is
        // the single line that most distinguishes this handler from the one it
        // replaces, where the From header was built by concatenating $_POST —
        // header injection and sender spoofing in one line, live on the
        // production site today.
        //
        // WRITTEN AS A LITERAL RATHER THAN READ FROM $site['email'], and the
        // difference is not stylistic. This address is ALSO the mailbox the
        // SMTP leg authenticates as; deriving it from an editable config value
        // would mean a future edit to the displayed contact address silently
        // broke authentication, in a place nobody would think to look. The two
        // uses are the same string for a reason and they are pinned together.
        $torin_m->setFrom('office@torin.bg', 'ТОРИН КОМПЮТЪРС');
        $torin_m->addAddress($spec['to']);

        // The visitor's address, and the ONLY header it may ever occupy. It
        // has already passed FILTER_VALIDATE_EMAIL above — an address that
        // failed validation never reaches this function, because a submission
        // that failed validation never reaches the fan-out.
        if (isset($spec['reply_to']) && $spec['reply_to'] !== '') {
            $torin_m->addReplyTo($spec['reply_to']);
        }

        // The filename given to each attachment is GENERATED. The visitor's
        // own filename never travels — it is not even an argument to the
        // upload pipeline — and a name that arrived with the request has no
        // business in a Content-Disposition header.
        if (isset($spec['attachments']) && is_array($spec['attachments'])) {
            $torin_i = 0;
            foreach ($spec['attachments'] as $torin_path) {
                $torin_m->addAttachment($torin_path, 'torin-' . $torin_i . '.jpg');
                $torin_i++;
            }
        }

        $torin_m->Subject = $spec['subject'];
        // PLAIN TEXT, NO HTML PART. Same reasoning as notify.php's refusal of
        // a Telegram parse_mode: with markup, every submitted value becomes
        // attacker-controlled markup needing its own escaping rules, and the
        // owner gains bold labels in exchange for a whole class of bug. Set
        // BEFORE Body, because isHTML() decides how the body is treated.
        $torin_m->isHTML(false);
        $torin_m->Body = $spec['body'];

        if ($torin_m->send()) {
            return 'sent';
        }
        // send() returned false rather than throwing. Reachable when
        // exceptions are suppressed internally; the boundary reading applies
        // identically.
        return $torin_accepted ? 'accepted' : 'pre';
    } catch (\PHPMailer\PHPMailer\Exception $torin_e) {
        // LOGGED AS A FIXED LITERAL, NEVER ECHOED AND NEVER WITH ITS MESSAGE
        // (T-04-09, T-04-27, P-14). The library's exception text can carry the
        // relay's response, which can carry a recipient address — which is a
        // submitted value. The caller writes the correlation line that makes
        // this findable; this one says only which transport it was.
        error_log('torin mail: send failed (' . ($useSmtp ? 'smtp' : 'sendmail') . ')');
        return $torin_accepted ? 'accepted' : 'pre';
    } catch (\Throwable $torin_e) {
        // A TypeError or a missing extension is a code defect, not a delivery
        // failure — but it must still leave this function as a return value
        // rather than as a 500 on a form the visitor spent two minutes filling
        // in (T-04-11). The fan-out's own wrapper would catch it one level up;
        // catching it here is what preserves the acceptance verdict, which
        // that wrapper cannot see.
        error_log('torin mail: driver errored (' . ($useSmtp ? 'smtp' : 'sendmail') . ')');
        return $torin_accepted ? 'accepted' : 'pre';
    }
}
?>
