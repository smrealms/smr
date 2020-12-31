<?php
if (isset($Transactions)) { ?>
	<form method="POST" action="<?php echo $ShowHREF; ?>">
		<table cellspacing="5" cellpadding="0" class="nobord center">
			<tr>
				<td><input type="number" class="center" name="minValue" size="3" value="<?php echo $MinValue; ?>"></td>
				<td>-</td>
				<td><input type="number" class="center" name="maxValue" size="3" value="<?php echo $MaxValue; ?>"></td>
				<td><input type="submit" name="action" value="Show" /></td>
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
<form method="POST" action="<?php echo $TransactionHREF; ?>">
	Amount:&nbsp;<input type="number" name="amount" size="10" value="0"><br /><br />
	<input type="submit" name="action" value="Deposit" />
	&nbsp;&nbsp;
	<button type="submit" name="action" value="Payment">Withdraw</button>
</form>
