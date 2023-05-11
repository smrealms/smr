<?php declare(strict_types=1);

/**
 * @var array<array{last_active: string, display_name: string, account_id: int}> $Members
 * @var string $BanishHREF
 */

?>
<div class="center"><?php
	if (count($Members) === 0) { ?>
		There is no-one to kick! You are all by yourself!<?php
	} else { ?>
		<form method="POST" action="<?php echo $BanishHREF; ?>">
			<table class="center standard inset">
				<tr>
					<th>Trader Name</th>
					<th>Last Online</th>
					<th>Action</th>
				</tr><?php
				foreach ($Members as $Member) { ?>
					<tr>
						<td class="left"><?php echo $Member['display_name']; ?></td>
						<td class="shrink noWrap"><?php echo $Member['last_active']; ?></td>
						<td class="shrink">
							<input type="checkbox" name="account_id[]" value="<?php echo $Member['account_id']; ?>" />
						</td>
					</tr><?php
				} ?>
			</table>
			<br />
			<input type="submit" name="action" value="Banish 'em!" />
		</form><?php
	} ?>
</div>
