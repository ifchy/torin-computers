<?php
// includes/footer.php — PHP 5.2-safe shared footer. Closes the #wrap element
// that header.php opens; that open/close split across two files is the
// templating contract. Phase 2 (plan 02-01) reduces this to the design-system
// shell only: the Bootstrap-4 grid/utility classes and the inline style
// attribute that used to live here are exactly the layer this phase removes.
// The full contact-first footer (D-33/D-34: address, three tel: links, hours,
// CTA row, secondary links, JSON-LD) is plan 02-03.
?>
	<footer class="site-footer">
		<div class="container">
			<p class="site-footer__legal">TORIN Company Ltd. &copy; <?php echo date("Y"); ?> г.</p>
		</div>
	</footer>

</div><!-- /#wrap -->

</body>
</html>
