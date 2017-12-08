<!DOCTYPE html>
<html>
	<head><?php
		$this->assign('FontSize', $FontSize-20);
		$this->includeTemplate('includes/Head.inc'); ?>
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
					<div>Get <b><u>FREE TURNS</u></b> for voting if you see the star, available <span id="v"><?php echo $TimeToNextVote ?></span>.</div><?php
						foreach ($VoteSites as $VoteSite) { ?>
							<a href='<? echo $VoteSite['url']; ?>' target="_blank">
								<img class="vote_site" src="images/game_sites/<?php echo $VoteSite['img']; ?>" alt="" width="98" height="41" />
							</a><?php
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
