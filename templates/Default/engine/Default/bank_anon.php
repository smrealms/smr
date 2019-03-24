<?php echo $Message; ?>

<h2>Access accounts</h2><br />
<form method="POST" action="<?php echo $AccessHREF; ?>">
<table cellspacing="0" cellpadding="0" class="nobord nohpad">
	<tr>
		<td class="top">Account Number:&nbsp;</td>
		<td><input type="number" name="account_num" size="4"></td>
	</tr>
	<tr>
		<td class="top">Password:&nbsp;</td>
		<td><input type="password" name="password" size="30"></td>
	</tr>
</table>
<br />
<input type="submit" name="action" value="Access Account" />
</form>

<?php
if ($OwnedAnon) { ?>
	<br /><h2>Your accounts</h2><br />
	<table class="standard inset center">
		<tr>
			<th>ID</th>
			<th>Password</th>
			<th>Last Transaction</th>
			<th>Balance</th>
			<th>Option</th>
		</tr><?php
		foreach ($OwnedAnon as $anon) { ?>
			<tr>
				<td class="shrink"><?php echo $anon['anon_id']; ?></td>
				<td class="left"><?php echo $anon['password']; ?></td>
				<td class="shrink noWrap"><?php echo $anon['last_transaction']; ?></td>
				<td class="right shrink"><?php echo $anon['amount']; ?></td>
				<td class="button">
					<div class="buttonA">
						<a class="buttonA" href="<?php echo $anon['href']; ?>">Access Account</a>
					</div>
				</td>
			</tr><?php
		} ?>
	</table>
	<br /><br /><?php
} ?>

<div class="buttonA">
	<a class="buttonA" href="<?php echo $CreateHREF; ?>">Create an account</a>
</div>
