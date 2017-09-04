Working on Galaxy : <?php echo $Galaxy->getName(); ?> (<?php echo $Galaxy->getGalaxyID(); ?>)
<br /><br />

<?php echo $Form; ?>
<table class="standard">
	<tr>
		<th>Planet Type</th>
		<th>Amount</th>
	</tr><?php
	foreach ($AllowedTypes as $ID => $Name) { ?>
		<tr>
			<td class="right"><?php echo $Name; ?></td>
			<td><input type="number" value="<?php echo $NumberOfPlanets[$ID]; ?>" size="5" name="type<?php echo $ID; ?>"></td>
		</tr><?php
	} ?>
	<tr>
		<td class="right">NPC - Won't work</td>
		<td><input type="number" value="<?php echo $NumberOfNpcPlanets; ?>" size="5" name="NPC"></td>
	</tr>
	<tr>
		<td colspan="2" class="center">
			<input type="submit" name="submit" value="Create Planets"><br /><br /><a href="<?php echo $CancelHREF; ?>" class="submitStyle">Cancel</a>
		</td>
	</tr>
</table>
</form>

<span class="small">Note: When you press "Create Planets" this will rearrange all current planets.<br />
To add new planets without rearranging everything use the edit sector feature.</span>
