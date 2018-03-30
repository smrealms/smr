Working on Galaxy : <?php echo $Galaxy->getName(); ?> (<?php echo $Galaxy->getGalaxyID(); ?>)<br />

<form method="POST" action="<?php echo $SubmitHREF; ?>">
	<table class="standard"><?php
		foreach ($Galaxies as $eachGalaxy) { ?>
			<tr>
				<td class="right"><?php echo $eachGalaxy->getName(); ?></td>
				<td><input type="number" value="<?php echo isset($Warps[$eachGalaxy->getGalaxyID()]) ? $Warps[$eachGalaxy->getGalaxyID()] : '0'; ?>" size="5" name="warp<?php echo $eachGalaxy->getGalaxyID(); ?>"></td>
			</tr><?php
		} ?>
		<tr>
			<td colspan="2" class="center">
				<input type="submit" name="submit" value="Create Warps">
				<br /><br />
				<a href="<?php echo $CancelHREF; ?>" class="submitStyle">Cancel</a>
			</td>
		</tr>
	</table>
</form>

<br />
<span class="bold">Note:</span> When you press "Create Warps" this will rearrange all current warps.<br />
To add new warps without rearranging everything use the edit sector feature.<br />
Keep in mind this removes both sides of the warp, so 2 gals are changed for each warp.
