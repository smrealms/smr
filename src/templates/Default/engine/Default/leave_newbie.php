<?php declare(strict_types=1);

/**
 * @var Smr\Player $ThisPlayer
 */

?>
<form method="POST" action="<?php echo $ThisPlayer->getLeaveNewbieProtectionHREF(); ?>">
	Do you really want to leave Newbie Protection?<br /><br />
	<input type="submit" name="action" value="Yes!" />&nbsp;&nbsp;<input type="submit" name="action" value="No!" />
</form>
