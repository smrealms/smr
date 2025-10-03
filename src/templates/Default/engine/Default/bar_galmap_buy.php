<?php declare(strict_types=1);

/**
 * @var Smr\Player $ThisPlayer
 * @var string $BuyHREF
 */

?>
<div class="center">
	<p>What galaxy do you want maps for?</p>
	<form method="POST" action="<?php echo $BuyHREF; ?>">
		<select type="select" name="gal_id" required>
			<option value="" disabled selected>[Select a galaxy]</option><?php
			foreach ($ThisPlayer->getGame()->getGalaxies() as $Galaxy) { ?>
				<option value="<?php echo $Galaxy->getGalaxyID(); ?>"><?php echo $Galaxy->getDisplayName(); ?></option><?php
			} ?>
		</select>
		<br /><br />
		<?php echo create_submit('action', 'Buy the map'); ?>
	</form>
</div>
