<?php
// includes/header.php — PHP 5.2-safe shared head + contact-info chrome.
// Extracted from site-current/index.html lines 1-80 (head block + secondarybar
// contact-info block). Values are pulled from site-config.php, not hardcoded.
require_once(dirname(__FILE__) . '/site-config.php');
?>
<!DOCTYPE html>
<html lang="bg">
<head>

	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="theme-color" content="#3ed2a7">

	<title>ТОРИН КОМПЮТЪРС - ТОТАЛЕН РЕМОНТ НА ЛАПТОПИ</title>

</head>

<div id="wrap">

	<header class="main-header main-header-overlay">

		<div class="secondarybar-wrap bg-white py-1">
			<div class="container secondarybar-container">
				<div class="secondarybar">
					<div class="row secondarybar-row align-items-center">

						<div class="lqd-column col-auto">

							<div class="header-module">
								<div class="iconbox iconbox-inline iconbox-xs">
									<div class="iconbox-icon-wrap">
										<span class="iconbox-icon-container font-size-16">
											<i class="fa fa-phone"></i>
										</span>
									</div>
									<h3 class="font-size-14">Телефон: <?php echo $site['phone']; ?></h3>
								</div>
							</div>

							<div class="header-module">
								<div class="iconbox iconbox-inline iconbox-xs">
									<div class="iconbox-icon-wrap">
										<span class="iconbox-icon-container font-size-16">
											<i class="fa fa-envelope-o"></i>
										</span>
									</div>
									<h3 class="font-size-14">E-mail: <a href="mailto:<?php echo $site['email']; ?>"><?php echo $site['email']; ?></a></h3>
								</div>
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>

	</header>
