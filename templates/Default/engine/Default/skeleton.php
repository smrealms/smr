<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<title><?php echo $Title; ?> <?php if(isset($GameName)) echo $GameName; ?></title>
		<meta http-equiv="pragma" content="no-cache"><?php
		if(!is_object($ThisAccount) || $ThisAccount->isDefaultCSSEnabled()) { ?>
			<link rel="stylesheet" type="text/css" href="<?php echo $CSSLink; ?>">
			<link rel="stylesheet" type="text/css" href="<?php echo $CSSColourLink; ?>" /><?php
		}
		if(isset($ExtraCSSLink)) {
			?><link rel="stylesheet" type="text/css" href="<?php echo $ExtraCSSLink; ?>"><?php
		} ?>
		<style type="text/css">
			body {
				font-size:<?php echo $FontSize-20; ?>%;
			}<?php
			if (is_object($ThisAccount)) { ?>
			/* Friend vs Foe shades */

			.enemy, .enemy:hover { {
				color: #<?php echo $ThisAccount->getEnemyColor(); ?>;
			}
			.enemyBack, .enemyBack:hover {
				background-color: #<?php echo $ThisAccount->getEnemyColor(); ?>;
			}

			.friendly, .friendly:hover {
				color: #<?php echo $ThisAccount->getFriendlyColor(); ?>;
			}
			.friendlyBack, .friendlyBack:hover {
				background-color: #<?php echo $ThisAccount->getFriendlyColor(); ?>;
			}

			.neutral, .neutral:hover {
				color: #<?php echo $ThisAccount->getNeutralColor(); ?>;
			}
			.neutralBack, .neutralBack:hover {
				background-color: #<?php echo $ThisAccount->getNeutralColor(); ?>;
			}<?php 
			} ?>
		</style><?php
		if(isset($HeaderTemplateInclude)) {
			$this->includeTemplate($HeaderTemplateInclude);
		} ?>
		<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
		<script type="text/javascript" src="js/smr15.js"></script>
		<?php if(isset($ExtraJSLink)) { ?>
			<script type="text/javascript" src="<?php echo $ExtraJSLink; ?>"></script>
		<?php } ?>

	</head>
	<body>
		<table class="m" align="center">
			<tr>
				<td class="l0" rowspan="2">
					<div class="l1">
						<?php $this->includeTemplate('includes/LeftPanel.inc'); ?>
					</div>
				</td>
				<td class="m0" colspan="2">
					<div id="middle_panel"><?php
						if(isset($PageTopic)) {
							?><h1><?php echo $PageTopic; ?></h1><br /><?php
						}
						if(isset($MenuItems)||isset($MenuBar)) { ?>
							<div class="bar1">
								<div><?php
									if(isset($MenuItems)) {
										$this->includeTemplate('includes/menu.inc');
									}
									else if(isset($MenuBar)) {
										echo $MenuBar;
									} ?>
								</div>
							</div><br /><?php
						}
						else if(isset($SubMenuBar)) {
							echo $SubMenuBar;
						}
						$this->includeTemplate($TemplateBody); ?>
					</div>
				</td>
				<td class="r0">
					<div id="right_panel">
						<?php $this->includeTemplate('includes/RightPanel.inc'); ?>
					</div>
				</td>
			</tr>
			<tr>
				<td class="footer_left">
					<div style="width:294px;" class="center">Get <b><u>FREE TURNS</u></b> for voting if you see the star, next available <span id="v"><?php if($TimeToNextVote <= 0){ ?>now<?php }else{ ?>in <?php echo format_time($TimeToNextVote,true); } ?></span>.</div><?php
						foreach($VoteSites as $VoteSite) {
							echo $VoteSite;
						} ?>
				</td>
				<td class="footer_right">
					<?php $this->includeTemplate('includes/copyright.inc'); ?>
				</td>
			</tr>
		</table>
		<?php $this->includeTemplate('includes/EndingJavascript.inc'); ?>
	</body>
</html>