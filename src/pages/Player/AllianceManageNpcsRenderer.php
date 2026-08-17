<?php declare(strict_types=1);

/**
 * @var list<array{name: string, race: string, lastActive: string, dismissHref: string}> $Npcs
 */

if (count($Npcs) > 0) { ?>
	<table class="standard center">
		<tr>
			<th>Trader</th>
			<th>Race</th>
			<th>Last Active</th>
			<th>Action</th>
		</tr><?php
		foreach ($Npcs as $npc) { ?>
			<tr>
				<td><?php echo $npc['name']; ?></td>
				<td><?php echo $npc['race']; ?></td>
				<td><?php echo $npc['lastActive']; ?>
				<td>
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $npc['dismissHref']; ?>">Dismiss</a>
					</div>
				</td>
			</tr><?php
		} ?>
	</table><?php
} else { ?>
	<div>Your alliance does not have any hired NPCs at this time.</div><?php
}
