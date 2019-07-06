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
		foreach ($Galaxies as $i => $gal) {
			?><tr>
				<td class="center"><?php echo $i; ?></td>
				<td><input required type="text" value="<?php echo $gal['Name']; ?>" name="gal<?php echo $i; ?>"></td>
				<td><input required class="center" type="number" min="1" max="100" value="<?php echo $gal['Width']; ?>" name="width<?php echo $i; ?>"></td>
				<td><input required class="center" type="number" min="1" max="100" value="<?php echo $gal['Height']; ?>" name="height<?php echo $i; ?>"></td>
				<td>
					<select name="type<?php echo $i; ?>" class="InputFields"><?php
					foreach ($GalaxyTypes as $GalaxyType) { ?>
						<option value="<?php echo htmlspecialchars($GalaxyType); ?>" <?php if ($GalaxyType == $gal['Type']) { ?>selected<?php } ?>><?php echo $GalaxyType; ?></option><?php
					} ?>
					</select>
				</td>
				<td class="center"><input required size="3" type="text" value="<?php echo $gal['ForceExpire']; ?>" name="forces<?php echo $i; ?>"></td>
			</tr><?php
		} ?>
		<tr><td class="center" colspan="6"><input type="submit" value="Create Galaxies" name="submit"></td>
	</table>
</form>

<br /><br />
<form method="POST" enctype="multipart/form-data" action="<?php echo $UploadSmrFileHREF; ?>">
	Or generate the universe from a SMR file:<br />
	<input type="file" name="smr_file" />&nbsp;
	<input type="submit" value="Upload SMR File" name="submit" />
</form>
