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
			// Static, PHP-rendered replacement for the legacy otpuska.js banner.
			// That script was 550 bytes of dependency-free vanilla JS carrying
			// genuine content rather than decoration, so the safe default is to
			// preserve an equivalent as content (OWNER-QUESTIONS #8). Emptying
			// the config value removes the band with no other edit.
			if ($site['notice'] !== '') { ?>
			<p class="notice notice--info"><?php echo torin_icon('clock'); ?><span><?php echo htmlspecialchars($site['notice'], ENT_QUOTES, 'UTF-8'); ?></span></p>
<?php		} ?>

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
							<a class="footer-phone" href="tel:<?php echo htmlspecialchars(str_replace(' ', '', $torin_phone), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($torin_phone, ENT_QUOTES, 'UTF-8'); ?></a>
<?php } ?>
						</span>
					</div>

					<p class="footer-item"><?php echo torin_icon('mail'); ?><a href="mailto:<?php echo htmlspecialchars($site['email'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($site['email'], ENT_QUOTES, 'UTF-8'); ?></a></p>
				</div>

				<div class="footer-hours">
					<h2 class="footer-heading">Работно време</h2>
					<p class="footer-item footer-item--muted"><?php echo torin_icon('clock'); ?><span><?php echo htmlspecialchars($site['hours'], ENT_QUOTES, 'UTF-8'); ?></span></p>

					<?php // The same two equal-weight primary actions as the homepage CTA
					      // block (D-16) — the same component, not a footer-only variant. ?>
					<div class="cta-block__actions">
						<a class="btn btn--primary" href="tel:<?php echo htmlspecialchars(str_replace(' ', '', $site['phones'][0]), ENT_QUOTES, 'UTF-8'); ?>"><?php echo torin_icon('phone'); ?>Обадете се</a>
						<a class="btn btn--primary" href="viber://chat?number=<?php echo rawurlencode($site['viber']); ?>"><?php echo torin_icon('chat'); ?>Пишете във Viber</a>
					</div>
				</div>

				<?php // D-20's sales line stays reachable from every page: the nav item
				      // covers laptopi.html and this row carries rezervni-chasti.html
				      // alongside it, so neither sales page depends on the other. ?>
				<ul class="footer-links">
					<li><a href="about.html">за нас</a></li>
					<li><a href="warrently.html">гаранция</a></li>
					<li><a href="uslovia.html">условия</a></li>
					<li><a href="rezervni-chasti.html">резервни части</a></li>
					<li><a href="laptopi.html">лаптопи</a></li>
				</ul>

			</div>

			<p class="site-footer__legal footer-legal">TORIN Company Ltd. &copy; <?php echo date("Y"); ?> г.</p>
		</div>
	</footer>
<?php
	// One LocalBusiness block per page, from one include, single-sourced off
	// $site (D-34). Included here rather than in header.php purely so that all
	// 16 pages get it from the file they already share.
	include(dirname(__FILE__) . '/jsonld.php');
?>

</div><!-- /#wrap -->

</body>
</html>
