<?php
try
{
	require_once('config.inc');
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');
	require_once(ENGINE . 'Default/smr.inc');
	require_once(ENGINE . 'Default/help.inc');
	
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
	echo ('<table>');
	echo ('<tr>');
	echo ('<th width="32">');
	echo ('<img src="'.URL.'/images/help/empty.jpg" width="32" height="32">');
	echo ('</th>');
	echo ('<th width="32">');
	echo ('<img src="'.URL.'/images/help/empty.jpg" width="32" height="32">');
	echo ('</th>');
	echo ('<th width="32">');
	echo ('<img src="'.URL.'/images/help/empty.jpg" width="32" height="32">');
	echo ('</th>');
	echo ('<th width="100%" align="center" validn="middle" style="font-size:18pt;font-weight:bold;">Table of Content</th>');
	echo ('<th width="32"><a href="'.URL.'/manual_toc.php"><img src="'.URL.'/images/help/contents.jpg" width="32" height="32" border="0"></a></th>');
	echo ('</tr>');
	echo ('</table>');
	echo ('</td>');
	echo ('</tr>');
	
	echo ('<tr>');
	echo ('<td>');
	
	echo_menu(0);
	
	echo ('</td>');
	echo ('</tr>');
	
	echo ('<tr>');
	echo ('<td>');
	echo ('<table>');
	echo ('<tr>');
	echo ('<th width="32">');
	echo ('<img src="'.URL.'/images/help/empty.jpg" width="32" height="32">');
	echo ('</th>');
	echo ('<th width="32">');
	echo ('<img src="'.URL.'/images/help/empty.jpg" width="32" height="32">');
	echo ('</th>');
	echo ('<th width="32">');
	echo ('<img src="'.URL.'/images/help/empty.jpg" width="32" height="32">');
	echo ('</th>');
	echo ('<th width="100%" align="center" validn="middle" style="font-size:18pt;font-weight:bold;">Table of Content</th>');
	echo ('<th width="32"><a href="'.URL.'/manual_toc.php"><img src="'.URL.'/images/help/contents.jpg" width="32" height="32" border="0"></a></th>');
	echo ('</tr>');
	echo ('</table>');
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