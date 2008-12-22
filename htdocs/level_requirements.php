<?

// ********************************
// *
// * I n c l u d e s   h e r e
// *
// ********************************

include('config.inc');
require_once(LIB . 'global/smr_db.inc');

$db = new SMR_DB();

$PHP_OUTPUT.=('<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">');
$PHP_OUTPUT.=('<html>');
$PHP_OUTPUT.=('<head>');
$PHP_OUTPUT.=('<link rel="stylesheet" type="text/css" href="default.css">');
$PHP_OUTPUT.=('<title>Level Requirements</title>');
$PHP_OUTPUT.=('<meta http-equiv="pragma" content="no-cache">');
$PHP_OUTPUT.=('</head>');

$PHP_OUTPUT.=('<body>');
$db->query('SELECT * FROM level ORDER BY level_id');
$PHP_OUTPUT.=('<table border="0" class="standard" cellspacing="0" cellpadding="5">');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th align="center" style="color:#80C870;">Rank Level</th>');
$PHP_OUTPUT.=('<th align="center" style="color:#80C870;">Rank Name</th>');
$PHP_OUTPUT.=('<th align="center" style="color:#80C870;">Required Experience</th>');
$PHP_OUTPUT.=('</tr>');

while ($db->next_record()) {

	$level = $db->f('level_id');
	$name = $db->f('level_name');
	$require = $db->f('requirement');

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="center">$level</td>');
	$PHP_OUTPUT.=('<td align="center">$name</td>');
	$PHP_OUTPUT.=('<td align="center">$require</td>');
	$PHP_OUTPUT.=('</tr>');

}

$PHP_OUTPUT.=('</table>');

?>