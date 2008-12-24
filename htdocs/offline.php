<?

include('config.inc');
require_once(LIB . 'global/smr_db.inc');

// new db object
$db = new SMR_DB();

$db->query('SELECT * FROM game_disable');
if ($db->next_record()) {

	$reason = stripslashes($db->f('reason'));

} else header('Location: '.$URL);

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
						<p><font face='Times New Roman' size='+2' color='#FFFFFF'>Space Merchant Realms is currently <strong>OFF-LINE</strong>.</font></p>
						<p><? echo ($reason); ?></p>
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