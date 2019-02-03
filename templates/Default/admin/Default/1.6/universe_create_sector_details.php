<form method="POST" action="<?php echo $EditHREF; ?>">
	<table class="shrink">
		<tr>
			<td class="center noWrap">
				Planet Type:
				<select name="plan_type">
					<option value="0">No Planet</option><?php
					foreach (array_keys(SmrPlanetType::PLANET_TYPES) as $type) { ?>
						<option value="<?php echo $type; ?>" <?php echo ($type == $SelectedPlanetType ? 'selected' : ''); ?>><?php echo SmrPlanetType::getTypeInfo($type)->name(); ?></option><?php
					} ?>
				</select>
				<br /><br />

				Port:
				<select name="port_level">
					<option value="0">No Port</option><?php
					for ($i=1; $i<=SmrPort::MAX_LEVEL; $i++) { ?>
						<option value="<?php echo $i; ?>" <?php echo ($i == $SelectedPortLevel ? 'selected' : ''); ?>>Level <?php echo $i; ?></option><?php
					} ?>
				</select>

				<select name="port_race"><?php
					foreach (Globals::getRaces() as $race) { ?>
						<option value="<?php echo $race['Race ID']; ?>" <?php echo ($race['Race ID'] == $SelectedPortRaceID ? 'selected' : ''); ?>><?php echo $race['Race Name']; ?></option><?php
					} ?>
				</select>
				<br /><br />

				<?php
				for ($i=0; $i<UNI_GEN_LOCATION_SLOTS; $i++) { ?>
					Location <?php echo ($i + 1); ?>:
					<select name="loc_type<?php echo $i; ?>">
						<option value="0">No Location</option><?php
						foreach (SmrLocation::getAllLocations() as $id => $location) { ?>
							<option value="<?php echo $id ?>" <?php echo ($id == $SectorLocationIDs[$i] ? 'selected' : ''); ?>><?php echo $location->getName(); ?></option><?php
						} ?>
					</select>
					<br /><?php
				} ?>
			</td>

			<td class="center">
				Warp Sector:<br />
				<input type="number" size="5" class="center" name="warp" value="<?php echo $WarpSectorID; ?>" />
				<br /><?php echo $WarpGal; ?>
			</td>
		</tr>
	</table>
	<br /><br />

	<input type="submit" name="submit" value="Edit Sector">
	<br /><br />
	<a href="<?php echo $CancelHREF; ?>" class="submitStyle">Cancel</a>
</form>
