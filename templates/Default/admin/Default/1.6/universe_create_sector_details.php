<a href="<?php echo $CancelHREF; ?>">&lt;&lt; Back</a><br /><br />
<form method="POST" action="<?php echo $EditHREF; ?>">
	<h2>Planet</h2><br />
	<b>Type: </b>
	<select name="plan_type" class="InputFields">
		<option value="0">No Planet</option><?php
		foreach (array_keys(SmrPlanetType::PLANET_TYPES) as $type) { ?>
			<option value="<?php echo $type; ?>" <?php echo ($type == $SelectedPlanetType ? 'selected' : ''); ?>><?php echo SmrPlanetType::getTypeInfo($type)->name(); ?></option><?php
		} ?>
	</select><br /><?php
	if ($SelectedPlanetType) { ?>
		<b>Habitable: </b><?php echo date(DATE_FULL_SHORT, $Planet->getInhabitableTime()); ?><br /><?php
	} ?>
	<br />

	<h2>Port</h2>
	<select name="port_level" class="InputFields">
		<option value="0">No Port</option><?php
		for ($i = 1; $i <= SmrPort::MAX_LEVEL; $i++) { ?>
			<option value="<?php echo $i; ?>" <?php echo ($i == $SelectedPortLevel ? 'selected' : ''); ?>>Level <?php echo $i; ?></option><?php
		} ?>
	</select>&nbsp;

	<select name="port_race" class="InputFields"><?php
		foreach (Globals::getRaces() as $race) { ?>
		<option value="<?php echo $race['Race ID']; ?>" <?php echo ($race['Race ID'] == $SelectedPortRaceID ? 'selected' : ''); ?>><?php echo $race['Race Name']; ?></option><?php
	} ?>
	</select>
	<br />

  <?php
	if ($SelectedPortLevel) { ?>
		<br /><span class="bold red">WARNING: </span>Ports should only have (Level + 2) number of goods.
		These options will be ignored if you are changing the port level.
		<input type="hidden" name="select_goods" value="1" />
		<table class="nobord">
			<tr><?php
				foreach ([1, 2, 3] as $class) { ?>
					<td class="top">
					<table class="nobord"><?php
						foreach (Globals::getGoods() as $good) {
							if ($good['Class'] == $class) { ?>
								<tr>
								<td>
									<img class="bottom" src="<?php echo $good['ImageLink']; ?>" width="13" height="16">&nbsp;
									<select name="good<?php echo $good['ID']; ?>" class="InputFields">
										<option value="None">--</option><?php
										foreach (['Buy', 'Sell'] as $trans) { ?>
											<option <?php if ($Port->hasGood($good['ID'], $trans)) { ?> selected <?php } ?> value="<?php echo $trans; ?>"><?php echo $trans; ?></option><?php
										} ?>
									</select>
								</td>
								</tr><?php
							}
						} ?>
					</table></td><?php
				} ?>
			</tr>
		</table><?php
	} ?>
	<br />

	<h2>Locations</h2>
	<table class="shrink">
		<tr>
			<td class="center noWrap">
				<?php
				for ($i = 0; $i < UNI_GEN_LOCATION_SLOTS; $i++) { ?>
					<b><?php echo ($i + 1); ?>. </b>
					<select name="loc_type<?php echo $i; ?>" class="InputFields">
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
</form>

<?php
if (isset($Message)) {
	echo $Message;
} ?>
