<?php
if (count($AlliedAllianceBanks) > 0)
{ ?>
	<ul><?php
	foreach ($AlliedAllianceBanks as $AlliedAllianceID => $AlliedAlliance)
	{ ?>
		<li>
			<a class="bold" href="<?php echo Globals::getAllianceBankHref($AlliedAlliance->getAllianceID()); ?>"><?php echo $AlliedAlliance->getAllianceName(); ?>'s Account'</a>
		</li><?php
	} ?>
	</ul><br /><?php
} ?>

Hello <?php echo $ThisPlayer->getPlayerName(); ?>,<br /><?php
if ($UnlimitedWithdrawal === true)
{
	?>You can withdraw an unlimited amount from this account. <br /><?php
}
else if (isset($PositiveWithdrawal))
{
	?>You can only withdraw <?php echo number_format($PositiveWithdrawal); ?> more credits based on your deposits.<br /><?php
}
else
{ ?>
	You can withdraw up to <?php echo number_format($WithdrawalPerDay); ?> credits per 24 hours.<br />
	So far you have withdrawn <?php echo number_format($TotalWithdrawn); ?> credits in the past 24 hours. You can withdraw <?php echo number_format($RemainingWithdrawal); ?> more credits.<br /><?php
}

// only if we have at least one result
if (count($BankTransactions) > 0)
{ ?>
	<div align="center">
		<form class="standard" method="POST" action="<?php echo $FilterTransactionsFormHREF; ?>">
			<table cellspacing="5" cellpadding="0" class="nobord">
				<tr>
					<td>
						<input class="center" type="text" name="minValue" size="3" value="<?php echo $MinValue; ?>">
					</td>
					<td>-</td>
					<td>
						<input class="center" type="text" name="maxValue" size="3" value="<?php echo $MaxValue; ?>">
					</td>
					<td>
						<input class="submit" type="submit" name="action" value="Show">
					</td>
				</tr>
			</table>
		</form><?php
		if($CanExempt)
		{
			?><form class="standard" method="POST" action="<?php echo $ExemptTransactionsFormHREF; ?>"><?php
		} ?>
			<table class="standard inset">
				<tr>
					<th>#</th>
					<th>Date</th>
					<th>Trader</th>
					<th>Reason for transfer</th>
					<th>Withdrawal</th>
					<th>&nbsp;&nbsp;Deposit&nbsp;&nbsp;</th><?php
					if($CanExempt)
					{
						?><th>Make Exempt</th><?php
					} ?>
				</tr><?php
				foreach($BankTransactions as $TransactionID => &$BankTransaction)
				{ ?>
					<tr>
						<td class="center shrink"><?php echo $TransactionID; ?></td>
						<td class="center shrink noWrap"><?php echo date(DATE_FULL_SHORT_SPLIT, $BankTransaction['Time']); ?></td>
						<td><?php
							if($BankTransaction['Exempt'])
							{
								?>Alliance Funds c/o<br /><?php
							}
							echo $BankTransaction['Player']->getLinkedDisplayName(); ?>
						</td>
						<td><?php echo $BankTransaction['Reason']; ?></td>
						<td class="shrink right"><?php echo $BankTransaction['TransactionID']; ?></td>
						<td class="center shrink"><?php echo $BankTransaction['Withdrawal']; ?></td>
						<td class="center shrink"><?php echo $BankTransaction['Deposit']; ?></td><?php
						if ($CanExempt)
						{ ?>
							<td class="center"><input type="checkbox" name="exempt[<?php echo $TransactionID; ?>]" value="true"<?php if($BankTransaction['Exempt']){ ?> checked="checked"<?php } ?>></td><?php
						} ?>
					</tr><?php
				} ?>
				<tr>
					<th colspan="5" class="right">Ending Balance</th>
					<td class="bold shrink right"><?php echo number_format($Alliance->getAccount()); ?></td><?php
					if($CanExempt)
					{
						?><td><input class="submit" type="submit" name="action" value="Make Exempt"></td><?php
					} ?>
				</tr>
			</table><?php
		if($CanExempt)
		{
			?></form><?php
		} ?>
	</div><?php
}
else
{
	?>Your alliance account is still unused<br /><?php
} ?>
<div align="center">
	<div class="buttonA">
		<a class="buttonA" href="<?php echo $BankReportHREF; ?>">&nbsp;View Bank Report&nbsp;</a>
	</div>
</div>

<h2>Make transaction</h2><br />
<form class="standard" method="POST" action="<?php echo $BankTransactionFormHREF; ?>">
	<table cellspacing="0" cellpadding="0" class="nobord nohpad">
		<tr>
			<td class="top">Amount:&nbsp;</td>
			<td><input type="text" name="amount" size="10">&nbsp;
				Request Exemption:<input type="checkbox" name="requestExempt"></td>
		</tr>
		<tr>
			<td class="top">Reason:&nbsp;</td>
			<td><textarea name="message"></textarea></td>
		</tr>
	</table>
	<br />
	<input class="submit" type="submit" name="action" value="Deposit">&nbsp;&nbsp;<input class="submit" type="submit" name="action" value="Withdraw">
</form>