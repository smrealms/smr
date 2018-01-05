<?php
try {
	// includes
	require_once('config.inc');
	require_once(LIB . 'Default/smr.inc');
	require_once(get_file_loc('SmrAccount.class.inc'));
	require_once(get_file_loc('SmrSession.class.inc'));

	// do we have a session?
	if (SmrSession::$account_id == 0) {
		header('Location: '.URL.'/login.php');
		exit;
	}

	// get account
	$account =& SmrAccount::getAccount(SmrSession::$account_id);

?>
<!DOCTYPE html>

<html>
	<head>
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
		<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
		<title>Space Merchant Realms</title>
		<meta http-equiv='pragma' content='no-cache'>
	</head>

	<body>

		<table cellspacing='0' cellpadding='0' border='0' width='100%' height='100%'>
			<tr>
				<td></td>
				<td colspan='3' height='1' bgcolor='#0B8D35'></td>
				<td></td>
			</tr>
			<tr>
				<td width='100'>&nbsp;</td>
				<td width='1' bgcolor='#0B8D35'></td>
				<td align='left' valign='top' width='600' bgcolor='#06240E'>
					<table width='100%' height='100%' border='0' cellspacing='5' cellpadding='5'>
					<tr>
						<td valign='top'>

							<h1>Invalid eMail</h1>

							<p>We detected that your eMail (<?php echo $account->getEmail(); ?>) is invalid!<br />
							Please enter a new one.</p>

							<form action='email_processing.php' method='post'>
									<div align='center'>
											<table border='0'>
												<tr>
														<th align='right'>eMail Address:</th>
														<td><input type='text' name='email' size='50' maxlength='128' id='InputFields'></td>
												</tr>
												<tr>
														<th align='right'>Verify eMail Address:</th>
														<td><input type='text' name='email_verify' size='50' maxlength='128' id='InputFields'></td>
												</tr>
											</table>
											<p><input type='submit' value='Change eMail' id='InputFields'></p>
									</div>
							</form>

						</td>
					</tr>
					</table>
				</td>
				<td width='1' bgcolor='#0B8D35'></td>
				<td width='100'>&nbsp;</td>
			</tr>
			<tr>
				<td></td>
				<td colspan='3' height='1' bgcolor='#0b8d35'></td>
				<td></td>
			</tr>
		</table>

	</body>
</html>
<?php
}
catch(Exception $e) {
	handleException($e);
}
?>
