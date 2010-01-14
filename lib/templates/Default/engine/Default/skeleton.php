<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?php echo $Title ?> <?php if(isset($GameName)) echo $GameName; ?></title>
		<meta http-equiv="pragma" content="no-cache"><?php
		if(is_object($ThisAccount) && $ThisAccount->isDefaultCSSEnabled())
		{
			?><link rel="stylesheet" type="text/css" href="<?php echo $CSSLink; ?>"><?php
		}
		if(isset($ExtraCSSLink))
		{
			?><link rel="stylesheet" type="text/css" href="<?php echo $ExtraCSSLink; ?>"><?php
		} ?>
		<!--[if IE]>
			<link rel="stylesheet" type="text/css" href="css/ie_specific.css">
		<![endif]-->
		<style type="text/css">
			body {
				font-size:<?php echo $FontSize; ?>%;
			}
		</style><?php
		if(isset($HeaderTemplateInclude))
		{
			$this->includeTemplate($HeaderTemplateInclude);
		} ?>
		<script type="text/javascript" src="js/smr15.js"></script>
	</head>
	<body>



		<table class="m" align="center">
			<tr>
				<td class="l0" rowspan="2">
					<div class="l1">
						<?php $this->includeTemplate('includes/leftPanel.inc'); ?>
					</div>
				</td>
				<td class="m0" colspan="2">
					<div id="middle_panel"><?php
						if(isset($PageTopic))
						{
							?><h1><?php echo $PageTopic; ?></h1><br /><?php
						}
						if(isset($MenuBar))
						{
							echo $MenuBar;
						}
						$this->includeTemplate($TemplateBody); ?>
					</div>
				</td>
				<td class="r0">
					<div id="right_panel">
						<?php $this->includeTemplate('includes/rightPanel.inc'); ?>
					</div>
				</td>
			</tr>
			<tr>
				<td class="footer_left">
					<div style="width:294px;text-align:center">Get <b><u>FREE TURNS</u></b> for voting if you see the star.</div><?php
						foreach($VoteSites as $VoteSite)
						{
							echo $VoteSite;
						} ?>
				</td>
				<td class="footer_right">
					<?php $this->includeTemplate('includes/copyright.inc'); ?>
				</td>
			</tr>
		</table>



		<script type="text/javascript" src="js/ajax.js"></script><?php
		if(!empty($js))
		{
			?><script type="text/javascript" src="<?php echo $js ?>"></script><?php
		} 
		if($AJAX_ENABLE_REFRESH)
		{
			?><script type="text/javascript">window.onload=function(){startRefresh('<?php echo $AJAX_ENABLE_REFRESH; ?>');}</script><?php
		} ?>
	</body>
</html>