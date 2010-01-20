<?php
try
{
	// ********************************
	// *
	// * I n c l u d e s   h e r e
	// *
	// ********************************
	
	require_once('config.inc');
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
	
	$db = new SmrMySqlDatabase();
	
	echo ('<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">');
	echo ('<html>');
	echo ('<head>');
	echo ('<link rel="stylesheet" type="text/css" href="css/default.css">');
	echo ('<title>Level Requirements</title>');
	echo ('<meta http-equiv="pragma" content="no-cache">');
	echo ('</head>');
	
	echo ('<body>');
	$db->query('SELECT * FROM level ORDER BY level_id');
	echo ('<table class="standard">');
	
	echo ('<tr>');
	echo ('<th align="center" style="color:#80C870;">Rank Level</th>');
	echo ('<th align="center" style="color:#80C870;">Rank Name</th>');
	echo ('<th align="center" style="color:#80C870;">Required Experience</th>');
	echo ('</tr>');
	
	while ($db->nextRecord()) {
	
		$level = $db->getField('level_id');
		$name = $db->getField('level_name');
		$require = $db->getField('requirement');
	
		echo ('<tr>');
		echo ('<td align="center">'.$level.'</td>');
		echo ('<td align="center">'.$name.'</td>');
		echo ('<td align="center">'.$require.'</td>');
		echo ('</tr>');
	
	}
	
	echo ('</table>');

}
catch(Exception $e)
{
	handleException($e);
}
?>