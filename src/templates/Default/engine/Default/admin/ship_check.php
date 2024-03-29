<?php declare(strict_types=1);

/**
 * @var array<array{player: string, game_id: int, hardware: string, amount: int, max_amount: int, fixHREF: string}> $ExcessHardware
 */

if (count($ExcessHardware) === 0) { ?>
	<p>No overpowered ships!</p><?php
} else { ?>
	<table class="standard">
		<tr>
			<th>Player</th>
			<th>Game ID</th>
			<th>Type</th>
			<th>Amount</th>
			<th>Max Amount</th>
			<th>Action</th>
		</tr><?php
		foreach ($ExcessHardware as $item) { ?>
			<tr>
				<td><?php echo $item['player']; ?></td>
				<td class="center"><?php echo $item['game_id']; ?></td>
				<td><?php echo $item['hardware']; ?></td>
				<td class="center"><?php echo $item['amount']; ?></td>
				<td class="center"><?php echo $item['max_amount']; ?></td>
				<td class="center">
					<a class="submitStyle" href="<?php echo $item['fixHREF']; ?>">Fix</a>
				</td>
			</tr><?php
		} ?>
	</table><?php
}
