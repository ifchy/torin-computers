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
// Recognised $page keys:
//   cat_id    string  required — the record id in $torin_categories
//   intro     string  one paragraph (Phase 3)
//   fixes     list    entries carrying a 'text' key and an optional 'href' key
//   warranty  string  TRUST-03 summary (Phase 3)
//   process   list    ordered steps, each entry a string
//   faq       list    entries carrying a 'q' key and an 'a' key
//   related   list    entries carrying a 'text' key and an optional 'href' key
//   prices    list    entries, each a string
//
// dirname(__FILE__) is the 5.2-safe include idiom; the 5.3+ magic directory
// constant must never be introduced anywhere in this tree.
require_once(dirname(__FILE__) . '/categories.php');
require_once(dirname(__FILE__) . '/site-config.php');
require_once(dirname(__FILE__) . '/icons.php');

// Record lookup by id. A plain named function, never a closure — closures do
// not exist on PHP 5.2. Returns null rather than a fabricated record, so a
// typo'd id renders nothing instead of a page of blanks.
function torin_category_by_id($id) {
	global $torin_categories;
	foreach ($torin_categories as $torin_cat_rec) {
		if ($torin_cat_rec['id'] === $id) {
			return $torin_cat_rec;
		}
	}
	return null;
}

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

function torin_render_category_page($page) {
	global $site;

	$cat = torin_category_by_id($page['cat_id']);
	if ($cat === null) {
		return;
	}

	// Every optional block is decided here, once, and an unmet guard emits
	// NOTHING AT ALL — not an empty heading, not an empty section wrapper.
	$torin_has_intro    = isset($page['intro'])    && torin_has_content($page['intro']);
	$torin_has_fixes    = isset($page['fixes'])    && torin_has_content($page['fixes']);
	$torin_has_warranty = isset($page['warranty']) && torin_has_content($page['warranty']);
	$torin_has_process  = isset($page['process'])  && torin_has_content($page['process']);
	$torin_has_faq      = isset($page['faq'])      && torin_has_content($page['faq']);
	$torin_has_related  = isset($page['related'])  && torin_has_content($page['related']);
	$torin_has_prices   = isset($page['prices'])   && torin_has_content($page['prices']);
	$torin_has_symptoms = torin_has_content($cat['symptoms']);
?>
	<section class="section svc">
		<div class="container">
			<h1><?php echo torin_esc($cat['name']); ?></h1>
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
	<section class="section section--tint svc__fixes">
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
			<p class="svc__symptoms"><?php echo torin_esc($cat['symptoms']); ?></p>
<?php		} ?>
		</div>
	</section>
<?php	}

	if ($torin_has_warranty) { ?>
	<section class="section svc__warranty">
		<div class="container">
			<span class="eyebrow" aria-hidden="true"></span>
			<h2>Гаранция</h2>
			<p><?php echo torin_esc($page['warranty']); ?></p>
		</div>
	</section>
<?php	}

	if ($torin_has_process) { ?>
	<section class="section section--tint svc__process">
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
	<section class="section svc__faq">
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
	<section class="section section--tint svc__related">
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
	<section class="section svc__prices">
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

	// The CTA closes the spine on every category page. Same component as the
	// homepage CTA block (D-16), not a category-only variant, and both values
	// come from the site config rather than being retyped — the chat number is
	// still [ASSUMED] (OWNER-QUESTIONS #21), and it must stay in exactly one
	// place so a single later edit fixes every page at once.
?>
	<section class="section" id="contact-us">
		<div class="container">
			<span class="eyebrow" aria-hidden="true"></span>
			<h2>Свържете се с нас</h2>

			<div class="cta-block">
				<div class="cta-block__actions">
					<a class="btn btn--primary" href="tel:<?php echo torin_esc(str_replace(' ', '', $site['phones'][0])); ?>"><?php echo torin_icon('phone'); ?>Обадете се</a>
					<a class="btn btn--primary" href="viber://chat?number=<?php echo rawurlencode($site['viber']); ?>"><?php echo torin_icon('chat'); ?>Пишете във Viber</a>
				</div>
				<p class="cta-block__note">Безплатна диагностика · Отговаряме в работно време</p>
			</div>
		</div>
	</section>
<?php
}
?>
