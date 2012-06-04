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
			</tr>
		<?
		for ($i=1;$i<=$NumGals;$i++)
		{
			?><tr>
				<td class="center"><? echo $i; ?></td>
				<td class="center"><input type="text" value="<?php echo $DefaultNames[$i] ?>" name="gal<?php echo $i; ?>"></td>
				<td class="center"><input size="3" type="text" value="15" name="width<?php echo $i; ?>"></td>
				<td class="center"><input size="3" type="text" value="15" name="height<?php echo $i; ?>"></td>
				<td class="center">
					<select name="type<?php echo $i; ?>" id="InputFields"><?php
					foreach($GalaxyTypes as $GalaxyType)
					{
						?><option value="<?php echo $GalaxyType; ?>"><?php echo $GalaxyType; ?></option><?php
					} ?>
					</select>
				</td>
				<td class="center"><input size="3" type="text" value="5" name="forces<?php echo $i; ?>"></td>
			</tr><?php
		} ?>
		<tr><td class="center" colspan="5"><input type="submit" value="Save Galaxies" name="submit"></td>
		<td class="center"><input type="submit" value="Next" name="Next"></td>
	</table>
</form>