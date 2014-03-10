<?php
try {
	// ********************************
	// *
	// * I n c l u d e s   h e r e
	// *
	// ********************************

	require_once('config.inc');
	require_once(LIB . 'Default/SmrMySqlDatabase.class.inc');

	$db = new SmrMySqlDatabase();
	?>
<!DOCTYPE html>

<html>
	<head>
	<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS; ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo DEFAULT_CSS_COLOUR; ?>">
		<title>Space Merchant Realms - Ship List</title>
		<meta http-equiv="pragma" content="no-cache">
	<style>
	#container {
		margin: 0;
		padding: 0;
		border: 0;
	}
	#main {
		margin: 0;
		padding: 0;
		border: 0;
	}
	select {
		border: solid #80C870 1px;
		background-color: #0A4E1D;
		color: #80C870; 
	}
	optgroup {
		border: solid #80C870 1px;
	}
	</style>
	<script>
		//JS code by Astax to foster filtering the results
		
		//Use window variable to store filter values, this is kinda like a JS equivellent of global
		window.filter = ["All", "All", "All", "All", "All", "All", "All", "All", "All", "All",
		"All", "All", "All", "All", "All", "All", "All", "All", "All"];
		function classPickf() {
			filterSelect("classPick", 2);	
		}
		function racePickf() {
			filterSelect("racePick", 1);		
		}
		function speedPickf() {
			filterSelect("speedPick", 4);		
		}
		function hpPickf() {
			filterSelect("hpPick", 5);		
		}
		function restrictPickf() {
			filterSelect("restrictPick", 6);			
		}
		function scannerPickf() {
			filterSelect("scannerPick", 13);
		}
		function cloakPickf() {
			filterSelect("cloakPick", 14);			
		}
		function illusionPickf() {
			filterSelect("illusionPick", 15);	
		}
		function jumpPickf() {
			filterSelect("jumpPick", 16);	
		}
		function scramblePickf() {
			filterSelect("scramblePick", 17);	
		}
		function filterSelect(selectId, filterId) {
			var option 	= document.getElementById(selectId);
			var selected = option.options[option.selectedIndex].value;

			window.filter[filterId] = selected;
			applyFilter();
		
		}
		function applyFilter() {
			var table 	= document.getElementById("table");
			for (var i=1; i < table.rows.length; i++) {
				var show = true;
				for (var j=0; j < table.rows[i].cells.length; j++) {
					if (window.filter[j] == "All")
						continue;
					if (table.rows[i].cells[j].innerHTML != window.filter[j]) {
						show = false;
						break;
					}
				}
				if (show)
					table.rows[i].style.display="";
				else
					table.rows[i].style.display="none";	
			}		
		}
	
	</script>
	</head><?php

	$seq = @$_REQUEST['seq'];
	$order = @$_REQUEST['order'];
	$hardwarea = @$_REQUEST['hardwarea'];
	
	$class		= buildSelector($db, 'class', 'ship_class_name', 'ship_class');
	$race		= buildSelector($db, 'race', 'race_name', 'race', 'race_id');
	$speed		= buildSelector($db, 'speed', 'speed', 'ship_type');
	$hardpoint	= buildSelector($db, 'hp', 'hardpoint', 'ship_type');
	$restrict	= buildRestriction();
	$scanner	= buildToggle('scannerPick');
	$cloak		= buildToggle('cloakPick');
	$illusion	= buildToggle('illusionPick');
	$jump		= buildToggle('jumpPick');
	$scramble	= buildToggle('scramblePick');
	
	echo ('<body>');
	if (empty($seq)) {
		$seq = 'ASC';
	}
	elseif ($seq == 'ASC') {
		$seq = 'DESC';
	}
	else {
		$seq = 'ASC';
	}	
	
	$allowedOrders = array('ship_name','race_name','cost','speed','hardpoint','buyer_restriction','lvl_needed','ship_class_name');

	if (!empty($order) && in_array($order,$allowedOrders)) {
		$order_by = $order .' '. $seq;
	}
	else {
		$order_by = 'ship_type.ship_type_id';
	}


	$order_by .= ', ship_name ASC, ship_type_support_hardware.hardware_type_id ASC';


	if(!empty($hardwarea) && is_numeric($hardwarea) && $hardwarea >=1 && $hardwarea <= 11) {
		$db->query('SELECT ship_type_id
					FROM ship_type_support_hardware
					WHERE hardware_type_id = '.$db->escapeNumber($hardwarea).'
					ORDER BY max_amount '.$seq);
		$db2 = new SmrMySqlDatabase();
		while ($db->nextRecord()) {
			$db2->query('SELECT *
						FROM ship_type
						JOIN ship_type_support_hardware USING(ship_type_id)
						JOIN ship_class USING(ship_class_id)
						JOIN race USING(race_id)
						WHERE ship_type_id=' . $db->escapeNumber($db->getInt('ship_type_id')) . '
						ORDER BY hardware_type_id ASC');
			if($db2->nextRecord()) {
				$shipArray[] = buildShipStats($db2);
			}
		}
	}
	else {
		$db->query('SELECT *
					FROM ship_type
					JOIN ship_type_support_hardware USING(ship_type_id)
					JOIN ship_class USING(ship_class_id)
					JOIN race USING(race_id)
					ORDER BY '.$order_by);
		while ($db->nextRecord()) {
			$shipArray[] = buildShipStats($db);
		}
	}
	?>
	<div id="container" style="padding: 0;">
	<div style="width:1400px; margin-left:auto; margin-right:auto;">
	<table id="table" class="standard">
		<?php /*<tr style="position:fixed">*/ ?>
		<tr >
			<th align="center"><a href="?order=ship_name&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Ship Name</span></a></th>
			<th align="center"><a href="?order=race_name&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Race</span></a>
				<?php echo $race ?></th>
			<th align="center">Class<?php echo $class; ?></th>
			<th align="center"><a href="?order=cost&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Cost</span></a></th>
			<th align="center"><a href="?order=speed&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Speed</span></a>
				<?php echo $speed ?></th>
			<th align="center"><a href="?order=hardpoint&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Hardpoints</span></a>
				<?php echo $hardpoint ?></th>
			<th align="center"><a href="?order=buyer_restriction&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Restriction</span></a>
				<?php echo $restrict ?></th><?php
/*			<th align="center"><a href="?order=lvl_needed&seq=<?php echo $seq; ?>"><span style="color:#80C870;">Level Needed(Semi War)</span></a></th>*/ ?>
			<th align="center"><a href="?hardwarea=1&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Shields</span></a></th>
			<th align="center"><a href="?hardwarea=2&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Armour</span></a></th>
			<th align="center"><a href="?hardwarea=3&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Cargo</span></a></th>
			<th align="center"><a href="?hardwarea=4&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Drones</span></a></th>
			<th align="center"><a href="?hardwarea=5&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Scouts</span></a></th>
			<th align="center"><a href="?hardwarea=6&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Mines</span></a></th>
			<th align="center"><a href="?hardwarea=7&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Scanner</span></a>
				<?php echo $scanner ?></th>
			<th align="center"><a href="?hardwarea=8&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Cloak</span></a>
				<?php echo $cloak ?></th>
			<th align="center"><a href="?hardwarea=9&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Illusion</span></a>
				<?php echo $illusion ?></th>
			<th align="center"><a href="?hardwarea=10&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Jump</span></a>
				<?php echo $jump ?></th>
			<th align="center"><a href="?hardwarea=11&amp;seq=<?php echo $seq; ?>"><span style="color:#80C870;">Scrambler</span></a>
				<?php echo $scramble ?></th>
		</tr><?php
	$search = array("'", " ", ",", "<", ">", "\"");
	foreach($shipArray as $stat) {
		echo ('<tr>');
		foreach ($stat as $value) {
			$class = '';
			if(is_array($value)) {
				$class = 'class="' . $value[0] . '"';
				$value = $value[1];
			}
			echo ('<td align="center" '.$class.'>'.$value.'</td>');
		}
		echo ('</tr>');
	} ?>
	</table></div></div><?php
}
catch(Exception $e) {
	handleException($e);
}

function buildSelector($db, $id, $name, $table, $typeField = false) {
	$selector = '<br><select id="'.$id.'Pick" name="'.$name.'" onchange="'.$id.'Pickf()"><option value="All">All</option>';
	$db->query('
		SELECT DISTINCT '.$name. ($typeField!==false?',' . $typeField: '') . '
		FROM '.$table.'
		ORDER BY '.$name);
	$class = '';
	while ($db->nextRecord()) {
		if($typeField !== false) {
			$class = 'class="' . $id . $db->getInt($typeField) . '"';
		}
		$selector .= '<option '.$class.' value="'.$db->getField($name).'">'
			.$db->getField($name).'</option>';
	}
	$selector .= '</select>';
	return $selector;
}

function buildRestriction() {
	$restrict = '<br><select id="restrictPick" name="restrict" onchange="restrictPickf()">'
	.'<option value="All">All</option>'
	.'<option value="">None</option>'
	."<option value='<font color=\"green\">Good</font>'>Good</option>"
	."<option value='<font color=\"red\">Evil</font>' style=\"color: red;\">Evil</option></select>";
	
	return $restrict;

}

function buildToggle($id) {
	$toggle = '<br><select id="'.$id.'" name="'.$id.'" onchange="'.$id.'f()">'
	.'<option value="All">All</option>'
	.'<option value="Yes">Yes</option>'
	.'<option value="">No</option></select>';
	
	return $toggle;

}

function buildShipStats($db) {
	//we want to put them all in an array so we dont have to have 15 td rows
	$stat = array();
	$stat[] = str_replace(' ','&nbsp;',$db->getField('ship_name'));
	//$stat[] = str_replace(' ','&nbsp;',$db->getField('race_name'));
	$stat[] = array('race' . $db->getInt('race_id'), $db->getField('race_name'));
	$stat[] = str_replace(' ','&nbsp;',$db->getField('ship_class_name'));
	$stat[] = number_format($db->getInt('cost'));
	$stat[] = $db->getInt('speed');
	$stat[] = $db->getInt('hardpoint');
	if ($db->getField('buyer_restriction') == 1)
		$restriction = '<font color="green">Good</font>';
	elseif ($db->getField('buyer_restriction') == 2)
		$restriction = '<font color="red">Evil</font>';
	else
		$restriction = '';
	$stat[] = $restriction;
//	$stat[] = $db->getInt('lvl_needed');
	$stat[] = number_format($db->getInt('max_amount'));
	$hardware_id = 2;
	//get our hardware
	while ($hardware_id <= 11)
	{
		if($db->nextRecord()) 
		{
			if ($hardware_id < 7)
				$stat[] = number_format($db->getInt('max_amount'));
			elseif ($db->getInt('max_amount') == 1)
				$stat[] = 'Yes';
			else
				$stat[] = '';
		}
		$hardware_id++;
	}
	return $stat;
}
?>
