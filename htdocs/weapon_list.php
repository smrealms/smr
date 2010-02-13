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
	echo ('<title>Weapon List</title>');
	echo ('<meta http-equiv="pragma" content="no-cache">');
	echo ('</head>');
	
	echo ('<body>');
	$seq = isset($_REQUEST['seq']) ? $_REQUEST['seq'] : '';
	if (empty($seq))
		$seq = 'ASC';
	elseif ($seq == 'ASC')
		$seq = 'DESC';
	else
		$seq = 'ASC';
	
	$columnNames = array('weapon_name','race_name','cost','shield_damage','armour_damage','accuracy','power_level','buyer_restriction');
	if (isset($_REQUEST['order'])&&in_array($_REQUEST['order'],$columnNames))
		$order_by = $_REQUEST['order'];
	else
		$order_by = 'weapon_type_id';
	
	$db->query('SELECT * FROM weapon_type, race WHERE weapon_type.race_id = race.race_id ORDER BY '.$order_by.' '.$seq);
	echo ('<table class="standard">');
	echo ('<tr>');
	echo ('<th align="center"><a href="?order=weapon_name&seq='.$seq.'"><span style=color:#80C870;>Weapon Name</span></a></th>');
	echo ('<th align="center"><a href="?order=race_name&seq='.$seq.'"><span style=color:#80C870;>Race</span></a></th>');
	echo ('<th align="center"><a href="?order=cost&seq='.$seq.'"><span style=color:#80C870;>Cost</span></a></th>');
	echo ('<th align="center"><a href="?order=shield_damage&seq='.$seq.'"><span style=color:#80C870;>Shield Damage</span></a></th>');
	echo ('<th align="center"><a href="?order=armour_damage&seq='.$seq.'"><span style=color:#80C870;>Armour Damage</span></a></th>');
	echo ('<th align="center"><a href="?order=accuracy&seq='.$seq.'"><span style=color:#80C870;>Accuracy</span></a></th>');
	echo ('<th align="center"><a href="?order=power_level&seq='.$seq.'"><span style=color:#80C870;>Power Level</span></a></th>');
	echo ('<th align="center"><a href="?order=buyer_restriction&seq='.$seq.'"><span style=color:#80C870;>Restriction</span></a></th>');
	echo ('</tr>');
	while ($db->nextRecord())
	{
		//we need an array so we dont have 8 td rows
	    $stat = array();
	    $stat[] = $db->getField('weapon_name');
	    $stat[] = $db->getField('race_name');
	    $stat[] = $db->getField('cost');
	    $stat[] = $db->getField('shield_damage');
	    $stat[] = $db->getField('armour_damage');
	    $stat[] = $db->getField('accuracy');
	    $stat[] = $db->getField('power_level');
		if ($db->getField('buyer_restriction') == 1)
	    	$restriction = '<font color="green">Good</font>';
		elseif ($db->getField('buyer_restriction') == 2)
	    	$restriction = '<font color="red">Evil</font>';
		else
	    	$restriction = '&nbsp;';
	    $stat[] = $restriction;
	
	    echo ('<tr>');
	    foreach ($stat as $value)
		    echo ('<td align="center">'.$value.'</td>');
	
	    echo ('</tr>');
	
	}
	echo ('</table>');
}
catch(Exception $e)
{
	handleException($e);
}
?>