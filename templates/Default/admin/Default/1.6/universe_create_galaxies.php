<form method="POST" action="<?php echo $UpdateNumGalsHREF; ?>">
	Number of Galaxies:
	<input class="center" type="number" min="1" max="30" name="num_gals" value="<?php echo $NumGals; ?>" />
	<input type="submit" name="submit" value="Update" />
</form>
<br />
<form method="POST" action="<?php echo $CreateGalaxiesHREF; ?>">
	<table class="standard">
		<tr>
			<th>Galaxy ID</th>
			<th>Name</th>
			<th>Width</th>
			<th>Height</th>
			<th>Type</th>
			<th>Max Hours Before<br />
				Forces Expire<br />
				(Decimals Allowed)</th>
			</tr><?php
		for ($i=1;$i<=$NumGals;++$i) {
			?><tr>
				<td class="center"><?php echo $i; ?></td>
				<td><input type="text" value="<?php if (isset($DefaultNames[$i])) { echo htmlspecialchars($DefaultNames[$i]); } ?>" name="gal<?php echo $i; ?>"></td>
				<td><input class="center" type="number" min="1" max="100" value="15" name="width<?php echo $i; ?>"></td>
				<td><input class="center" type="number" min="1" max="100" value="15" name="height<?php echo $i; ?>"></td>
				<td>
					<select name="type<?php echo $i; ?>" id="InputFields"><?php
					foreach($GalaxyTypes as $GalaxyType) {
						?><option value="<?php echo htmlspecialchars($GalaxyType); ?>"><?php echo $GalaxyType; ?></option><?php
					} ?>
					</select>
				</td>
				<td class="center"><input size="3" type="text" value="120" name="forces<?php echo $i; ?>"></td>
			</tr><?php
		} ?>
		<tr><td class="center" colspan="6"><input type="submit" value="Create Galaxies" name="submit"></td>
	</table>
</form>
