<?php declare(strict_types=1);

?>
<!DOCTYPE html>
<html>
	<head><?php
		$this->includeTemplate('includes/Head.inc.php'); ?>
	</head>
	<body>
		<table class="m centered">
			<tr>
				<td class="l0" rowspan="2">
					<div class="l1">
						<?php $this->includeTemplate('includes/LeftPanel.inc.php'); ?>
					</div>
				</td>
				<td class="m0" colspan="2">
					<div id="middle_panel"><?php
						if (isset($PageTopic)) {
							?><h1><?php echo $PageTopic; ?></h1><br /><?php
						}
						$this->includeTemplate('includes/menu.inc.php');
						$this->includeTemplate($TemplateBody); ?>
					</div>
				</td>
				<td class="r0">
					<div id="right_panel">
						<?php $this->includeTemplate('includes/RightPanelPlayer.inc.php'); ?>
						<br />
						<?php $this->includeTemplate('includes/RightPanelShip.inc.php'); ?>
					</div>
				</td>
			</tr>
			<tr>
				<td class="footer_left">
					<?php $this->includeTemplate('includes/VoteLinks.inc.php'); ?>
				</td>
				<td class="footer_right">
					<?php $this->includeTemplate('includes/copyright.inc.php'); ?>
				</td>
				<td></td>
			</tr>
		</table>
		<?php $this->includeTemplate('includes/EndingJavascript.inc.php'); ?>
	</body>
</html>
