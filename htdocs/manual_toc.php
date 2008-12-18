<?

include('config.inc');
require_once($LIB . 'global/smr_db.inc');
include(ENGINE . 'Old_School/smr.inc');
include(ENGINE . 'Old_School/help.inc');

$PHP_OUTPUT.=('<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">');

$PHP_OUTPUT.=('<html>');
$PHP_OUTPUT.=('<head>');
$PHP_OUTPUT.=('<link rel="stylesheet" type="text/css" href="'.$URL.'/default.css">');
$PHP_OUTPUT.=('<title>Space Merchant Realms - Manual</title>');
$PHP_OUTPUT.=('<meta http-equiv="pragma" content="no-cache">');
$PHP_OUTPUT.=('</head>');

$PHP_OUTPUT.=('<body>');

$PHP_OUTPUT.=('<table width="100%" border="0">');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=('<table>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th width="32">');
$PHP_OUTPUT.=('<img src="'.$URL.'/images/help/empty.jpg" width="32" height="32">');
$PHP_OUTPUT.=('</th>');
$PHP_OUTPUT.=('<th width="32">');
$PHP_OUTPUT.=('<img src="'.$URL.'/images/help/empty.jpg" width="32" height="32">');
$PHP_OUTPUT.=('</th>');
$PHP_OUTPUT.=('<th width="32">');
$PHP_OUTPUT.=('<img src="'.$URL.'/images/help/empty.jpg" width="32" height="32">');
$PHP_OUTPUT.=('</th>');
$PHP_OUTPUT.=('<th width="100%" align="center" validn="middle" style="font-size:18pt;font-weight:bold;">Table of Content</th>');
$PHP_OUTPUT.=('<th width="32"><a href="'.$URL.'/manual_toc.php"><img src="'.$URL.'/images/help/contents.jpg" width="32" height="32" border="0"></a></th>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>');

echo_menu(0);

$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=('<table>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th width="32">');
$PHP_OUTPUT.=('<img src="'.$URL.'/images/help/empty.jpg" width="32" height="32">');
$PHP_OUTPUT.=('</th>');
$PHP_OUTPUT.=('<th width="32">');
$PHP_OUTPUT.=('<img src="'.$URL.'/images/help/empty.jpg" width="32" height="32">');
$PHP_OUTPUT.=('</th>');
$PHP_OUTPUT.=('<th width="32">');
$PHP_OUTPUT.=('<img src="'.$URL.'/images/help/empty.jpg" width="32" height="32">');
$PHP_OUTPUT.=('</th>');
$PHP_OUTPUT.=('<th width="100%" align="center" validn="middle" style="font-size:18pt;font-weight:bold;">Table of Content</th>');
$PHP_OUTPUT.=('<th width="32"><a href="'.$URL.'/manual_toc.php"><img src="'.$URL.'/images/help/contents.jpg" width="32" height="32" border="0"></a></th>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('</table>');

$PHP_OUTPUT.=('</body>');
$PHP_OUTPUT.=('</html>');

?>