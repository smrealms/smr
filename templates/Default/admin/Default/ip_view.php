<script type="text/javascript">
	function go()
	{
		var val = window.document.form_ip.type.value;
		if (val == "search") {
			window.document.form_ip.variable.value = 'Enter IP Here';
		}
		if (val == "account_ips") {
			window.document.form_ip.variable.value = 'Enter Account ID Here';
		}
		if (val == "alliance_ips") {
			window.document.form_ip.variable.value = 'Enter Alliance ID/Game ID (ie 5/12)';
		}
		if (val == "wild_log") {
			window.document.form_ip.variable.value = 'Enter wildcard login here (ie %zool or MrS% or %Nari%)';
		}
		if (val == "wild_in") {
			window.document.form_ip.variable.value = 'Enter wildcard ingame name here (ie %zool or MrS% or %Nari%)';
		}
		if (val == "compare") {
			window.document.form_ip.variable.value = 'Enter ingame names here separated by commas (ie Azool, MrSpock, Jedi Oscar)';
		}
		if (val == "list") {
			window.document.form_ip.variable.value = 'Enter how many IPs per page';
		}
		if (val == "request") {
			window.document.form_ip.variable.value = 'Talk to Azool to add more options for IP searches.';
		}
		if (val == "wild_ip") {
			window.document.form_ip.variable.value = 'Such as 127.2.%.123 or 127.%.5.158';
		}
		if (val == "wild_host") {
			window.document.form_ip.variable.value = 'Such as %.clspco.adelphia.net or co-briar-u1-c4a-44.%.adelphia.net';
		}
		if (val == "compare_log") {
			window.document.form_ip.variable.value = 'Enter login names here separated by commas (ie Azool, MrSpock, Jedi Oscar)';
		}
		if (val == "all_acc") {
			window.document.form_ip.variable.value = 'Enter Starting Account ID, Ending Account ID Currently: <?php echo $MaxAccountID; ?> (ie 5,50)';
		}
		if (val == "comp_share") {
			window.document.form_ip.variable.value = 'Have a Nice Day :) (p.s. click the button)';
		}
	}
</script>
Please select the type of IP search you would like.<br />
<form name="form_ip" method="POST" action="<?php echo $IpFormHref; ?>">
	<select name="type" onchange="go()" id="InputFields">
		<option value="list">List all IPs</option>
		<option value="search">Search for a specific IP</option>
		<option value="account_ips">List all IPs for a specific account (id)</option>
		<option value="alliance_ips">List All IPs for a specific alliance</option>
		<option value="wild_log">List All IPs for a wildcard login name</option>
		<option value="wild_in">List All IPs for a wildcard ingame name</option>
		<option value="compare">List All IPs for specified players</option>
		<option value="compare_log">List All IPs for specified logins</option>
		<option value="wild_ip">Wildcard IP Search</option>
		<option value="wild_host">Wildcard Host Search</option>
		<option value="comp_share">Computer Sharing</option>
		<option value="all_acc">List all Accounts</option>
		<option value="request">Want more? Request it from Azool</option>
	</select><br />
	<input type="text" size="64" value="Enter how many IPs per page" name="variable" id="InputFields"><br />
	<input type="submit" name="action" value="Continue" id="InputFields" />
</form>
<script type="text/javascript">
	window.document.form_ip.variable.select();
	window.document.form_ip.variable.focus();
</script>