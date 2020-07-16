<div class="center">
	<p>What galaxy do you want maps for?</p>
	<form method="POST" action="<?php echo $BuyHREF; ?>">
		<select type="select" name="gal_id" required>
			<option value="" disabled selected>[Select a galaxy]</option><?php
			$GameGalaxies = SmrGalaxy::getGameGalaxies($ThisPlayer->getGameID());
			foreach ($GameGalaxies as $Galaxy) { ?>
				<option value="<?php echo $Galaxy->getGalaxyID(); ?>"><?php echo $Galaxy->getName(); ?></option><?php
			} ?>
		</select>
		<br /><br />
		<input type="submit" name="action" value="Buy the map" />
	</form>
</div>
