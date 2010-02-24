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
	
	$db = new SmrMySqlDatabase(); ?>
	
	<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<link rel="stylesheet" type="text/css" href="css/default.css">
	<link rel="stylesheet" type="text/css" href="css/Default/Default.css">
	<title>Level Requirements</title>
	<meta http-equiv="pragma" content="no-cache">
	</head>
	
	<body><?php
	$db->query('SELECT * FROM level ORDER BY level_id'); ?>
	<table class="standard">
	
	<tr>
	<th align="center" style="color:#80C870;">Rank Level</th>
	<th align="center" style="color:#80C870;">Rank Name</th>
	<th align="center" style="color:#80C870;">Required Experience</th>
	</tr><?php
	
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