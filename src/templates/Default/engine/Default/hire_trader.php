<?php declare(strict_types=1);

/**
 * @var list<array{player: Smr\Player, hireCost: int, hireHref: string}> $Npcs
 * @var ?string $DisableReason
 * @var Smr\Player $ThisPlayer
 */

if ($DisableReason !== null) { ?>
	<div><?php echo $DisableReason; ?></div><?php
}

if (count($Npcs) > 0) { ?>
	<table class="standard center">
		<tr>
			<th>Trader</th>
			<th>Race</th>
			<th>Cost</th>
			<th></th>
		</tr><?php
		foreach ($Npcs as $npc) { ?>
			<tr>
				<td><?php echo $npc['player']->getLinkedDisplayName(true); ?></td>
				<td><?php echo $npc['player']->getColouredRaceName($ThisPlayer->getRaceID()); ?></td>
				<td><?php echo number_format($npc['hireCost']); ?></td>
				<td>
					<?php if ($DisableReason !== null) { ?>
						<div class="buttonA">
							<a class="buttonA" href="<?php echo $npc['hireHref']; ?>">Hire</a>
						</div><?php
					} ?>
				</td>
			</tr><?php
		} ?>
	</table><?php
}
