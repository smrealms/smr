<?php declare(strict_types=1);

/**
 * @var Smr\Player $ThisPlayer
 */

?>
<form method="POST" action="<?php echo $ThisPlayer->getLeaveNewbieProtectionHREF(); ?>">
	Do you really want to leave Newbie Protection?<br /><br />
	<?php echo create_submit('action', 'Yes!'); ?>&nbsp;&nbsp;<?php echo create_submit('action', 'No!'); ?>
</form>
