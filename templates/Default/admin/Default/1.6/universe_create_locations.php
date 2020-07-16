<form method="POST" action="<?php echo $JumpGalaxyHREF; ?>">
	Working on Galaxy:
	<select name="gal_on" onchange="this.form.submit()"><?php
		foreach ($Galaxies as $OtherGalaxy) { ?>
			<option value="<?php echo $OtherGalaxy->getGalaxyID(); ?>"<?php if ($OtherGalaxy->equals($Galaxy)) { ?> selected<?php } ?>><?php
				echo $OtherGalaxy->getName() . ' (' . $OtherGalaxy->getGalaxyID() . ')'; ?>
			</option><?php
		} ?>
	</select>
</form>
<br />

<?php
foreach ($LocTypes as $category => $LocIDs) { ?>
	<!-- There is custom js that disables clicking links multiple times for any
	link that does not have a target. Since this affects anchors as well, we
	explicitly use the default target "_self" so the selector skips it. -->
	<a href="#<?php echo $category; ?>" target="_self">Jump to <?php echo $category; ?></a><br /><?php
} ?>

<br />
Click a category heading to toggle its display.

<style>
	tr.collapsible:hover {
		cursor:pointer;
	}
</style>

<form method="POST" action="<?php echo $CreateLocationsFormHREF; ?>">

	<table class="standard">
		<!-- colgroup style ensures fixed table width as categories are toggled -->
		<colgroup>
			<col style="width:250px">
			<col style="width:158px">
		</colgroup>
		<?php
		foreach ($LocTypes as $category => $LocIDs) { ?>
			<tr class="collapsible" onclick="$('.toggle-<?php echo $category; ?>').toggle();">
				<th id="<?php echo $category; ?>"><?php echo $category; ?></th>
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
