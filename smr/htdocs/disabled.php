<?php

// includes
include('config.inc');
require_once($ENGINE . 'Old_School/smr.inc');
require_once(get_file_loc('SmrAccount.class.inc'));
require_once(get_file_loc('SmrSession.class.inc'));

// get session
$session = new SmrSession();
$db = new SMR_DB();

if (SmrSession::$account_id > 0) {

	$account =& SmrAccount::getAccount(SmrSession::$account_id);
	$db->query('SELECT * FROM account_is_closed WHERE account_id = '.SmrSession::$account_id);
	if ($db->next_record())
		$time = $db->f('expires');
	
	$reason = $account->is_disabled();
	if ($time > 0) $reason .= '  Your account is set to reopen ' . date('n/j/Y g:i:s A', $time) . '.';
	else $reason .= '  Your account is set to never reopen.  If you believe this is wrong contact an admin.';

	SmrSession::destroy();

} else $reason = 'Accessing Account Information Failed.  Contact an admin if you have questions.';

?>

<!doctype html public '-//W3C//DTD HTML 4.0 Transitional//EN'>

<html>

<head>
	<link rel='stylesheet' type='text/css' href='default.css'>
	<meta http-equiv='Content-Type' content='text/html; charset=windows-1252'>
	<meta http-equiv='Content-Language' content='en-us'>
	<title>Space Merchant</title>
</head>

<body>

<?php include('menu.html'); ?>

<table border='0' cellpadding='0' cellspacing='1' width='85%'>
<tr>
	<td align='center'><b style='color:red;font-size:125%;'><br><br></b>
	</td>
</tr>
<tr>
	<td bgcolor='#0B8D35'>
		<table border='0' cellpadding='3' cellspacing='2' width='100%'>
		<tr bgcolor='#0B2121'>
			<td>
				<table border='0' cellpadding='3' cellspacing='2' width='100%'>
				<tr bgcolor='#0B8D35'>
					<td align='center'>
						<p><font face='Times New Roman' size='+2' color='#FFFFFF'>Your account is <strong>DISABLED</strong>.</font></p>
						<p><? $PHP_OUTPUT.=($reason); ?></p>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>

</body>

</html>