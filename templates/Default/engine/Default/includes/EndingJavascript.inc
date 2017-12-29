<script type="text/javascript" src="js/jquery.hotkeys.js"></script>
<script type="text/javascript" src="js/ajax.js"></script><?php
if(!empty($js)) {
	?><script type="text/javascript" src="<?php echo $js; ?>"></script><?php
}
foreach($this->jsAlerts as $string) {
	?>alert(<?php echo json_encode($string); ?>);<?php
}

$AvailableLinks = Globals::getAvailableLinks(); ?>
<script type="text/javascript">$(function(){<?php
	if ($AJAX_ENABLE_REFRESH) { ?>
		startRefresh('<?php echo $AJAX_ENABLE_REFRESH; ?>');<?php
	}
	foreach($AvailableLinks as $LinkName => $AvailableLink) {
		$Hotkeys = $ThisAccount->getHotkeys($LinkName);
		foreach($Hotkeys as $Hotkey) {
			?>$(document).bind('keydown', '<?php echo addslashes($Hotkey); ?>', followLink('<?php echo $AvailableLink; ?>'));<?php
		}
	} ?>
})</script>
