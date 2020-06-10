<?php
if (count($AlliedAllianceBanks) > 0) { ?>
	<ul><?php
	foreach ($AlliedAllianceBanks as $AlliedAllianceID => $AlliedAlliance) { ?>
		<li>
			<a class="bold" href="<?php echo Globals::getAllianceBankHref($AlliedAlliance->getAllianceID()); ?>"><?php echo $AlliedAlliance->getAllianceDisplayName(); ?>'s Account</a>
		</li><?php
	} ?>
	</ul><br /><?php
} ?>

Hello <?php echo $ThisPlayer->getDisplayName(); ?>,<br /><?php
if (isset($UnlimitedWithdrawal) && $UnlimitedWithdrawal === true) {
	?>You can withdraw an unlimited amount from this account.<?php
} elseif (isset($PositiveWithdrawal)) {
	?>You can only withdraw <?php echo number_format($PositiveWithdrawal); ?> more credits based on your deposits.<?php
} else { ?>
	You can withdraw up to <?php echo number_format($WithdrawalPerDay); ?> credits per 24 hours.<br />
	So far you have withdrawn <?php echo number_format($TotalWithdrawn); ?> credits in the past 24 hours. You can withdraw <?php echo number_format($RemainingWithdrawal); ?> more credits.<?php
} ?>
<br /><br /><?php

// only if we have at least one result
if (!empty($BankTransactions)) { ?>
	<div class="center">
		<form class="standard" method="POST" action="<?php echo $FilterTransactionsFormHREF; ?>">
			<table cellspacing="5" cellpadding="0" class="nobord center">
				<tr>
					<td>
						<input class="center" type="number" name="minValue" size="3" value="<?php echo $MinValue; ?>">
					</td>
					<td>-</td>
					<td>
						<input class="center" type="number" name="maxValue" size="3" value="<?php echo $MaxValue; ?>">
					</td>
					<td>
						<input class="submit" type="submit" name="action" value="Show">
					</td>
				</tr>
			</table>
		</form><?php
		if ($CanExempt) {
			?><form class="standard" method="POST" action="<?php echo $ExemptTransactionsFormHREF; ?>"><?php
		} ?>
			<table class="standard inset center">
				<tr>
					<th class="shrink">#</th>
					<th class="shrink">Date</th>
					<th>Trader</th>
					<th>Reason for transfer</th>
					<th class="shrink">Withdrawal</th>
					<th class="shrink">Deposit</th><?php
					if ($CanExempt) {
						?><th class="shrink noWrap">Make Exempt</th><?php
					} ?>
				</tr><?php
				foreach ($BankTransactions as $TransactionID => $BankTransaction) { ?>
					<tr>
						<td><?php echo number_format($TransactionID); ?></td>
						<td class="noWrap"><?php echo date(DATE_FULL_SHORT_SPLIT, $BankTransaction['Time']); ?></td>
						<td class="left"><?php
							if ($BankTransaction['Exempt']) {
								?>Alliance Funds c/o<br /><?php
							}
							echo $BankTransaction['Player']->getLinkedDisplayName(); ?>
						</td>
						<td class="left"><?php echo htmlentities($BankTransaction['Reason']); ?></td>
						<td><?php if (is_numeric($BankTransaction['Withdrawal'])) { echo number_format($BankTransaction['Withdrawal']); } else { ?>&nbsp;<?php } ?></td>
						<td><?php if (is_numeric($BankTransaction['Deposit'])) { echo number_format($BankTransaction['Deposit']); } else { ?>&nbsp;<?php } ?></td><?php
						if ($CanExempt) { ?>
							<td><input type="checkbox" name="exempt[<?php echo $TransactionID; ?>]" value="true"<?php if ($BankTransaction['Exempt']) { ?> checked="checked"<?php } ?>></td><?php
						} ?>
					</tr><?php
				} ?>
				<tr>
					<th colspan="5" class="right">Ending Balance</th>
					<td class="bold right"><?php echo number_format($Alliance->getBank()); ?></td><?php
					if ($CanExempt) {
						?><td><input class="submit" type="submit" name="action" value="Make Exempt"></td><?php
					} ?>
				</tr>
			</table><?php
		if ($CanExempt) {
			?></form><?php
		} ?>
	</div>
	
	<div class="center">
		<div class="buttonA">
			<a class="buttonA" href="<?php echo $BankReportHREF; ?>">View Bank Report</a>
		</div>
	</div><?php
} else {
	?>Your alliance account is still unused.<br /><?php
} ?>

<br />
<h2>Make transaction</h2><br />
<form class="standard" method="POST" action="<?php echo $BankTransactionFormHREF; ?>">
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td class="top">Amount:&nbsp;</td>
			<td><input type="number" name="amount" required size="10">&nbsp;
				Request Exemption:<input type="checkbox" name="requestExempt"></td>
		</tr>
		<tr>
			<td class="top">Reason:&nbsp;</td>
			<td><textarea spellcheck="true" name="message" style="height: 5em"></textarea></td>
		</tr>
	</table>
	<br />
	<input class="submit" type="submit" name="action" value="Deposit">&nbsp;&nbsp;<input class="submit" type="submit" name="action" value="Withdraw">
</form>
