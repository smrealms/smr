Working on Galaxy : <?php echo $Galaxy->getName(); ?> (<?php echo $Galaxy->getGalaxyID(); ?>)
<br /><br />

<?php echo $Form; ?>
<table class="standard">
	<tr>
		<td class="right">Uninhabited Planets</td>
		<td><input type="number" value="<?php echo $NumberOfPlanets; ?>" size="5" name="Uninhab"></td>
	</tr>
	<tr>
		<td class="right">NPC Planets - Won't work</td>
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
