<?

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
echo ('<link rel="stylesheet" type="text/css" href="default.css">');
echo ('<title>Level Requirements</title>');
echo ('<meta http-equiv="pragma" content="no-cache">');
echo ('</head>');

echo ('<body>');
$db->query('SELECT * FROM level ORDER BY level_id');
echo ('<table border="0" class="standard" cellspacing="0" cellpadding="5">');

echo ('<tr>');
echo ('<th align="center" style="color:#80C870;">Rank Level</th>');
echo ('<th align="center" style="color:#80C870;">Rank Name</th>');
echo ('<th align="center" style="color:#80C870;">Required Experience</th>');
echo ('</tr>');

while ($db->next_record()) {

	$level = $db->f('level_id');
	$name = $db->f('level_name');
	$require = $db->f('requirement');

	echo ('<tr>');
	echo ('<td align="center">'.$level.'</td>');
	echo ('<td align="center">'.$name.'</td>');
	echo ('<td align="center">'.$require.'</td>');
	echo ('</tr>');

}

echo ('</table>');

?>