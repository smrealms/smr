<h2>Create New Multi Account</h2>
<p>All players are allowed a second ("multi") account in addition to their
primary ("main") account. It is created only through this interface, so
<b>DO NOT</b> create a second login!</p>

<p>There are very specific rules about how you can use you multi account.
<a href='https://wiki.smrealms.de/rules' target="_blank" style='font-weight:bold;'>Click HERE</a> for more information.</p>

<p>If you have an existing multi account, please link it below instead of
creating a new one.</p>

<div class="buttonA">
	<a class="buttonA" href="<?php echo $CreateHREF; ?>">Create Multi</a>
</div>
<br /><br /><br />

<h2>Link Existing Multi Account</h2>

<p>If you are logged in on an existing multi account, <b>DO NOT CONTINUE</b>.
Instead, please log into your main account and link your multi account from
there.</p>

<p><span class="bold red">WARNING</span>: When you do this, your multi login
will be deleted. This action cannot be undone! However, your multi account
data will then be associated as the multi of the account you are currently
logged in as.</p>

<p>If you are at all unsure how to procede, please contact an admin for
assistance.</p>

<form id="LinkOldAccountForm" method="POST" action="<?php echo $LinkHREF; ?>">
	<table>
		<tr>
			<th colspan="2">Enter Existing Account Credentials</th>
		</tr>
		<tr>
			<td>Login:</td>
			<td><input type="text" name="multi_account_login"/></td>
		</tr>
		<tr>
			<td>Password:</td>
			<td><input type="password" name="multi_account_password"/></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="submit" name="action" value="Link Account" id="InputFields" /></td>
		</tr>
	</table>
</form>
