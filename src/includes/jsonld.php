<?php
// includes/jsonld.php — LocalBusiness structured data (D-34). PHP 5.2-safe:
// array() only, no [] literals, no closures, no short echo tags.
//
// Included once from footer.php, so it renders on all 16 pages from one source.
// Every value is read from $site or comes from the verified source table in
// 02-RESEARCH §6b. Nothing here is invented.
//
// The @type below is an array of two. The store subtype is the most specific
// real schema.org type for a shop that both repairs and sells — there is no
// repair-specific type — and the general LocalBusiness type rides alongside for
// consumers that do not know the subtype (02-RESEARCH N-2).
//
// THE JSON IS ENCODED, NEVER HAND-WRITTEN. Two properties of the encoder govern
// that choice:
//
//   * Cyrillic is emitted as \uXXXX escapes. That is VALID JSON and Google
//     parses it correctly. Do NOT "fix" the escapes by writing the JSON by hand.
//   * Forward slashes are escaped, so a literal closing script tag inside any
//     string can never terminate the block below early (T-02-11).
//
// THE RUNTIME UPGRADE RE-CHECK, PERFORMED AND RECORDED (D4-01, plan 04-06).
// This paragraph used to say that both behaviours came from the 5.2 build
// lacking the 5.4-era flags, and that a PHP upgrade would REMOVE the slash
// protection. The upgrade happened in 04-01 — this tree now runs 8.5 — and the
// warning was discharged by MEASURING the served page rather than reasoning
// about it. Result, read off https://torin.bg/new/index.html on 2026-09-20: the
// block parses as valid JSON, carries 13 escaped forward slashes and ZERO bare
// ones, and carries 45 Unicode escapes with no raw Cyrillic. Both behaviours are
// unchanged. Nothing in this file needed a code change, and none was made.
//
// BUT THE GUARANTEE IS WEAKER THAN IT WAS, AND THAT IS THE PART WORTH KEEPING.
// Under 5.2 the escaping was STRUCTURAL: the flag that switches it off did not
// exist, so it could not be switched off. Under 8.5 it is a DEFAULT, and the
// flag exists. The protection now depends on nobody passing a second argument to
// the encoder below. So: NO ENCODING FLAG MAY BE PASSED HERE, ever, and in
// particular not the one that leaves slashes bare — it would silently re-open
// T-02-11. The plan-level grep asserting that no 5.4+ JSON constant appears in
// this file is no longer a stylistic rule about a runtime that cannot use them;
// it is the control. (The constants are still not spelled out above, for the
// same reason as before: naming one would defeat that grep.)
//
// dayOfWeek values are English schema.org enums even on a Bulgarian page. They
// are identifiers, not copy — do not translate them.
require_once(dirname(__FILE__) . '/site-config.php');

// ── Opening hours (D4-26, D4-27, research P-13) ─────────────────────────────
//
// Read from the single-sourced settings keys. They used to be a hard-coded
// clock literal here, which meant the hours a search engine publishes and the
// hours the site's own footer shows could drift apart silently — nobody reading
// either one alone would see it, and the cost of being wrong is a customer
// standing at a locked door. The reason the values are stored in machine form
// rather than parsed back out of the Bulgarian display string lives beside them
// in site-config.php and is deliberately not restated here: an explanation kept
// in two places is the same defect as a value kept in two places.
//
// THE CLOSED DAYS ARE STATED, NOT INFERRED. schema.org does support reading an
// absent day as closed — "the place is open if the opens property is specified,
// and closed otherwise" — and the phase decision rested on that reading. But
// Google's own documentation takes a different route: its guidance says to show
// a business closed all day by setting opens and closes both to midnight, and
// its worked example lists the closed day explicitly. Nowhere does it state
// that omission means anything. Two extra array entries buy certainty on the
// one value on this site where an inference sends a real person to a shut shop.
//
// The owner's decision is untouched by this: the weekend closure is still
// stated in NEITHER rendered place. This is only about the JSON.
$torin_hours = array(
	array(
		'@type'     => 'OpeningHoursSpecification',
		'dayOfWeek' => $site['hours_days_open'],
		'opens'     => $site['hours_open'],
		'closes'    => $site['hours_close']
	)
);

foreach ($site['hours_days_closed'] as $torin_closed_day) {
	$torin_hours[] = array(
		'@type'     => 'OpeningHoursSpecification',
		'dayOfWeek' => $torin_closed_day,
		'opens'     => '00:00',
		'closes'    => '00:00'
	);
}

// The scheduled closure, appended conditionally — the same idiom the sameAs
// property below uses, and for the same reason: a property that would carry
// nothing is omitted rather than emitted empty.
//
// This is Google's own seasonal shape. It carries NO dayOfWeek, and it lives in
// the ordinary opening-hours property rather than the special one schema.org
// defines for exceptions — because the special one does not appear in Google's
// own example, and matching the documented shape is the whole point of the
// paragraph above.
//
// THE CONDITION IS DELIBERATELY WIDER THAN THE BANNER'S. The strip in
// includes/banner.php renders only while a closure is CURRENT, so that a
// visitor never reads «Затворено» about a week that has not arrived. Here the
// period is published as soon as it is scheduled and until it has passed,
// because the entry declares its own validity window — a search engine is told
// when the closure is, not that it is now. The two are not in conflict; they are
// the same two dates addressed to readers who need different things.
if ($site['vacation_from'] !== '' && $site['vacation_to'] !== ''
	&& date('Y-m-d') <= $site['vacation_to']) {
	$torin_hours[] = array(
		'@type'        => 'OpeningHoursSpecification',
		'opens'        => '00:00',
		'closes'       => '00:00',
		'validFrom'    => $site['vacation_from'],
		'validThrough' => $site['vacation_to']
	);
}

$torin_ld = array(
	'@context'  => 'https://schema.org',
	'@type'     => array('LocalBusiness', 'ComputerStore'),
	'name'      => 'ТОРИН КОМПЮТЪРС',   // decoded from the legacy Maps embed
	'url'       => 'https://torin.bg/',
	// Read from the single-sourced E.164 key. It used to be an independent
	// literal here, which meant the number a search engine publishes and the
	// number the page's own call buttons dial could drift apart silently —
	// nobody reading either one alone would see it. The reason the value is a
	// stored literal rather than derived from the display list now lives beside
	// the value in site-config.php, and is deliberately not restated here: an
	// explanation kept in two places is the same defect as a value kept in two
	// places.
	'telephone' => $site['phone_e164'],
	'email'     => $site['email'],
	'hasMap'    => $site['maps_url'],
	'address'   => array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => 'ул. Свети Иван Рилски 46',
		'addressLocality' => 'София',
		'addressRegion'   => 'София-град',
		'postalCode'      => '1606',
		'addressCountry'  => 'BG'
	),
	'geo' => array(
		'@type'     => 'GeoCoordinates',
		'latitude'  => $site['geo_lat'],
		'longitude' => $site['geo_lng']
	),
	// Built above, from the single-sourced settings keys plus the scheduled
	// closure. There is no literal here to fall out of step with the page.
	'openingHoursSpecification' => $torin_hours
);

// TRUST-02, structured half. sameAs points at the shop's own Google Business
// Profile and is the ONLY profile signal permitted here.
//
// What must NEVER be added beside it: any aggregate-rating property, any
// rating value, any count of customer feedback, or an array of such entries —
// under LocalBusiness, ComputerStore, Organization or any other type. A
// business marking up ITS OWN customer feedback is categorically ineligible
// (RESEARCH P-1); it is not a grey area, it is a documented manual-action
// trigger. A plan-level gate asserts the property names themselves do not
// appear anywhere in this file, which is why this paragraph describes them
// instead of spelling them. If a future phase wants stars in the search
// result, the route is the Google Business Profile itself, not this file.
//
// The key is READ, never a literal, so the profile URL lives once in
// site-config.php beside the badge that uses it. An empty value omits the
// PROPERTY ENTIRELY rather than emitting sameAs: [""] — an empty string in a
// sameAs array is a claim that the business is identified by nothing, which is
// worse-formed than saying nothing at all. Today it is empty
// (OWNER-QUESTIONS #7), so no sameAs is emitted anywhere on the site.
if (isset($site['gbp_url']) && trim($site['gbp_url']) !== '') {
	$torin_ld['sameAs'] = array($site['gbp_url']);
}
?>
<script type="application/ld+json">
<?php echo json_encode($torin_ld); ?>
</script>
<?php
// BreadcrumbList (D3-03). Emitted only when the page assigned $torin_crumbs
// before including footer.php — the homepage and every page without a parent
// hub assign nothing and get no block, rather than a one-item chain.
//
// This is driven by the SAME array the markup renders from, so the visible
// breadcrumb and the structured one cannot drift into disagreeing about the
// site's own hierarchy.
//
// The encoding rules above govern this block identically and are deliberately
// not restated: the JSON is ENCODED, never hand-written. That is what makes a
// literal closing script tag inside a crumb name harmless here.
//
// item URLs must be ABSOLUTE while every href in the markup is relative, so
// they are built from $site['base_url'] — the single place the staging path
// segment lives, and a one-line Phase 4 cutover edit.
if (isset($torin_crumbs) && is_array($torin_crumbs) && count($torin_crumbs) > 0) {
	$torin_crumb_items = array();
	$torin_crumb_pos = 0;
	foreach ($torin_crumbs as $torin_crumb_rec) {
		$torin_crumb_pos = $torin_crumb_pos + 1;
		$torin_crumb_items[] = array(
			'@type'    => 'ListItem',
			'position' => $torin_crumb_pos,
			'name'     => $torin_crumb_rec['text'],
			'item'     => $site['base_url'] . $torin_crumb_rec['href']
		);
	}
	$torin_ld_crumbs = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $torin_crumb_items
	);
?>
<script type="application/ld+json">
<?php echo json_encode($torin_ld_crumbs); ?>
</script>
<?php
}
?>
