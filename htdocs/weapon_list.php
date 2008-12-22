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
$PHP_OUTPUT.=('<title>Weapon List</title>');
$PHP_OUTPUT.=('<meta http-equiv="pragma" content="no-cache">');
$PHP_OUTPUT.=('</head>');

$PHP_OUTPUT.=('<body>');
$seq = $_REQUEST['seq'];
$order = $_REQUEST['order'];
if (empty($seq))
	$seq = 'ASC';
elseif ($seq == 'ASC')
	$seq = 'DESC';
else
	$seq = 'ASC';

if (isset($order))
	$order_by = $order;
else
	$order_by = 'weapon_type_id';

$db->query('SELECT * FROM weapon_type, race WHERE weapon_type.race_id = race.race_id ORDER BY $order_by '.$seq);
$PHP_OUTPUT.=('<table class="standard" cellspacing="0">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?order=weapon_name&seq='.$seq.'"><span style=color:#80C870;>Weapon Name</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?order=race_name&seq='.$seq.'"><span style=color:#80C870;>Race</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?order=cost&seq='.$seq.'"><span style=color:#80C870;>Cost</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?order=shield_damage&seq='.$seq.'"><span style=color:#80C870;>Shield Damage</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?order=armor_damage&seq='.$seq.'"><span style=color:#80C870;>Armor Damage</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?order=accuracy&seq='.$seq.'"><span style=color:#80C870;>Accuracy</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?order=power_level&seq='.$seq.'"><span style=color:#80C870;>Power Level</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?order=buyer_restriction&seq='.$seq.'"><span style=color:#80C870;>Restriction</span></a></th>');
$PHP_OUTPUT.=('</tr>');
while ($db->next_record()) {

	//we need an array so we dont have 8 td rows
    $stat = array();
    $stat[] = $db->f('weapon_name');
    $stat[] = $db->f('race_name');
    $stat[] = $db->f('cost');
    $stat[] = $db->f('shield_damage');
    $stat[] = $db->f('armor_damage');
    $stat[] = $db->f('accuracy');
    $stat[] = $db->f('power_level');
	if ($db->f('buyer_restriction') == 1)
    	$restriction = '<font color=green>Good</font>';
	elseif ($db->f('buyer_restriction') == 2)
    	$restriction = '<font color=red>Evil</font>';
	else
    	$restriction = '&nbsp;';
    $stat[] = $restriction;

    $PHP_OUTPUT.=('<tr>');
    foreach ($stat as $value)
	    $PHP_OUTPUT.=('<td align="center">$value</td>');

    $PHP_OUTPUT.=('</tr>');

}
$PHP_OUTPUT.=('</table>');
?>