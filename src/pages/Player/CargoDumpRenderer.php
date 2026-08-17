<?php declare(strict_types=1);

namespace Smr\Pages\Player;

class CargoDumpRenderer {

/**
 * @param array<array{image: string, name: string, amount: int, dump_href: string}> $Goods
 */
public static function render(array $Goods): void {
?>
Enter the amount of cargo you wish to jettison.<br />
Please keep in mind that you will lose experience and <?php echo pluralise(TURNS_TO_DUMP_CARGO, 'turn'); ?>!<br /><br />

<?php
if (count($Goods) === 0) { ?>
	You have no cargo to dump!<?php
} else { ?>
	<table class="standard">
		<tr>
			<th>Good</th>
			<th>Amount to Drop</th>
			<th>Action</th>
		</tr><?php

		foreach ($Goods as $good) { ?>
			<form name="DumpForm" method="POST" action="<?php echo $good['dump_href']; ?>">
				<tr>
					<td><?php echo $good['image']; ?>&nbsp;<?php echo $good['name']; ?></td>
					<td class="center">
						<input type="number" name="amount" value="<?php echo $good['amount']; ?>" maxlength="5" size="5" class="center" />
					</td>
					<td class="center">
						<?php echo create_submit_display('Dump (' . TURNS_TO_DUMP_CARGO . ')'); ?>
					</td>
				</tr>
			</form><?php
		} ?>
	</table><?php
}

}

}
