<?php
try
{
	require_once('config.inc');
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
	require_once(ENGINE . 'Default/smr.inc');
	require_once(ENGINE . 'Default/help.inc');
	
	$topic_id = $_SERVER['QUERY_STRING'];
	if (empty($topic_id))
		$topic_id = 1;
	
	echo ('<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">');
	
	echo ('<html>');
	echo ('<head>');
	echo ('<link rel="stylesheet" type="text/css" href="'.URL.'/css/default.css">');
	echo ('<title>Space Merchant Realms - Manual</title>');
	echo ('<meta http-equiv="pragma" content="no-cache">');
	echo ('</head>');
	
	echo ('<body>');
	
	echo ('<table width="100%" border="0">');
	
	echo ('<tr>');
	echo ('<td>');
	echo_nav($topic_id);
	echo ('</td>');
	echo ('</tr>');
	
	echo ('<tr>');
	echo ('<td>');
	echo_content($topic_id);
	echo ('</td>');
	echo ('</tr>');
	
	echo ('<tr>');
	echo ('<td>');
	echo_subsection($topic_id);
	echo ('</td>');
	echo ('</tr>');
	
	echo ('<tr>');
	echo ('<td>');
	echo_nav($topic_id);
	echo ('</td>');
	echo ('</tr>');
	
	echo ('</table>');
	
	echo ('</body>');
	echo ('</html>');

}
catch(Exception $e)
{
	handleException($e);
}
?>