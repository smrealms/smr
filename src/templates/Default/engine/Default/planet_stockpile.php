<?php declare(strict_types=1);

/**
 * @var array<array{Name: string, ImageHTML: string, ShipAmount: int, PlanetAmount: int, DefaultAmount: int, HREF: string}> $GoodInfo
 */

if (count($GoodInfo) === 0) { ?>
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
				<td class="left"><?php echo $info['ImageHTML']; ?></td>
				<td><?php echo $info['Name']; ?></td>
				<td class="center"><?php echo $info['ShipAmount']; ?></td>
				<td class="center"><?php echo $info['PlanetAmount']; ?></td>
				<td><input type="number" name="amount" value="<?php echo $info['DefaultAmount']; ?>" class="center" size="4" /></td>
				<td class="center">
					<?php echo create_submit('action', 'Ship'); ?>&thinsp;
					<?php echo create_submit('action', 'Planet'); ?>
				</td>
			</tr>
		</form><?php
	} ?>
</table>
