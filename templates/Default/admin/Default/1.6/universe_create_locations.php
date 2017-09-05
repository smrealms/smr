Working on Galaxy : <?php echo $Galaxy->getName(); ?> (<?php echo $Galaxy->getGalaxyID() ?>)<br />

<?php echo $Form; ?>

<table class="standard"><?php
	foreach ($LocTypes as $category => $LocIDs) { ?>
		<tr>
			<th><?php echo $category; ?></th>
			<th>Amount</th>
		</tr><?php
		foreach ($LocIDs as $LocID) { ?>
			<tr>
				<td class="right"><?php echo $LocText[$LocID]; ?></td>
				<td><input type="number" value="<?php echo $TotalLocs[$LocID]; ?>" size="5" name="loc<?php echo $LocID; ?>"></td>
			</tr><?php
		}
	} ?>

	<tr>
		<td colspan="2" class="center"><input type="submit" name="submit" value="Create Locations"><br /><br /><a href="<?php echo $CancelHREF; ?>" class="submitStyle">Cancel</a></td>
	</tr>
</table>

</form>

<span class="small">Note: When you press "Create Locations" this will rearrange all current locations.<br />
To add new locations without rearranging everything use the edit sector feature.</span>
