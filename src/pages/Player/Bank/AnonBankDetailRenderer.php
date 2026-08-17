<?php declare(strict_types=1);

namespace Smr\Pages\Player\Bank;

class AnonBankDetailRenderer {

	/**
	 * @param array<int, array{date: string, payment: string, deposit: string, link: string}> $Transactions
	 */
	public static function render(int $Balance, ?int $MinValue, ?int $MaxValue, ?string $ShowHREF, array $Transactions, AnonBankDetailProcessor $TransactionPage): void {
		if (count($Transactions) > 0) { ?>
			<form method="POST" action="<?php echo $ShowHREF; ?>">
				<table cellspacing="5" cellpadding="0" class="nobord center">
					<tr>
						<td><input type="number" class="center" name="minValue" size="3" value="<?php echo $MinValue; ?>"></td>
						<td>-</td>
						<td><input type="number" class="center" name="maxValue" size="3" value="<?php echo $MaxValue; ?>"></td>
						<td><?php echo create_submit_display('Show'); ?></td>
					</tr>
				</table>
			</form>

			<table class="standard inset center">
				<tr>
					<th>#</th>
					<th>Date</th>
					<th>Trader</th>
					<th>Withdrawal</th>
					<th>&nbsp;&nbsp;Deposit&nbsp;&nbsp;</th>
				</tr><?php
				foreach ($Transactions as $TransactionID => $Transaction) { ?>
					<tr>
						<td class="shrink center"><?php echo $TransactionID; ?></td>
						<td class="shrink center noWrap"><?php echo $Transaction['date']; ?></td>
						<td class="left"><?php echo $Transaction['link']; ?></td>
						<td class="shrink right"><?php echo $Transaction['payment']; ?></td>
						<td class="shrink right"><?php echo $Transaction['deposit']; ?></td>
					</tr><?php
				} ?>
				<tr>
					<th colspan="4" class="right">Ending Balance</th>
					<td class="bold shrink right"><?php echo number_format($Balance); ?></td>
				</tr>
			</table><?php
		} else { ?>
			<br />No transactions have been made on this account.<br /><?php
		} ?>

		<br />
		<h2>Make transaction</h2><br />
		<form method="POST" action="<?php echo $TransactionPage->href(); ?>">
			Amount:&nbsp;<input type="number" name="amount" min="1" required size="10"><br /><br />
			<?php echo $TransactionPage->actionDeposit->html(); ?>
			&nbsp;&nbsp;
			<?php echo $TransactionPage->actionPayment->html('Withdraw'); ?>
		</form>

		<?php
	}

}
