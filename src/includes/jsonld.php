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
// THE JSON IS ENCODED, NEVER HAND-WRITTEN. Two facts about this host's PHP
// 5.2.17 build govern that choice:
//
//   * The 5.4-era flag that would leave Unicode unescaped does not exist here,
//     so Cyrillic is emitted as \uXXXX escapes. That is VALID JSON and Google
//     parses it correctly. Do NOT "fix" the escapes by writing the JSON by hand.
//   * The 5.4-era flag that would leave forward slashes unescaped does not
//     exist here either, and that absence is a SAFETY BENEFIT: this build always
//     escapes "/", so a literal closing script tag inside any string can never
//     terminate the block below early (T-02-11). A future PHP upgrade REMOVES
//     that protection — whoever performs one must re-check this file.
//
// (Neither flag is spelled out above on purpose: a plan-level grep asserts that
// no 5.4+ JSON constant appears in this file, and naming them would defeat it.)
//
// dayOfWeek values are English schema.org enums even on a Bulgarian page. They
// are identifiers, not copy — do not translate them.
require_once(dirname(__FILE__) . '/site-config.php');

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
	// [ASSUMED] These hours are the unconfirmed two-of-three majority — see
	// site-config.php and OWNER-QUESTIONS #20. This is the copy Google acts on.
	'openingHoursSpecification' => array(
		array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
			'opens'     => '08:00',
			'closes'    => '16:00'
		)
	)
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
