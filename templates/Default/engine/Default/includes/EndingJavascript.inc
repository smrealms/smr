<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.hotkeys.js"></script>
<script type="text/javascript" src="js/ajax.js"></script><?php
if(!empty($js))
{
	?><script type="text/javascript" src="<?php echo $js; ?>"></script><?php
}
if($AJAX_ENABLE_REFRESH)
{
	$AvailableLinks = Globals::getAvailableLinks();
	?><script type="text/javascript">$(function(){
		startRefresh('<?php echo $AJAX_ENABLE_REFRESH; ?>');<?php
		if(isset($AvailableLinks['MoveUp'])) {
			?>$(document).bind('keydown', 'w', followLink('<?php echo $AvailableLinks['MoveUp']; ?>'));<?php
		}
		if(isset($AvailableLinks['MoveLeft'])) {
			?>$(document).bind('keydown', 'a', followLink('<?php echo $AvailableLinks['MoveLeft']; ?>'));<?php
		}
		if(isset($AvailableLinks['MoveDown'])) {
			?>$(document).bind('keydown', 's', followLink('<?php echo $AvailableLinks['MoveDown']; ?>'));<?php
		}
		if(isset($AvailableLinks['MoveRight'])) {
			?>$(document).bind('keydown', 'd', followLink('<?php echo $AvailableLinks['MoveRight']; ?>'));<?php
		}
		if(isset($AvailableLinks['MoveWarp'])) {
			?>$(document).bind('keydown', 'e', followLink('<?php echo $AvailableLinks['MoveWarp']; ?>'));<?php
		} ?>
	})</script><?php
} ?>