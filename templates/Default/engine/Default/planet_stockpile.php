<?php
if (empty($GoodInfo)) { ?>
	<p>There are no goods present on your ship or the planet!</p><?php
	return;
} ?>

<br />
<table class="standard">
	<tr>
		<th></th>
		<th>Good</th>
		<th>Ship</th>
		<th>Planet</th>
		<th>Amount</th>
		<th>Transfer To</th>
	</tr>

	<?php
	foreach ($GoodInfo as $info) { ?>
		<form method="POST" action="<?php echo $info['HREF']; ?>">
			<tr>
				<td class="left"><img src="<?php echo $info['ImageLink']; ?>" width="13" height="16" title="<?php echo $info['Name']; ?>" alt=""></td>
				<td><?php echo $info['Name']; ?></td>
				<td class="center"><?php echo $info['ShipAmount']; ?></td>
				<td class="center"><?php echo $info['PlanetAmount']; ?></td>
				<td><input type="number" name="amount" value="<?php echo $info['DefaultAmount']; ?>" class="InputFields center" size="4" /></td>
				<td class="center">
					<input type="submit" name="action" value="Ship" class="InputFields" />&thinsp;
					<input type="submit" name="action" value="Planet" class="InputFields" />
				</td>
			</tr>
		</form><?php
	} ?>
</table>
