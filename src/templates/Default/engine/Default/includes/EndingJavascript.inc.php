<?php declare(strict_types=1);

use Smr\Globals;

/**
 * @var Smr\Account $ThisAccount
 * @var Smr\Template $this
 * @var int|false $AJAX_ENABLE_REFRESH
 */

?>
<script src="/js/jquery.hotkeys.js"></script>
<script src="/js/ajax.js"></script>

<?php
foreach ($this->jsSources as $src) { ?>
	<script src="<?php echo $src; ?>"></script><?php
}

foreach ($this->jsAlerts as $string) {
	?>alert(<?php echo json_encode($string, JSON_THROW_ON_ERROR); ?>);<?php
}

if ($this->listjsInclude !== null) { ?>
	<script src="<?php echo LISTJS_URL; ?>"></script>
	<script src="/js/listjs_include.js"></script>
	<script>
		listjs.<?php echo $this->listjsInclude; ?>();
	</script><?php
}

if (isset($AddRaceRadarChartJS) && isset($SelectedRaceID)) { ?>
	<script src="https://cdn.plot.ly/plotly-1.58.2.min.js"></script>
	<script>
		createRaceRadarChart(<?php echo $SelectedRaceID?>);
	</script><?php
}

$AvailableLinks = Globals::getAvailableLinks(); ?>
<script>$(function(){<?php
	if ($AJAX_ENABLE_REFRESH !== false) { ?>
		initRefresh('<?php echo $AJAX_ENABLE_REFRESH; ?>');<?php
	}
	foreach ($AvailableLinks as $LinkName => $AvailableLink) {
		$Hotkeys = $ThisAccount->getHotkeys($LinkName);
		foreach ($Hotkeys as $Hotkey) {
			?>$(document).bind('keydown', '<?php echo addslashes($Hotkey); ?>', followLink('<?php echo $AvailableLink; ?>'));<?php
		}
	} ?>
})</script>
