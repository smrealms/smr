Working on Galaxy : <?php echo $Galaxy->getName(); ?> (<?php echo $Galaxy->getGalaxyID() ?>)<br /><br />

<?php
foreach ($LocTypes as $category => $LocIDs) { ?>
		<a href="javascript:;" onclick="window.location.hash='<?php echo $category; ?>'">Jump to <?php echo $category; ?></a><br /><?php
} ?>

<br />
Click a category heading to toggle its display.

<?php echo $Form; ?>

<table class="standard">
	<!-- colgroup style ensures fixed table width as categories are toggled -->
	<colgroup>
		<col style="width:250px">
		<col style="width:158px">
	</colgroup>
	<?php
	foreach ($LocTypes as $category => $LocIDs) { ?>
		<tr>
			<th id="<?php echo $category; ?>">
				<a href="javascript:;" onclick="$('.toggle-<?php echo $category; ?>').toggle();"><?php echo $category; ?></a>
			</th>
			<th>Amount</th>
		</tr><?php
		foreach ($LocIDs as $LocID) { ?>
			<tr class="toggle-<?php echo $category; ?>">
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
