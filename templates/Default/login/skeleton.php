<!DOCTYPE html>
<html>
	<head>
		<title><?php echo PAGE_TITLE; ?></title>
		<link rel="stylesheet" href="css/login.css" />
		<?php
		// Include Google Analytics global site tag if we have one
		if (!empty(GOOGLE_ANALYTICS_ID)) { ?>
			<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo GOOGLE_ANALYTICS_ID; ?>"></script>
			<script>
				window.dataLayer = window.dataLayer || [];
				function gtag() { dataLayer.push(arguments); }
				gtag('js', new Date());
				gtag('config', '<?php echo GOOGLE_ANALYTICS_ID; ?>');
			</script><?php
		} ?>
		<!--[if IE]>
		<style>
		input.inputbox {
			width:121px;
			height:18px;
		}
		</style>
		<![endif]-->
	</head>

	<body>
		<div class="center">
			<div class="collapsed">
				<img src="images/login/smr_banner.png" width="769" height="132" alt=""><br />

				<!-- small header menu -->
				<div id="small_menu">
					<img src="images/login/bottom_left.gif" width="7" height="11" alt="">
					<img src="images/login/bottom_center.png" width="222" height="11" alt="">
					<div id="small_menu_1">
						<a href="/">(Home)</a>
					</div>
					<div id="small_menu_2">
						<a href="mailto:support@smrealms.de" target="_blank">(Contact)</a>
					</div>
					<img src="images/login/bottom_right.png" width="540" height="11" alt="">
				</div>

				<!-- header menu -->
				<ul id="main_menu">
					<li><a href="<?php echo DISCORD_URL; ?>" target="discord">Discord</a></li>
					<li><a href="<?php echo WIKI_URL; ?>" target="manu">Game&nbsp;Guide</a></li>
					<li><a href="https://smrcnn.smrealms.de" target="board">Forums</a></li>
					<li><a href="<?php echo WIKI_URL; ?>/tutorials#video-tutorials" target="vid">Video&nbsp;Tutorials</a></li>
					<li><a href="album" target="ml">Merchant&nbsp;Album</a></li>
				</ul>
			</div>

			<?php
			if (isset($Message)) { ?>
				<h4 style="margin-bottom: 0px;"><?php echo $Message ?></h4><?php
			} ?>
		</div>

		<?php $this->includeTemplate($Body); ?>

	</body>
</html>
