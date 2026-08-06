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
	// E.164 form of the main line. Kept as a literal rather than derived from
	// $site['phones']: mapping a local 0-prefixed number to +359 is a dialling
	// rule, not a string operation, and inventing one here would be the kind of
	// silent guess this file exists to avoid.
	'telephone' => '+35929549710',
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
?>
<script type="application/ld+json">
<?php echo json_encode($torin_ld); ?>
</script>
