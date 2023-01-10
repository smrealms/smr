<?php declare(strict_types=1);

?>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<title><?php echo PAGE_TITLE; ?><?php if (isset($GameName)) echo ": $GameName"; ?></title>
<meta http-equiv="pragma" content="no-cache" /><?php
if ($ThisAccount->isDefaultCSSEnabled()) { ?>
	<link rel="stylesheet" type="text/css" href="<?php echo $CSSLink; ?>" />
	<link rel="stylesheet" type="text/css" href="<?php echo $CSSColourLink; ?>" /><?php
}
if (isset($ExtraCSSLink)) {
	?><link rel="stylesheet" type="text/css" href="<?php echo $ExtraCSSLink; ?>" /><?php
} ?>
<style>
	body {
		font-size:<?php echo $FontSize; ?>%;
	}

	.enemy, .enemy:hover {
		color: #<?php echo $ThisAccount->getEnemyColour(); ?>;
	}
	.enemyBack, .enemyBack:hover {
		background-color: #<?php echo $ThisAccount->getEnemyColour(); ?>;
	}

	.friendly, .friendly:hover {
		color: #<?php echo $ThisAccount->getFriendlyColour(); ?>;
	}
	.friendlyBack, .friendlyBack:hover {
		background-color: #<?php echo $ThisAccount->getFriendlyColour(); ?>;
	}

	.neutral, .neutral:hover {
		color: #<?php echo $ThisAccount->getNeutralColour(); ?>;
	}
	.neutralBack, .neutralBack:hover {
		background-color: #<?php echo $ThisAccount->getNeutralColour(); ?>;
	}
</style>
<link rel="stylesheet" href="/css/colorpicker.css" />
<script src="<?php echo JQUERY_URL; ?>"></script>
<script src="<?php echo JQUERYUI_URL; ?>"></script>
<script src="/js/smr15.js"></script>
