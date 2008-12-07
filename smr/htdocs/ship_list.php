<?

// ********************************
// *
// * I n c l u d e s   h e r e
// *
// ********************************

include('config.inc');
require_once($LIB . 'global/smr_db.inc');

$db = new SMR_DB();

$PHP_OUTPUT.=('<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">');
$PHP_OUTPUT.=('<html>');
$PHP_OUTPUT.=('<head>');
$PHP_OUTPUT.=('<link rel="stylesheet" type="text/css" href="default.css">');
$PHP_OUTPUT.=('<title>Ship List</title>');
$PHP_OUTPUT.=('<meta http-equiv="pragma" content="no-cache">');
$PHP_OUTPUT.=('</head>');
$seq = $_REQUEST['seq'];
$order = $_REQUEST['order'];
$hardwarea = $_REQUEST['hardwarea'];
$PHP_OUTPUT.=('<body>');
if (empty($seq))
	$seq = 'ASC';
elseif ($seq == 'ASC')
	$seq = 'DESC';
else
	$seq = 'ASC';
if (isset($order))
	$order_by = $order .' '. $seq;
elseif (isset($hardwarea)) {

    $ship_array = array();
    $db->query('SELECT ship_type.ship_type_id as id FROM ship_type, ship_type_support_hardware, race ' .
                'WHERE race.race_id = ship_type.race_id AND ' .
                'ship_type_support_hardware.ship_type_id = ship_type.ship_type_id AND ' .
                'ship_type_support_hardware.hardware_type_id = '.$hardwarea.' ' .
                'ORDER BY max_amount '.$seq);
    while ($db->next_record())
    	$ship_array[] = $db->f('id');

} else
	$order_by = 'ship_type.ship_type_id';


$order_by .= ', ship_name ASC, ship_type_support_hardware.hardware_type_id ASC';
$PHP_OUTPUT.=('<form>');

$site = $URL . '/ship_list.php';

$PHP_OUTPUT.=('<table class="standard"  cellspacing="0">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th align="left"><a href="$site?order=ship_name&seq='.$seq.'"><span style=color:#80C870;>Ship Name</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?order=race_name&seq='.$seq.'"><span style=color:#80C870;>Ship Race</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?order=cost&seq='.$seq.'"><span style=color:#80C870;>Cost</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?order=speed&seq='.$seq.'"><span style=color:#80C870;>Speed</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?order=hardpoint&seq='.$seq.'"><span style=color:#80C870;>Hardpoints</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?order=buyer_restriction&seq='.$seq.'"><span style=color:#80C870;>Restriction</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?order=lvl_needed&seq='.$seq.'"><span style=color:#80C870;>Level Needed(Semi War)</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?hardwarea=1&seq='.$seq.'"><span style=color:#80C870;>Shields</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?hardwarea=2&seq='.$seq.'"><span style=color:#80C870;>Armor</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?hardwarea=3&seq='.$seq.'"><span style=color:#80C870;>Cargo</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?hardwarea=4&seq='.$seq.'"><span style=color:#80C870;>Combat Drones</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?hardwarea=5&seq='.$seq.'"><span style=color:#80C870;>Scout Drones</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?hardwarea=6&seq='.$seq.'"><span style=color:#80C870;>Mines</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?hardwarea=7&seq='.$seq.'"><span style=color:#80C870;>Scanner</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?hardwarea=8&seq='.$seq.'"><span style=color:#80C870;>Cloak</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?hardwarea=9&seq='.$seq.'"><span style=color:#80C870;>Illusion</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?hardwarea=10&seq='.$seq.'"><span style=color:#80C870;>Jump</span></a></th>');
$PHP_OUTPUT.=('<th align="center"><a href="$site?hardwarea=11&seq='.$seq.'"><span style=color:#80C870;>Drone Scrambler</span></a></th>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</form>');
$loop = 1;

if (is_array($ship_array)) {

    while (sizeof($ship_array) > 0) {

		$db_id = array_shift ($ship_array);
		//$PHP_OUTPUT.=(sizeof($ship_array));
		$db->query('SELECT * FROM ship_type, ship_type_support_hardware, race ' .
                'WHERE race.race_id = ship_type.race_id AND ' .
                'ship_type_support_hardware.ship_type_id = ship_type.ship_type_id AND ' .
                'ship_type.ship_type_id = $db_id ' .
                'ORDER BY ship_type_support_hardware.hardware_type_id');

		while ($db->next_record()) {

		    //we want to put them all in an array so we dont have to have 15 td rows
    		$stat = array();
	    	$name = str_replace(' ','&nbsp;',$db->f('ship_name'));
	    	$stat[] = $name;
			$race = str_replace(' ','&nbsp;',$db->f('race_name'));
    		$stat[] = $race;
		    $cost = $db->f('cost');
    		$stat[] = $cost;
	    	$speed = $db->f('speed');
	    	$stat[] = $speed;
		    $hardpoints = $db->f('hardpoint');
    		$stat[] = $hardpoints;
		    if ($db->f('buyer_restriction') == 1)
    		    $restriction = '<font color=green>Good</font>';
	    	elseif ($db->f('buyer_restriction') == 2)
    	    	$restriction = '<font color=red>Evil</font>';
		    else
    		    $restriction = '&nbsp;';
	    	$stat[] = $restriction;
            $level = $db->f('lvl_needed');
            $stat[] = $level;
	    	$hardware_dis = array();
		    $hardware_dis[1] = $db->f('max_amount');
		    $stat[] = $hardware_dis[1];
    		$hardware_id = 2;
	    	//get our hardware
	    	while ($hardware_id <= 11) {

		        if($db->next_record()) {

		        	if ($hardware_id < 7)
    		        	$stat[] = $db->f('max_amount');
        		    elseif ($db->f('max_amount') == 1)
            			$stat[] = 'Yes';
	            	else
    	        		$stat[] = '&nbsp;';
	        		$hardware_id++;

		        }

		    }
    		$loop++;
	    	$PHP_OUTPUT.=('<tr>');

		    foreach ($stat as $value)
    		    $PHP_OUTPUT.=('<td align="center">$value</td>');

		    $PHP_OUTPUT.=('</tr>');

		}

    }
	$PHP_OUTPUT.=('</table>');
} else {

	$db->query('SELECT * FROM ship_type, ship_type_support_hardware, race ' .
                'WHERE race.race_id = ship_type.race_id AND ' .
                'ship_type_support_hardware.ship_type_id = ship_type.ship_type_id ' .
                'ORDER BY $order_by');

	while ($db->next_record()) {

		//we want to put them all in an array so we dont have to have 15 td rows
    	$stat = array();
	   	$name = str_replace(' ','&nbsp;',$db->f('ship_name'));
	    $stat[] = $name;
		$race = str_replace(' ','&nbsp;',$db->f('race_name'));
    	$stat[] = $race;
		$cost = $db->f('cost');
    	$stat[] = $cost;
	    $speed = $db->f('speed');
	    $stat[] = $speed;
		$hardpoints = $db->f('hardpoint');
    	$stat[] = $hardpoints;
		if ($db->f('buyer_restriction') == 1)
    		$restriction = '<font color=green>Good</font>';
	    elseif ($db->f('buyer_restriction') == 2)
    		$restriction = '<font color=red>Evil</font>';
		else
    		$restriction = '&nbsp;';
	    $stat[] = $restriction;
		$level = $db->f('lvl_needed');
        $stat[] = $level;
	    $hardware_dis = array();
		$hardware_dis[1] = $db->f('max_amount');
		$stat[] = $hardware_dis[1];
    	$hardware_id = 2;
	    //get our hardware
	    while ($hardware_id <= 11) {

			if($db->next_record()) {

		    	if ($hardware_id < 7)
    		    	$stat[] = $db->f('max_amount');
        		elseif ($db->f('max_amount') == 1)
            		$stat[] = 'Yes';
	            else
    	        	$stat[] = '&nbsp;';
	        	$hardware_id++;

		    }

		}
    	$loop++;
	    $PHP_OUTPUT.=('<tr>');

		foreach ($stat as $value)
    		$PHP_OUTPUT.=('<td align="center">$value</td>');

		$PHP_OUTPUT.=('</tr>');

	}
$PHP_OUTPUT.=('</table>');
}
?>