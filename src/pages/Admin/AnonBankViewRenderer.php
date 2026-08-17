<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

class AnonBankViewRenderer {

/** @param array<array{player_name: string, transaction: string, amount: int}> $Rows */
public static function render(string $BackHREF, array $Rows, int $AnonID, int $ViewGameID): void {
?>
<a href="<?php echo $BackHREF; ?>">&lt;&lt;Back</a>
<p>Transactions from Anonymous Account #<?php echo $AnonID; ?> in Game <?php echo $ViewGameID; ?></p>
<table class="standard">
	<tr>
		<th>Player Name</th>
		<th>Type</th>
		<th>Amount</th>
	</tr><?php
	foreach ($Rows as $Row) { ?>
		<tr>
			<td><?php echo htmlentities($Row['player_name']); ?></td>
			<td><?php echo $Row['transaction']; ?></td>
			<td class="right"><?php echo number_format($Row['amount']); ?></td>
		</tr><?php
	} ?>
</table>

<?php
}

}
