<form method="POST" action="<?php echo $Submit['href']; ?>">
	<table class="standard">
		<tr>
			<th>Galaxy ID</th>
			<th>Name</th>
			<th>Width</th>
			<th>Height</th>
			<th>Type</th>
			<th>
				Max Hours Before<br />
				Forces Expire<br />
				(Decimals Allowed)
			</th>
		</tr><?php
		foreach ($Galaxies as $i => $gal) { ?>
			<tr>
				<td class="center"><?php echo $i; ?></td>
				<td><input required type="text" value="<?php echo $gal['Name']; ?>" name="gal<?php echo $i; ?>"></td>
				<td><input required <?php if ($GameEnabled) { ?>disabled<?php } ?> class="center" type="number" min="1" max="100" value="<?php echo $gal['Width']; ?>" name="width<?php echo $i; ?>"></td>
				<td><input required <?php if ($GameEnabled) { ?>disabled<?php } ?> class="center" type="number" min="1" max="100" value="<?php echo $gal['Height']; ?>" name="height<?php echo $i; ?>"></td>
				<td>
					<select name="type<?php echo $i; ?>"><?php
					foreach (SmrGalaxy::TYPES as $GalaxyType) { ?>
						<option value="<?php echo htmlspecialchars($GalaxyType); ?>" <?php if ($GalaxyType == $gal['Type']) { ?>selected<?php } ?>><?php echo $GalaxyType; ?></option><?php
					} ?>
					</select>
				</td>
				<td class="center"><input required size="3" type="text" value="<?php echo $gal['ForceMaxHours']; ?>" name="forces<?php echo $i; ?>"></td>
			</tr><?php
		} ?>
		<tr><td class="center" colspan="6"><input type="submit" value="<?php echo $Submit['value']; ?>" name="submit"></td>
	</table>
</form>
