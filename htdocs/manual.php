<?

include('config.inc');
require_once($LIB . 'global/smr_db.inc');
include(ENGINE . 'Old_School/smr.inc');
include(ENGINE . 'Old_School/help.inc');

$topic_id = $_SERVER['QUERY_STRING'];
if (empty($topic_id))
	$topic_id = 1;

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
echo_nav($topic_id);
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>');
echo_content($topic_id);
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>');
echo_subsection($topic_id);
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>');
echo_nav($topic_id);
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('</table>');

$PHP_OUTPUT.=('</body>');
$PHP_OUTPUT.=('</html>');

?>