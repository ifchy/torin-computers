<?php
// includes/footer.php — PHP 5.2-safe contact-first footer (D-33/D-34), rendered
// on all 16 pages. Closes the #wrap element that header.php opens; that
// open/close split across two files is the templating contract.
//
// There is deliberately NO map embed here or anywhere else on the site (D-34).
// One would pull several hundred kilobytes of third-party JavaScript per page
// view, across 16 pages, working directly against DESIGN-02. The address
// deep-links to Google Maps instead, and jsonld.php publishes geo + hasMap so
// local search still gets the location at near-zero weight.
//
// Every interpolated config value is escaped before it reaches an attribute or
// a text node. The values are literals today; the escaping exists so that a
// later editor cannot turn a config change into an injection (T-02-12).
require_once(dirname(__FILE__) . '/site-config.php');
require_once(dirname(__FILE__) . '/icons.php');
?>
	<footer class="site-footer">
		<div class="container">
<?php
			// THE «NOTICE» BAND THAT STOOD HERE IS GONE — owner decision,
			// 2026-09-22. It rendered «Работно време: ...» at the top of every
			// footer, restating the hours line thirty-four lines below it.
			//
			// It was carried over as the static replacement for the hours half
			// of the legacy otpuska.js, whose live value was «В А Ж Н О !!!
			// НОВО Работно време ...» — an announcement that the hours HAD
			// CHANGED, temporary by nature. Made permanent and stripped of its
			// «НОВО», it was just a duplicate. The closure half of that script
			// survives as banner.php, which is the part OWNER-QUESTIONS #8
			// approved; this band was never what #8 asked about.
			//
			// Do not reinstate it to show working hours. The footer already has
			// one hours line, composed from the same single source (D4-26), and
			// a second one is how this site previously came to publish three
			// disagreeing copies.
?>
			<div class="footer-grid">

				<div class="footer-contact">
					<h2 class="footer-heading">Свържете се с нас</h2>

					<p class="footer-item"><?php echo torin_icon('pin'); ?><a href="<?php echo htmlspecialchars($site['maps_url'], ENT_QUOTES, 'UTF-8'); ?>" rel="noopener"><?php echo htmlspecialchars($site['address'], ENT_QUOTES, 'UTF-8'); ?></a></p>

					<div class="footer-item">
						<?php echo torin_icon('phone'); ?>
						<?php
						// Three numbers today, rendered by looping the config
						// rather than by three hand-written blocks, into a
						// wrapping stack — one number or five lays out equally
						// well. The list is never joined back into a display
						// string: each entry gets its own tel: link. The icon
						// sits on the group, not on each line, so the three read
						// as one contact channel.
						?>
						<span class="footer-phones">
<?php foreach ($site['phones'] as $torin_phone) { ?>
							<a class="footer-phone" href="tel:<?php echo htmlspecialchars(str_replace(' ', '', $torin_phone), ENT_QUOTES, 'UTF-8'); ?>" data-slot="footer-list"><?php echo htmlspecialchars($torin_phone, ENT_QUOTES, 'UTF-8'); ?></a>
<?php } ?>
						</span>
					</div>

					<p class="footer-item"><?php echo torin_icon('mail'); ?><a href="mailto:<?php echo htmlspecialchars($site['email'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($site['email'], ENT_QUOTES, 'UTF-8'); ?></a></p>
				</div>

				<div class="footer-hours">
					<h2 class="footer-heading">Работно време</h2>
					<p class="footer-item footer-item--muted"><?php echo torin_icon('clock'); ?><span><?php echo htmlspecialchars($site['hours'], ENT_QUOTES, 'UTF-8'); ?></span></p>

					<?php // The same two equal-weight primary actions as the homepage CTA
					      // block (D-16) — the same component, not a footer-only variant.
					      //
					      // The call action reads the E.164 key, not the first display
					      // entry of the phone list: stripping spaces from that entry
					      // yields the LOCAL form, which is why this footer and the
					      // homepage hero used to dial two different strings for one
					      // number. Every primary call CTA on the site now resolves the
					      // one key. The three display links above are a different job
					      // and deliberately keep their local form. ?>
					<div class="cta-block__actions">
						<a class="btn btn--primary" href="tel:<?php echo htmlspecialchars($site['phone_e164'], ENT_QUOTES, 'UTF-8'); ?>" data-slot="footer-cta"><?php echo torin_icon('phone'); ?>Обадете се</a>
						<?php // D4-17 (§C-7). The write action leads to kontakti.html; the chat
						      // glyph is reused, not replaced. This is the slot that decided the
						      // label: §Conflicts C-1 measured this grid column at 233.6px at
						      // 560px and 246.7px at 900px, where the longer phrase renders as a
						      // TWO-LINE button beside a 48px call button — which is the exact
						      // equal-weight pairing D4-17 exists to preserve. ?>
						<a class="btn btn--primary" href="kontakti.html" data-slot="footer"><?php echo torin_icon('chat'); ?>Пишете ни</a>
					</div>
				</div>

				<?php // D-33's secondary row. It carried two sales links until plan
				      // 03.5-01; the shop discontinued sales (CONTEXT D3.5-01), so both
				      // are gone here and from the nav, and both URLs 301 from
				      // src/.htaccess. Three items now — a subtraction, not a
				      // rebalancing. ?>
				<?php // The third label is «поверителност», NOT «условия» — corrected
				      // 2026-09-24 at the owner's review of GATE 0.4. uslovia.html is
				      // wholly a Декларация за поверителност: what is collected, why,
				      // retention, enquiry-form data, visit statistics. It contains no
				      // general terms, and none are owed — общи условия bind distance
				      // contracts, and this site sells nothing, takes no payment and
				      // holds no accounts. The shop's actual terms are the warranty
				      // ones, which «гаранция» above already names correctly.
				      //
				      // Do not restore «условия» here. The contact form's consent
				      // label is the one place that word belongs, and it is already
				      // scoped precisely — «условията за обработка на личните ми
				      // данни» — so the consent stays accurate while this link stops
				      // promising a document that does not exist. ?>
				<ul class="footer-links">
					<li><a href="about.html">за нас</a></li>
					<li><a href="warrently.html">гаранция</a></li>
					<li><a href="uslovia.html">поверителност</a></li>
				</ul>

			</div>

			<?php // The legal line carried an anchor to the EU-publicity page until
			      // plan 03.5-01. It is gone, and so is the page: the owner confirmed
			      // on 2026-09-12 that the operational-programme publicity obligation
			      // has expired and only document retention remains (CONTEXT D3.5-09,
			      // OWNER-QUESTIONS #4). The disclosure itself was NOT dropped — plan
			      // 03-05 relocated it into about.html, and the retired URL 301s
			      // there from src/.htaccess, so the statement and its inbound links
			      // both survive the page that used to hold them.
			      //
			      // The reasoning that put the anchor here is therefore SPENT, not
			      // overruled: it closed a live publicity-audit exposure while the
			      // obligation ran. If a future phase reintroduces a legal notice
			      // needing site-wide reachability, this line is where it goes — and
			      // it still must NOT be promoted into the navigation, the contact
			      // block, or D-33's secondary row above, for the reason D-35 gave:
			      // a legal notice competing for attention with service links loses,
			      // and drags the service links down with it. ?>
			<p class="site-footer__legal footer-legal">TORIN Company Ltd. &copy; <?php echo date("Y"); ?> г.</p>
		</div>
	</footer>
<?php
	// One LocalBusiness block per page, from one include, single-sourced off
	// $site (D-34). Included here rather than in header.php purely so that all
	// 16 pages get it from the file they already share.
	include(dirname(__FILE__) . '/jsonld.php');

	// ── The settings sentinel (OWNER-01, D4-23) ─────────────────────────────
	//
	// WHY A PAGE EMITS THIS AT ALL. includes/settings.php falls back per key
	// when the owner's settings.txt is absent or a line in it is malformed, and
	// a fallback renders IDENTICALLY to a success — that is the entire design
	// goal, and it is also what makes a silent one undetectable. This line is
	// the same answer asset-version.php gives with its ?v=0 token: make the
	// degraded state visible in the served page so a remote check can assert
	// against it, rather than hoping someone notices the hours are last
	// month's.
	//
	// HOW TO READ IT. `fallback=none` means every managed key came from the
	// owner's file. A list of names means those keys came from the literals in
	// site-config.php. With NO settings.txt on the server every key is listed,
	// and that is the shipped state and not a fault — the interesting case is a
	// SHORT list, which means the file exists and one line in it is wrong.
	//
	// It carries key NAMES only, never values, so it discloses nothing the
	// rendered page does not already say out loud. It is a comment rather than
	// an attribute or a header so that it survives a plain curl, costs nothing
	// to the layout, and cannot be styled or scripted against by accident.
	$torin_fb = isset($site['settings_fallbacks']) ? $site['settings_fallbacks'] : array();
	echo "\n" . '<!-- torin-settings: fallback='
		. (count($torin_fb) > 0 ? implode(',', $torin_fb) : 'none')
		. ' -->';
	unset($torin_fb);
?>

</div><!-- /#wrap -->

</body>
</html>
