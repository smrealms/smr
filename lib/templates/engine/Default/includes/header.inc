<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title><?php echo $Title.' '.$GameName; ?></title>
		<meta http-equiv="pragma" content="no-cache">
		<link rel="stylesheet" type="text/css" href="<?php echo $CSSLink; ?>">
		<!--[if IE]>
			<link rel="stylesheet" type="text/css" href="css/ie_specific.css">
		<![endif]-->
		<!--<script type="text/javascript" src="js/smr.js"></script>-->
		<style type="text/css">
			body {
				font-size:<?php echo $fontSize; ?>%;
			}
		</style>
		<?php
			if(isset($HeaderTemplateInclude))
			{
				$this->includeTemplate($HeaderTemplateInclude);
			}
		?>
	</head>
	<body <?php echo $bodyTag; ?>>