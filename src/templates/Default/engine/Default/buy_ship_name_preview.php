<?php declare(strict_types=1);

use Smr\Globals;

/**
 * @var string $ContinueHREF
 * @var string $ShipName
 */

?>
<div class="center">
	If you ship is found to use HTML inappropriately you may be banned.
	Inappropriate HTML includes but is not limited to something that can
	either cause display errors or cause functionality of the game to stop.
	Also it is your responsibility to make sure ALL HTML tags that need to
	be closed are closed!
	<br /><br />
	Preview:<br /><br />
	<?php echo $ShipName; ?>
	<br /><br />
	Are you sure you want to continue?<br />
	<br />
	<a href="<?php echo $ContinueHREF; ?>" class="submitStyle">Continue </a>
	&nbsp;&nbsp;
	<a href="<?php echo Globals::getBuyShipNameHREF(); ?>" class="submitStyle">Back</a>
</div>
