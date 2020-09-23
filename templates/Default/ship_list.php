<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
		<title>Space Merchant Realms - Ship List</title>
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

	<body>
		<div id="container">
			<table id="data-list" class="center standard">
				<thead>
					<tr class="top">
						<th style="width: 190px;">
							<span class="sort" data-sort="name">Ship Name</span><br />
							<input class="search center" placeholder="Search" />
						</th>
						<th>
							<span class="sort" data-sort="race">Race</span><br />
							<select onchange="filterSelect(this)">
								<option>All</option><?php
								foreach (Globals::getRaces() as $raceId => $raceData) { ?>
									<option class="race<?php echo $raceId; ?>"><?php echo $raceData['Race Name']; ?></option><?php
								} ?>
							</select>
						</th>
						<th>
							<span class="sort" data-sort="class_">Class</span><br />
							<select onchange="filterSelect(this)">
								<option value="All">All</option><?php
								foreach (Globals::getShipClass() as $shipClass) { ?>
									<option><?php echo $shipClass; ?></option><?php
								} ?>
							</select>
						</th>
						<th style="width: 90px;">
							<span class="sort" data-sort="cost">Cost</span>
						</th>
						<th>
							<span class="sort" data-sort="speed">Speed</span><br />
							<select onchange="filterSelect(this)">
								<option>All</option><?php
								foreach ($Speeds as $Speed) { ?>
									<option><?php echo $Speed; ?></option><?php
								} ?>
							</select>
						</th>
						<th>
							<span class="sort" data-sort="hardpoint">Hardpoints</span><br />
							<select onchange="filterSelect(this)">
								<option>All</option><?php
								foreach ($Hardpoints as $Hardpoint) { ?>
									<option><?php echo $Hardpoint; ?></option><?php
								} ?>
							</select>
						</th>
						<th>
							<span class="sort" data-sort="restriction">Restriction</span><br />
							<select onchange="filterSelect(this)">
								<option>All</option>
								<option value="">None</option>
								<option class="dgreen">Good</option>
								<option class="red">Evil</option>
							</select>
						</th>
						<th><span class="sort" data-sort="shields">Shields</span></th>
						<th><span class="sort" data-sort="armour">Armour</span></th>
						<th><span class="sort" data-sort="cargo">Cargo</span></th>
						<th><span class="sort" data-sort="cds">Drones</span></th>
						<th><span class="sort" data-sort="scouts">Scouts</span></th>
						<th><span class="sort" data-sort="mines">Mines</span></th><?php
						foreach ($BooleanFields as $Field) { ?>
							<th>
								<span class="sort" data-sort="<?php echo strtolower($Field); ?>"><?php echo $Field; ?></span><br />
								<select onchange="filterSelect(this)">
									<option>All</option>
									<option>Yes</option>
									<option value="">No</option>
								</select>
							</th><?php
						} ?>
					</tr>
				</thead>
				<tbody class="list"><?php
					foreach ($shipArray as $stats) { ?>
						<tr><?php
							foreach ($stats as $class => $value) { ?>
								<td class="<?php echo $class; ?>"><?php echo $value; ?></td><?php
							} ?>
						</tr><?php
					} ?>
				</tbody>
			</table>
		</div>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
		<script>
		var list = new List('data-list', {
			valueNames: ['name', 'race', 'class_', 'cost', 'speed', 'hardpoint', 'restriction', 'shields', 'armour', 'cargo', 'cds', 'scouts', 'mines', 'scanner', 'cloak', 'illusion', 'jump', 'scrambler'],
			sortFunction: function(a, b, options) {
				return list.utils.naturalSort(a.values()[options.valueName].replace(/<.*?>|,/g,''), b.values()[options.valueName].replace(/<.*?>|,/g,''), options);
			}
		});
		</script>
	</body>
</html>
