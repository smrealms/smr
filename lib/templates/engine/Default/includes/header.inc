<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?php echo $Title ?> <?php if(isset($GameName)) echo $GameName; ?></title>
		<meta http-equiv="pragma" content="no-cache">
		<?php if(is_object($ThisAccount) && $ThisAccount->isDefaultCSSEnabled()){ ?><link rel="stylesheet" type="text/css" href="<?php echo $CSSLink; ?>"><?php } ?>
		<?php if(isset($ExtraCSSLink)){ ?><link rel="stylesheet" type="text/css" href="<?php echo $ExtraCSSLink; ?>"><?php } ?>
		<!--[if IE]>
			<link rel="stylesheet" type="text/css" href="css/ie_specific.css">
		<![endif]-->
		<!--<script type="text/javascript" src="js/smr.js"></script>-->
		<style type="text/css">
			body {
				font-size:<?php echo $FontSize; ?>%;
			}
		</style>
		<?php
			if(isset($HeaderTemplateInclude))
			{
				$this->includeTemplate($HeaderTemplateInclude);
			}
		?>
	</head>
	<body>