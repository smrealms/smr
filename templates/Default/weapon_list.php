<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
		<title>Weapon List</title>
		<meta http-equiv="pragma" content="no-cache">
		<style>
		#container {
			margin: 0;
			padding: 0;
			border: 0;
		}
		#main {
			margin: 0 auto 0 auto;
			padding: 0;
			border: 0;
		}
		select {
			border: solid #80C870 1px;
			background-color: #0A4E1D;
			color: #80C870;
		}
		</style>
		<script src="js/weapon_list.js"></script>
	</head>

	<body onload="resetBoxes()">
		<div id="container">
			<div id="main" style="width:810px;">
				<?php echo $raceBoxes; ?>
				<table id="table" class="standard center">
					<tr>
						<th style="width: 240px;">
							<a href="?order=weapon_name&amp;seq=<?php echo $seq; ?>">
								<span>Weapon Name</span>
							</a>
						</th>
						<th style="width: 90px;">
							<a href="?order=race_name&amp;seq=<?php echo $seq; ?>">
								<span style=color:#80C870;>Race</span>
							</a>
						</th>
						<th style="width: 64px;">
							<a href="?order=cost&amp;seq=<?php echo $seq; ?>">
								<span style=color:#80C870;>Cost</span>
							</a>
						</th>
						<th style="width: 74px;">
							<a href="?order=shield_damage&amp;seq=<?php echo $seq; ?>">
								<span style=color:#80C870;>Shield<br>Damage</span>
							</a>
						</th>
						<th style="width: 74px;">
							<a href="?order=armour_damage&amp;seq=<?php echo $seq; ?>">
								<span style=color:#80C870;>Armour<br>Damage</span>
							</a>
						</th>
						<th style="width: 85px;">
							<a href="?order=accuracy&amp;seq=<?php echo $seq; ?>">
								<span style=color:#80C870;>Accuracy<br>%</span>
							</a>
						</th>
						<th style="width: 51px;">
							<a href="?order=power_level&amp;seq=<?php echo $seq; ?>">
								<span style=color:#80C870;>Level</span>
							</a>
							<?php echo $power; ?>
						</th>
						<th style="width: 92px;">
							<a href="?order=buyer_restriction&amp;seq=<?php echo $seq; ?>">
								<span style=color:#80C870;>Restriction</span>
							</a>
							<?php echo $restrict; ?>
						</th>
					</tr><?php
					foreach ($Weapons as $weapon) { ?>
						<tr>
							<td><?php echo $weapon['weapon_name']; ?></td>
							<td class="race<?php echo $weapon['race_id']; ?>"><?php echo $weapon['race_name']; ?></td>
							<td><?php echo $weapon['cost']; ?></td>
							<td><?php echo $weapon['shield_damage']; ?></td>
							<td><?php echo $weapon['armour_damage']; ?></td>
							<td><?php echo $weapon['accuracy']; ?></td>
							<td><?php echo $weapon['power_level']; ?></td>
							<?php echo $weapon['restriction']; ?>
						</tr><?php
					} ?>
				</table>
			</div>
		</div>
	</body>
</html>
