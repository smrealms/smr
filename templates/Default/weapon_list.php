<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
		<title>Weapon List</title>
		<meta http-equiv="pragma" content="no-cache">
		<style>
		#container {
			margin: 10px;
			padding: 0;
			border: 0;
		}
		select {
			border: solid #80C870 1px;
			background-color: #0A4E1D;
			color: #80C870;
		}
		</style>
		<script src="js/filter_list.js"></script>
	</head>

	<body onload="resetBoxes()">
		<div id="container">
			<form id="raceform" name="raceform" style="text-align:center;"><?php
				foreach (Globals::getRaces() as $raceID => $race) { ?>
					<input type="checkbox" id="race<?php echo $raceID; ?>" name="races" value="<?php echo $race['Race Name']; ?>" onClick="raceToggle()">
					<label for="race<?php echo $raceID; ?>" class="race<?php echo $raceID; ?>"><?php echo $race['Race Name']; ?></label>&thinsp;<?php
				} ?>
			</form>
			<table id="data-list" class="standard center">
				<thead>
					<tr class="top">
						<th style="width: 240px;">
							<span class="sort" data-sort="name">Weapon Name</span><br />
							<input class="search center" placeholder="Search" />
						</th>
						<th style="width: 90px;">
							<span class="sort" data-sort="race">Race</span><br />
							<select onchange="filterSelect(this)">
								<option>All</option><?php
								foreach (Globals::getRaces() as $raceId => $raceData) { ?>
									<option class="race<?php echo $raceId; ?>"><?php echo $raceData['Race Name']; ?></option><?php
								} ?>
							</select>
						</th>
						<th style="width: 68px;">
							<span class="sort" data-sort="cost">Cost</span>
						</th>
						<th style="width: 73px;">
							<span class="sort" data-sort="shield_damage">Shield<br>Damage</span>
						</th>
						<th style="width: 73px;">
							<span class="sort" data-sort="armour_damage">Armour<br>Damage</span>
						</th>
						<th style="width: 83px;">
							<span class="sort" data-sort="accuracy">Accuracy<br>(%)</span>
						</th>
						<th style="width: 51px;">
							<span class="sort" data-sort="level">Level</span><br />
							<select onchange="filterSelect(this)">
								<option>All</option><?php
								foreach ($PowerLevels as $PowerLevel) { ?>
									<option><?php echo $PowerLevel; ?></option><?php
								} ?>
							</select>
						</th>
						<th style="width: 92px;">
							<span class="sort" data-sort="restriction">Restriction</span><br />
							<select onchange="filterSelect(this)">
								<option>All</option>
								<option value="">None</option>
								<option class="dgreen">Good</option>
								<option class="red">Evil</option>
								<option style="color: #06F;">Newbie</option>
							</select>
						</th>
						<th>
							Locations<br />
							<select onchange="filterSelect(this)">
								<option>All</option><?php
								foreach ($AllLocs as $Loc) { ?>
									<option><?php echo $Loc; ?></option><?php
								} ?>
							</select>
						</th>
					</tr>
				</thead>
				<tbody class="list"><?php
					foreach ($Weapons as $weapon) { ?>
						<tr>
							<td class="name"><?php echo $weapon['weapon_name']; ?></td>
							<td class="race race<?php echo $weapon['race_id']; ?>"><?php echo $weapon['race_name']; ?></td>
							<td class="cost"><?php echo $weapon['cost']; ?></td>
							<td class="shield_damage"><?php echo $weapon['shield_damage']; ?></td>
							<td class="armour_damage"><?php echo $weapon['armour_damage']; ?></td>
							<td class="accuracy"><?php echo $weapon['accuracy']; ?></td>
							<td class="level"><?php echo $weapon['power_level']; ?></td>
							<td class="restriction"><?php echo $weapon['restriction']; ?></td>
							<td class="locs"><?php echo join('<br />', $weapon['locs']); ?></td>
						</tr><?php
					} ?>
				</tbody>
			</table>
		</div>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
		<script>
		var list = new List('data-list', {
			valueNames: ['name', 'race', 'cost', 'shield_damage', 'armour_damage', 'accuracy', 'level', 'restriction'],
			sortFunction: function(a, b, options) {
				return list.utils.naturalSort(a.values()[options.valueName].replace(/<.*?>|,/g,''), b.values()[options.valueName].replace(/<.*?>|,/g,''), options);
			}
		});
		</script>
	</body>
</html>
