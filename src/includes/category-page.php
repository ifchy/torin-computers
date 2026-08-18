<?php
// includes/category-page.php — PHP 5.2-safe (array() only, named functions
// only, no namespaces, no short echo tags, tabs). Emits nothing on include:
// it is function definitions and no top-level output, exactly like
// categories.php and site-config.php.
//
// The D-24 category page template — a required spine plus optional blocks.
// Consumed today by the three PUBLISHED category pages:
//   mehanichni-problemi.html · optimizatsiq.html · zalivane-technosti.html
//
// The three UNPUBLISHED categories deliberately have no page file yet. D-23
// gates publication on genuine content and D-25 assigns content-depth targets
// to Phase 3, so until then their cards and their nav entries route to their
// own homepage anchors through torin_category_href(). Phase 3 creates each
// file and flips one boolean; nothing here needs to change for that.
//
// The category display name and the symptom line are read from
// $torin_categories via the record id, never retyped on a page — so a D-40
// rename cannot leave a stale name behind on a category page.
//
// PHASE 3 DEPENDENCY, recorded here so it is not mistaken for an omission:
// the intro and the warranty summary (TRUST-03) belong to D-24's required
// spine, but their CONTENT is a Phase 3 deliverable (D-25). They are therefore
// guarded exactly like the optional blocks, so a page that has not been given
// them yet renders cleanly instead of shipping a heading with nothing under
// it. A heading with no body is worse than an absent section: it is precisely
// the thin-content signal the publish gate exists to avoid. Unused slots cost
// nothing in search, which is why D-24 chose core-plus-optional over a lean
// fixed template — a lean template would cap depth on the strongest
// categories, which are the ones that could actually rank.
//
// PHASE 3 GENERALIZATION. The noun this file renders is now the SERVICE PAGE.
// A CATEGORY page is the variant that carries a cat_id; the five D3-03 child
// pages are the variant that does not. One renderer, not two: two renderers
// means every later slot change lands twice, and P-5 records the concrete
// failure a second one invites.
//
// Recognised $page keys:
//   cat_id       string  optional — the record id in $torin_categories. When
//                        present it supplies the display name and the symptom
//                        line. When absent, 'name' and 'symptoms' do.
//   name         string  display name for a page with no cat_id (D3-03 children)
//   symptoms     string  symptom line for a page with no cat_id
//   h1           string  optional heading override (SEO-01/D3-14). Absent or
//                        present-but-empty falls back to the display name.
//   crumbs       list    entries carrying 'text' and 'href'; max depth 3
//   intro        string  one paragraph
//   fixes        list    entries carrying a 'text' key and an optional 'href' key
//   blocks       list    structured content entries — see torin_render_block()
//   warranty_key string  selects an entry from $site['warranty'] (TRUST-03)
//   warranty     string  LEGACY scalar summary; still renders as one <p>
//   process      list    ordered steps, each entry a string
//   faq          list    entries carrying a 'q' key and an 'a' key
//   related      list    entries carrying a 'text' key and an optional 'href' key
//   prices       list    entries, each a string
//
// A page that renders breadcrumbs should also assign the same data to
// $torin_crumbs before including footer.php, which is where jsonld.php reads it
// to emit the matching BreadcrumbList. The markup and the structured data are
// therefore driven by ONE array on the page, never by two that can disagree.
//
// dirname(__FILE__) is the 5.2-safe include idiom; the 5.3+ magic directory
// constant must never be introduced anywhere in this tree.
require_once(dirname(__FILE__) . '/categories.php');
require_once(dirname(__FILE__) . '/site-config.php');
require_once(dirname(__FILE__) . '/icons.php');
// TRUST-01 and TRUST-02 are ONE partial each, included in two places — here for
// every service page and from index.html for the homepage — never duplicated
// markup. Two copies of the brand row is how the homepage and a service page
// end up disagreeing about which manufacturers the shop accepts.
//
// These two files require_once THIS file back, for torin_has_content() and
// torin_esc(). That mutual include is safe in both directions and the
// mechanism is written out in full in brand-row.php's head comment.
require_once(dirname(__FILE__) . '/brand-row.php');
require_once(dirname(__FILE__) . '/rating-badge.php');

// torin_category_by_id() used to be defined here. It MOVED to categories.php,
// beside the data it looks up and beside torin_category_href(), because
// services.php needs the same lookup to resolve a child page parent and a data
// file must not have to include the renderer to do it. Every caller is
// unchanged: categories.php is required above, so the name resolves exactly as
// before. This note exists so the next reader does not conclude it was lost.

// True only when a key carries something worth rendering. Both halves matter:
// a present-but-empty value must omit its block exactly like an absent key, or
// Phase 3 assigning an empty string would quietly reintroduce the empty
// heading these guards exist to prevent.
function torin_has_content($value) {
	if (is_array($value)) {
		return count($value) > 0;
	}
	return trim($value) !== '';
}

// PHP 5.2 defaults htmlspecialchars() to ISO-8859-1 and every string on these
// pages is Cyrillic, so the charset argument is always passed. Same convention
// as header.php, footer.php and index.html — collected here because this file
// escapes on many more lines than they do.
function torin_esc($value) {
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// A list entry renders as a link when it carries an href and as plain text
// otherwise. This is what lets профилактика be cross-listed as a LINK to the
// one existing page rather than as duplicated body copy (D-28): one URL, one
// canonical, no duplicate-content exposure, and the internal link from a
// second topical context is the entire benefit.
function torin_render_svc_item($item) {
	if (isset($item['href']) && trim($item['href']) !== '') {
		echo '<a href="' . torin_esc($item['href']) . '">' . torin_esc($item['text']) . '</a>';
		return;
	}
	echo torin_esc($item['text']);
}

// The running tint toggle (UI-SPEC C3-5). The three literals this replaces were
// hardcoded onto specific slots while EVERY slot is optional, so a page with
// «Какво ремонтираме» and no гаранция already rendered two adjacent tinted bands
// before Phase 3 added a section between them. The state is flipped once per
// EMITTED section — not per possible section — which is what makes alternation
// correct for every combination of filled slots, including the ones nobody has
// written yet. It starts true so that the first flip yields the untinted page
// surface: the page opens on white, exactly as it did before.
//
// This is the one place in the file that names the class. Passing the state by
// reference rather than keeping it in a global is deliberate — two service pages
// rendered in one request would otherwise share one toggle.
function torin_next_tint(&$state) {
	$state = !$state;
	if ($state) {
		return ' section--tint';
	}
	return '';
}

// Breadcrumbs (D3-03). Every item but the last is a link; the last is a span
// carrying aria-current, never a link to the page you are already on. Depth is
// capped at 3 because the hub/child structure has no deeper path and an
// unbounded chain is how a breadcrumb row starts scrolling sideways on a phone.
// Separators are CSS generated content, so they stay out of the accessible name.
function torin_render_breadcrumbs($crumbs) {
	if (!is_array($crumbs) || count($crumbs) === 0) {
		return;
	}
	$torin_crumb_list = array_slice($crumbs, 0, 3);
	$torin_crumb_last = count($torin_crumb_list) - 1;
	$torin_crumb_i = 0;
?>
			<nav class="breadcrumbs" aria-label="Навигация по раздели">
				<ol>
<?php	foreach ($torin_crumb_list as $torin_crumb) {
			if ($torin_crumb_i === $torin_crumb_last || !isset($torin_crumb['href']) || trim($torin_crumb['href']) === '') { ?>
					<li><span aria-current="page"><?php echo torin_esc($torin_crumb['text']); ?></span></li>
<?php		} else { ?>
					<li><a href="<?php echo torin_esc($torin_crumb['href']); ?>"><?php echo torin_esc($torin_crumb['text']); ?></a></li>
<?php		}
			$torin_crumb_i = $torin_crumb_i + 1;
		} ?>
				</ol>
			</nav>
<?php
}

// One structured content block (CONTENT-01 / UI-SPEC §5).
//
// The slot is a STRUCTURED SUB-ARRAY and deliberately NOT a raw-HTML sink: a
// passthrough key would be this project's first unescaped output path and its
// first real XSS surface. Every leaf below still goes through torin_esc(); it is
// the STRUCTURE, not the string, that carries the formatting. Writing an HTML
// sanitiser on PHP 5.2 against Cyrillic input is a genuinely hard problem with a
// long CVE history (DH-5) — this avoids needing one at all.
//
// kind and tone are both validated against literal whitelists and both resolve
// to a class SELECTED here, never interpolated from the data. An unrecognised
// kind, or an entry missing its heading or its items, is skipped SILENTLY — the
// same treatment torin_icon() gives an unknown icon name. Half-formed markup on
// a live page is worse than an absent block.
function torin_render_block($block) {
	$torin_kind = isset($block['kind']) ? $block['kind'] : '';
	if ($torin_kind === 'prose') {
		$torin_kind_class = ' svc__block--prose';
	} elseif ($torin_kind === 'steps') {
		$torin_kind_class = ' svc__block--steps';
	} elseif ($torin_kind === 'callout') {
		$torin_kind_class = ' svc__block--callout';
	} else {
		return;
	}
	if (!isset($block['heading']) || !torin_has_content($block['heading'])) {
		return;
	}
	if (!isset($block['items']) || !torin_has_content($block['items'])) {
		return;
	}
	$torin_tone_class = '';
	if (isset($block['tone']) && $block['tone'] === 'urgent') {
		$torin_tone_class = ' svc__block--urgent';
	}
?>
			<div class="svc__block<?php echo $torin_kind_class; ?><?php echo $torin_tone_class; ?>">
				<span class="eyebrow" aria-hidden="true"></span>
				<h2><?php echo torin_esc($block['heading']); ?></h2>
<?php	if ($torin_kind === 'steps') { ?>
				<ol>
<?php		foreach ($block['items'] as $torin_item) { ?>
					<li><?php echo torin_esc($torin_item); ?></li>
<?php		} ?>
				</ol>
<?php	} else {
			foreach ($block['items'] as $torin_item) { ?>
				<p><?php echo torin_esc($torin_item); ?></p>
<?php		}
		}
		if (isset($block['link']) && isset($block['link']['text']) && torin_has_content($block['link']['text'])
			&& isset($block['link']['href']) && torin_has_content($block['link']['href'])) { ?>
				<p class="svc__block__link"><a href="<?php echo torin_esc($block['link']['href']); ?>"><?php echo torin_esc($block['link']['text']); ?></a></p>
<?php	} ?>
			</div>
<?php
}

// Back-compat alias, kept so that no already-published page breaks mid-phase.
// mehanichni-problemi.html and optimizatsiq.html call this name and are not
// touched by this plan; they must keep rendering byte-for-byte the same spine.
function torin_render_category_page($page) {
	torin_render_service_page($page);
}

function torin_render_service_page($page) {
	global $site;

	// cat_id is now OPTIONAL. With it, the display name and the symptom line
	// come from the shared record, so a D-40 rename cannot strand a stale name
	// on a page. Without it, the page supplies its own — that is the D3-03 child
	// case, where there is no category record to be the single writer.
	$cat = null;
	if (isset($page['cat_id']) && trim($page['cat_id']) !== '') {
		$cat = torin_category_by_id($page['cat_id']);
	}
	if ($cat !== null) {
		$torin_name     = $cat['name'];
		$torin_symptoms = $cat['symptoms'];
	} else {
		$torin_name     = isset($page['name'])     ? $page['name']     : '';
		$torin_symptoms = isset($page['symptoms']) ? $page['symptoms'] : '';
	}

	// The silent early return, and why it is worth naming rather than just
	// writing (P-5): a page that reaches this renderer with an unresolvable
	// cat_id and no name of its own serves a full header, a full footer and
	// literally NOTHING between them, at HTTP 200 — precisely the thin-content
	// shape the D-23 publish gate exists to prevent, and invisible to any check
	// that only asserts the page responds. If a published page ever renders
	// blank, this line is the first place to look.
	if (!torin_has_content($torin_name)) {
		return;
	}

	// The h1 defaults to the display name for the reason the hardwired version
	// existed: a D-40 rename must not leave a stale heading behind. The override
	// is opt-in, one line per page, and exists because a category name is a
	// navigation label while an h1 is a search target — «Оптимизация» is 11
	// characters and carries neither a keyword nor «София» (P-3). An override
	// that is present but empty falls back, so an empty h1 can never render.
	$torin_h1 = $torin_name;
	if (isset($page['h1']) && torin_has_content($page['h1'])) {
		$torin_h1 = $page['h1'];
	}

	// Every optional block is decided here, once, and an unmet guard emits
	// NOTHING AT ALL — not an empty heading, not an empty section wrapper.
	$torin_has_crumbs   = isset($page['crumbs'])   && torin_has_content($page['crumbs']);
	$torin_has_intro    = isset($page['intro'])    && torin_has_content($page['intro']);
	$torin_has_fixes    = isset($page['fixes'])    && torin_has_content($page['fixes']);
	$torin_has_blocks   = isset($page['blocks'])   && torin_has_content($page['blocks']);
	$torin_has_process  = isset($page['process'])  && torin_has_content($page['process']);
	$torin_has_faq      = isset($page['faq'])      && torin_has_content($page['faq']);
	$torin_has_related  = isset($page['related'])  && torin_has_content($page['related']);
	$torin_has_prices   = isset($page['prices'])   && torin_has_content($page['prices']);
	$torin_has_symptoms = torin_has_content($torin_symptoms);

	// TRUST-03 resolution, in three cases and in this order:
	//   1. a LEGACY scalar (or array) assigned to 'warranty' wins, so the two
	//      already-published pages keep whatever they set (UI-SPEC C3-7);
	//   2. a present-but-empty 'warranty' omits the whole section rather than
	//      shipping an empty «Гаранция» heading (P-4);
	//   3. otherwise 'warranty_key' selects a shared entry, and an ABSENT or
	//      UNKNOWN key falls back to 'default' rather than rendering nothing —
	//      a page that asked for a warranty always gets one.
	// A page that sets neither key gets no warranty section at all, which is
	// what the two published pages do today.
	$torin_warranty = null;
	if (isset($page['warranty'])) {
		if (torin_has_content($page['warranty'])) {
			$torin_warranty = $page['warranty'];
		}
	} elseif (isset($page['warranty_key'])) {
		$torin_warranty_key = $page['warranty_key'];
		if (!isset($site['warranty'][$torin_warranty_key])) {
			$torin_warranty_key = 'default';
		}
		$torin_warranty = $site['warranty'][$torin_warranty_key];
	}
	$torin_has_warranty = ($torin_warranty !== null);

	// One toggle for this page's whole spine. See torin_next_tint().
	$torin_tint = true;
?>
	<section class="section<?php echo torin_next_tint($torin_tint); ?> svc">
		<div class="container">
<?php	if ($torin_has_crumbs) { torin_render_breadcrumbs($page['crumbs']); } ?>
			<h1><?php echo torin_esc($torin_h1); ?></h1>
<?php	if ($torin_has_intro) { ?>
			<p class="svc__intro"><?php echo torin_esc($page['intro']); ?></p>
<?php	} ?>
		</div>
	</section>
<?php
	// «Какво ремонтираме» plus the symptom line. The section appears only if
	// at least one of the two has content, and each half is guarded again
	// inside, so neither can ever produce a heading standing on its own.
	if ($torin_has_fixes || $torin_has_symptoms) { ?>
	<section class="section<?php echo torin_next_tint($torin_tint); ?> svc__fixes">
		<div class="container">
			<span class="eyebrow" aria-hidden="true"></span>
<?php		if ($torin_has_fixes) { ?>
			<h2>Какво ремонтираме</h2>
			<ul>
<?php			foreach ($page['fixes'] as $torin_fix) { ?>
				<li><?php torin_render_svc_item($torin_fix); ?></li>
<?php			} ?>
			</ul>
<?php		} ?>
<?php		if ($torin_has_symptoms) { ?>
			<p class="svc__symptoms"><?php echo torin_esc($torin_symptoms); ?></p>
<?php		} ?>
		</div>
	</section>
<?php	}

	// The whole blocks group is ONE section, not one per entry. That is what
	// keeps the emitted section count independent of how many blocks a page
	// carries, which is in turn what keeps the tint toggle deterministic. It
	// sits immediately after «Какво ремонтираме» and BEFORE гаранция because the
	// liquid-damage first-aid list lives here: a panicking visitor needs
	// «изключете адаптера» before they need the FAQ, and urgent content below
	// the FAQ is a design failure.
	if ($torin_has_blocks) { ?>
	<section class="section<?php echo torin_next_tint($torin_tint); ?> svc__blocks">
		<div class="container">
<?php		foreach ($page['blocks'] as $torin_block) {
				torin_render_block($torin_block);
			} ?>
		</div>
	</section>
<?php	}

	// TRUST-03. The term line is its own element so the DURATION is scannable
	// without reading the paragraph. The full-terms href renders as a trailing
	// link paragraph rather than inline, because every leaf here is escaped and
	// an escaped slot cannot carry inline markup — the same shape as a block's
	// link. Reuses the existing section and its h2; no new heading level.
	if ($torin_has_warranty) { ?>
	<section class="section<?php echo torin_next_tint($torin_tint); ?> svc__warranty">
		<div class="container">
			<span class="eyebrow" aria-hidden="true"></span>
			<h2>Гаранция</h2>
<?php		if (is_array($torin_warranty)) { ?>
			<p class="svc__warranty__term"><?php echo torin_esc($torin_warranty['term']); ?></p>
<?php			if (isset($torin_warranty['detail']) && torin_has_content($torin_warranty['detail'])) { ?>
			<p><?php echo torin_esc($torin_warranty['detail']); ?></p>
<?php			}
				if (isset($torin_warranty['href']) && torin_has_content($torin_warranty['href'])) { ?>
			<p class="svc__warranty__link"><a href="<?php echo torin_esc($torin_warranty['href']); ?>">Пълни гаранционни условия</a></p>
<?php			}
			} else { ?>
			<p><?php echo torin_esc($torin_warranty); ?></p>
<?php		} ?>
		</div>
	</section>
<?php	}

	if ($torin_has_process) { ?>
	<section class="section<?php echo torin_next_tint($torin_tint); ?> svc__process">
		<div class="container">
			<span class="eyebrow" aria-hidden="true"></span>
			<h2>Как работим</h2>
			<ol>
<?php		foreach ($page['process'] as $torin_step) { ?>
				<li><?php echo torin_esc($torin_step); ?></li>
<?php		} ?>
			</ol>
		</div>
	</section>
<?php	}

	// FAQ reuses the homepage catch-all's native disclosure component: every
	// answer ships as real HTML in the served response, opens without any
	// script, and keeps native find-in-page reveal.
	if ($torin_has_faq) { ?>
	<section class="section<?php echo torin_next_tint($torin_tint); ?> svc__faq">
		<div class="container">
			<span class="eyebrow" aria-hidden="true"></span>
			<h2>Често задавани въпроси</h2>
<?php		foreach ($page['faq'] as $torin_qa) { ?>
			<details class="disc" name="svc-faq">
				<summary><span class="disc__row"><?php echo torin_esc($torin_qa['q']); ?> <?php echo torin_icon('chevron-down'); ?></span></summary>
				<div class="disc__body">
					<p><?php echo torin_esc($torin_qa['a']); ?></p>
				</div>
			</details>
<?php		} ?>
		</div>
	</section>
<?php	}

	if ($torin_has_related) { ?>
	<section class="section<?php echo torin_next_tint($torin_tint); ?> svc__related">
		<div class="container">
			<span class="eyebrow" aria-hidden="true"></span>
			<h2>Свързани услуги</h2>
			<ul>
<?php		foreach ($page['related'] as $torin_rel) { ?>
				<li><?php torin_render_svc_item($torin_rel); ?></li>
<?php		} ?>
			</ul>
		</div>
	</section>
<?php	}

	if ($torin_has_prices) { ?>
	<section class="section<?php echo torin_next_tint($torin_tint); ?> svc__prices">
		<div class="container">
			<span class="eyebrow" aria-hidden="true"></span>
			<h2>Ориентировъчни цени</h2>
			<ul>
<?php		foreach ($page['prices'] as $torin_price) { ?>
				<li><?php echo torin_esc($torin_price); ?></li>
<?php		} ?>
			</ul>
		</div>
	</section>
<?php	}

	// TRUST-01. The brand row sits immediately above the CTA on every service
	// page — the last thing read before the visitor is asked to call, which is
	// where "yes, they take my machine" is worth the most. It is handed the
	// RUNNING TINT rather than choosing its own surface, so inserting it here
	// cannot produce two adjacent tinted bands on a page whichever optional
	// slots that page happens to fill (C3-5).
	torin_render_brand_row(torin_next_tint($torin_tint));

	// The CTA closes the spine on every category page. Same component as the
	// homepage CTA block (D-16), not a category-only variant, and both values
	// come from the site config rather than being retyped — the chat number is
	// still [ASSUMED] (OWNER-QUESTIONS #21), and it must stay in exactly one
	// place so a single later edit fixes every page at once.
?>
	<section class="section<?php echo torin_next_tint($torin_tint); ?>" id="contact-us">
		<div class="container">
			<span class="eyebrow" aria-hidden="true"></span>
			<h2>Свържете се с нас</h2>

			<div class="cta-block">
				<div class="cta-block__actions">
					<?php // The E.164 key, not the first display entry of the phone list:
					      // one telephone fact, one representation, every primary call CTA
					      // on the site resolving the same string (see site-config.php). ?>
					<a class="btn btn--primary" href="tel:<?php echo torin_esc($site['phone_e164']); ?>"><?php echo torin_icon('phone'); ?>Обадете се</a>
					<a class="btn btn--primary" href="viber://chat?number=<?php echo rawurlencode($site['viber']); ?>"><?php echo torin_icon('chat'); ?>Пишете във Viber</a>
				</div>
				<p class="cta-block__note">Безплатна диагностика · Отговаряме в работно време</p>
<?php			// TRUST-02, as the LAST CHILD of .cta-block — the rating sits where
				// the visitor is being asked to call. It renders nothing today
				// (OWNER-QUESTIONS #7) and that absence is the specified state, not
				// an omission; see rating-badge.php.
				torin_render_rating_badge(); ?>
			</div>
		</div>
	</section>
<?php
}
?>
