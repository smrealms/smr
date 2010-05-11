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
	?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">

<html>
	<head>
	<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
		<title>Space Merchant Realms - Ship List</title>
		<meta http-equiv="pragma" content="no-cache">
	</head><?php

	$seq = @$_REQUEST['seq'];
	$order = @$_REQUEST['order'];
	$hardwarea = @$_REQUEST['hardwarea'];
	echo ('<body>');
	if (empty($seq))
		$seq = 'ASC';
	elseif ($seq == 'ASC')
		$seq = 'DESC';
	else
		$seq = 'ASC';

	$allowedOrders = array('ship_name','race_name','cost','speed','hardpoint','buyer_restriction','lvl_needed');

	if (!empty($order) && in_array($order,$allowedOrders))
		$order_by = $order .' '. $seq;
	else
		$order_by = 'ship_type.ship_type_id';


	$order_by .= ', ship_name ASC, ship_type_support_hardware.hardware_type_id ASC';


	if(!empty($hardwarea) && is_numeric($hardwarea) && $hardwarea >=1 && $hardwarea <= 11)
	{
		$db->query('SELECT ship_type_id FROM ship_type_support_hardware ' .
				'WHERE hardware_type_id = '.$hardwarea.' ' .
				'ORDER BY max_amount '.$seq);
		$db2 = new SmrMySqlDatabase();
		while ($db->nextRecord())
		{
			$db2->query('SELECT * FROM ship_type, ship_type_support_hardware, race ' .
				'WHERE race.race_id = ship_type.race_id AND ' .
				'ship_type_support_hardware.ship_type_id = ship_type.ship_type_id AND ' .
				'ship_type.ship_type_id=' . $db->getField('ship_type_id') . ' ' .
				'ORDER BY ship_type_support_hardware.hardware_type_id ASC');
			if($db2->nextRecord())
				$shipArray[] = buildShipStats($db2);
		}
	}
	else
	{
		$db->query('SELECT * FROM ship_type, ship_type_support_hardware, race ' .
				'WHERE race.race_id = ship_type.race_id AND ' .
				'ship_type_support_hardware.ship_type_id = ship_type.ship_type_id ' .
				'ORDER BY '.$order_by);
		while ($db->nextRecord())
		{
			$shipArray[] = buildShipStats($db);
		}
	}
	?>

	<table class="standard">
		<tr>
			<th align="left"><a href="?order=ship_name&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Ship Name</span></a></th>
			<th align="center"><a href="?order=race_name&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Ship Race</span></a></th>
			<th align="center"><a href="?order=cost&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Cost</span></a></th>
			<th align="center"><a href="?order=speed&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Speed</span></a></th>
			<th align="center"><a href="?order=hardpoint&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Hardpoints</span></a></th>
			<th align="center"><a href="?order=buyer_restriction&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Restriction</span></a></th>
			<th align="center"><a href="?order=lvl_needed&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Level Needed(Semi War)</span></a></th>
			<th align="center"><a href="?hardwarea=1&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Shields</span></a></th>
			<th align="center"><a href="?hardwarea=2&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Armour</span></a></th>
			<th align="center"><a href="?hardwarea=3&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Cargo</span></a></th>
			<th align="center"><a href="?hardwarea=4&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Combat Drones</span></a></th>
			<th align="center"><a href="?hardwarea=5&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Scout Drones</span></a></th>
			<th align="center"><a href="?hardwarea=6&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Mines</span></a></th>
			<th align="center"><a href="?hardwarea=7&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Scanner</span></a></th>
			<th align="center"><a href="?hardwarea=8&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Cloak</span></a></th>
			<th align="center"><a href="?hardwarea=9&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Illusion</span></a></th>
			<th align="center"><a href="?hardwarea=10&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Jump</span></a></th>
			<th align="center"><a href="?hardwarea=11&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Drone Scrambler</span></a></th>
		</tr><?php

	foreach($shipArray as $stat)
	{
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

function buildShipStats($db)
{
	//we want to put them all in an array so we dont have to have 15 td rows
	$stat = array();
   	$name = str_replace(' ','&nbsp;',$db->getField('ship_name'));
    $stat[] = $name;
	$race = str_replace(' ','&nbsp;',$db->getField('race_name'));
	$stat[] = $race;
	$cost = $db->getField('cost');
	$stat[] = $cost;
    $speed = $db->getField('speed');
    $stat[] = $speed;
	$hardpoints = $db->getField('hardpoint');
	$stat[] = $hardpoints;
	if ($db->getField('buyer_restriction') == 1)
		$restriction = '<font color="green">Good</font>';
    elseif ($db->getField('buyer_restriction') == 2)
		$restriction = '<font color="red">Evil</font>';
	else
		$restriction = '&nbsp;';
    $stat[] = $restriction;
	$level = $db->getField('lvl_needed');
    $stat[] = $level;
	$stat[] = $db->getField('max_amount');
	$hardware_id = 2;
    //get our hardware
    while ($hardware_id <= 11)
    {
		if($db->nextRecord())
		{

	    	if ($hardware_id < 7)
		    	$stat[] = $db->getField('max_amount');
    		elseif ($db->getField('max_amount') == 1)
        		$stat[] = 'Yes';
            else
	        	$stat[] = '&nbsp;';
	    }
        $hardware_id++;
	}
	return $stat;
}
?>