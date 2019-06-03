<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
		<title>Space Merchant Realms - Ship List</title>
		<meta http-equiv="pragma" content="no-cache">
		<style>
		#container {
			margin: 0;
			padding: 0;
			border: 0;
		}
		#main {
			margin: 0;
			padding: 0;
			border: 0;
		}
		select {
			border: solid #80C870 1px;
			background-color: #0A4E1D;
			color: #80C870;
		}
		optgroup {
			border: solid #80C870 1px;
		}
		</style>
		<script src="js/ship_list.js"></script>
	</head>

	<body>
		<div id="container" style="padding: 0;">
			<div style="width:1400px; margin-left:auto; margin-right:auto;">
				<table id="table" class="center standard">
					<tr >
						<th><a href="?order=ship_name&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Ship Name</span></a></th>
						<th><a href="?order=race_name&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Race</span></a>
							<?php echo $race; ?></th>
						<th>Class<?php echo $class; ?></th>
						<th><a href="?order=cost&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Cost</span></a></th>
						<th><a href="?order=speed&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Speed</span></a>
							<?php echo $speed; ?></th>
						<th><a href="?order=hardpoint&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Hardpoints</span></a>
							<?php echo $hardpoint; ?></th>
						<th><a href="?order=buyer_restriction&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Restriction</span></a>
							<?php echo $restrict; ?></th>
						<th><a href="?hardwarea=1&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Shields</span></a></th>
						<th><a href="?hardwarea=2&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Armour</span></a></th>
						<th><a href="?hardwarea=3&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Cargo</span></a></th>
						<th><a href="?hardwarea=4&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Drones</span></a></th>
						<th><a href="?hardwarea=5&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Scouts</span></a></th>
						<th><a href="?hardwarea=6&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Mines</span></a></th>
						<th><a href="?hardwarea=7&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Scanner</span></a>
							<?php echo $scanner; ?></th>
						<th><a href="?hardwarea=8&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Cloak</span></a>
							<?php echo $cloak; ?></th>
						<th><a href="?hardwarea=9&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Illusion</span></a>
							<?php echo $illusion; ?></th>
						<th><a href="?hardwarea=10&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Jump</span></a>
							<?php echo $jump; ?></th>
						<th><a href="?hardwarea=11&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Scrambler</span></a>
							<?php echo $scramble; ?></th>
					</tr><?php
					foreach ($shipArray as $stat) { ?>
						<tr><?php
							foreach ($stat as $value) {
								$class = '';
								if (is_array($value)) {
									$class = 'class="' . $value[0] . '"';
									$value = $value[1];
								} ?>
								<td <?php echo $class; ?>><?php echo $value; ?></td><?php
							} ?>
						</tr><?php
					} ?>
				</table>
			</div>
		</div>
	</body>
</html>
