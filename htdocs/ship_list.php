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
	SELECT {
		border: solid #80C870 1px;
		background-color: #0A4E1D;
		color: #80C870; }
	OPTGROUP {
		border: solid #80C870 1px;
	}
	</style>
	<script>
		//JS code by Astax to foster filtering the results
		
		//Use window variable to store filter values, this is kinda like a JS equivellent of global
		window.filter = new Array("All", "All", "All", "All", "All", "All", "All", "All", "All", "All",
		"All", "All", "All", "All", "All", "All", "All", "All", "All");
		function classPickf() {
			var option 	= document.getElementById("classPick");
			var selected;
			
			for (var i=0; i < option.options.length; i++) {
				if (option.options[i].selected)
					selected = option.options[i].value;
			}
			if (selected == "null") 
				window.filter[2] = "All";
			else
				window.filter[2] = selected;
			applyFilter();
		
		}
		function racePickf() {
			var option 	= document.getElementById("racePick");
			var selected;
			
			for (var i=0; i < option.options.length; i++) {
				if (option.options[i].selected)
					selected = option.options[i].value;
			}
			if (selected == "null") 
				window.filter[1] = "All";
			else
				window.filter[1] = selected;
			applyFilter();
		
		}
		function speedPickf() {
			var option 	= document.getElementById("speedPick");
			var selected;
			
			for (var i=0; i < option.options.length; i++) {
				if (option.options[i].selected)
					selected = option.options[i].value;
			}
			if (selected == "null") 
				window.filter[4] = "All";
			else
				window.filter[4] = selected;
			applyFilter();
		
		}
		function hpPickf() {
			var option 	= document.getElementById("hpPick");
			var selected;
			
			for (var i=0; i < option.options.length; i++) {
				if (option.options[i].selected)
					selected = option.options[i].value;
			}
			if (selected == "null") 
				window.filter[5] = "All";
			else
				window.filter[5] = selected;
			applyFilter();
		
		}
		function restrictPickf() {
			var option 	= document.getElementById("restrictPick");
			var selected;
			
			for (var i=0; i < option.options.length; i++) {
				if (option.options[i].selected)
					selected = option.options[i].value;
			}
			if (selected == "null") 
				window.filter[6] = "All";
			else
				window.filter[6] = selected;
			applyFilter();
		
		}
		function scannerPickf() {
			var option 	= document.getElementById("scannerPick");
			var selected;
			
			for (var i=0; i < option.options.length; i++) {
				if (option.options[i].selected)
					selected = option.options[i].value;
			}
			if (selected == "null") 
				window.filter[13] = "All";
			else
				window.filter[13] = selected;
			applyFilter();
		
		}
		function cloakPickf() {
			var option 	= document.getElementById("cloakPick");
			var selected;
			
			for (var i=0; i < option.options.length; i++) {
				if (option.options[i].selected)
					selected = option.options[i].value;
			}
			if (selected == "null") 
				window.filter[14] = "All";
			else
				window.filter[14] = selected;
			applyFilter();
		
		}
		function illusionPickf() {
			var option 	= document.getElementById("illusionPick");
			var selected;
			
			for (var i=0; i < option.options.length; i++) {
				if (option.options[i].selected)
					selected = option.options[i].value;
			}
			if (selected == "null") 
				window.filter[15] = "All";
			else
				window.filter[15] = selected;
			applyFilter();
		
		}
		function jumpPickf() {
			var option 	= document.getElementById("jumpPick");
			var selected;
			
			for (var i=0; i < option.options.length; i++) {
				if (option.options[i].selected)
					selected = option.options[i].value;
			}
			if (selected == "null") 
				window.filter[16] = "All";
			else
				window.filter[16] = selected;
			applyFilter();
		
		}
		function scramblePickf() {
			var option 	= document.getElementById("scramblePick");
			var selected;
			
			for (var i=0; i < option.options.length; i++) {
				if (option.options[i].selected)
					selected = option.options[i].value;
			}
			if (selected == "null") 
				window.filter[17] = "All";
			else
				window.filter[17] = selected;
			applyFilter();
		
		}
		function applyFilter() {
			var table 	= document.getElementById("table");
			for (var i=1; i < table.rows.length; i++) {
					show = true;
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
	
	$class 		= buildSelector($db, "classPick", "ship_class_name", "ship_class");
	$race 		= buildSelector($db, "racePick", "race_name", "race");
	$speed 		= buildSelector($db, "speedPick", "speed", "ship_type");
	$hardpoint  = buildSelector($db, "hpPick", "hardpoint", "ship_type");
	$restrict 	= buildRestriction();
	$scanner 	= buildToggle("scannerPick");
	$cloak 		= buildToggle("cloakPick");
	$illusion 	= buildToggle("illusionPick");
	$jump 		= buildToggle("jumpPick");
	$scramble 	= buildToggle("scramblePick");
	
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

	foreach($shipArray as $stat) {
	    echo ('<tr>');
		foreach ($stat as $value)
			echo ('<td align="center">'.$value.'</td>');
		echo ('</tr>');
	} ?>
	</table></div></div><?php
}
catch(Exception $e) {
	handleException($e);
}

function buildSelector($db,  $id, $name, $table) {
	$selector = '<br><form class="selector" action="" method="get">';
	$selector .= '<select id="'.$id.'" name="'.$name.'" onchange="'.$id.'f()"><option value=null>All</option>';
		$db->query("select distinct ".$name." from ".$table." order by ".$name);
	while ($db->nextRecord()) {
		$selector .= '<option value="'.$db->getField($name).'">'
		.$db->getField($name).'</option>';
	}
	$selector .= '</select></form>';
	return $selector;
}

function buildRestriction() {
	$restrict = '<br><form class="selector" action="" method="get">'
	.'<select id="restrictPick" name="restrict" onchange="restrictPickf()">'
	.'<option value=null>All</option>'
	.'<option value="">None</option>'
	."<option value='<font color=\"green\">Good</font>'>Good</option>"
	."<option value='<font color=\"red\">Evil</font>' style=\"color: red;\">Evil</option></select></form>";
	
	return $restrict;

}

function buildToggle($id) {
	$toggle = '<br><form class="selector" action="" method="get">'
	.'<select id="'.$id.'" name="'.$id.'" onchange="'.$id.'f()">'
	.'<option value=null>All</option>'
	.'<option value="Yes">Yes</option>'
	.'<option value="">No</option></select></form>';
	
	return $toggle;

}

function buildShipStats($db) {
	//we want to put them all in an array so we dont have to have 15 td rows
	$stat = array();
    $stat[] = str_replace(' ','&nbsp;',$db->getField('ship_name'));
	//$stat[] = str_replace(' ','&nbsp;',$db->getField('race_name'));
	$stat[] = $db->getField('race_name');
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
//    $stat[] = $db->getInt('lvl_needed');
	$stat[] = $db->getInt('max_amount');
	$hardware_id = 2;
    //get our hardware
    while ($hardware_id <= 11)
    {
		if($db->nextRecord()) {

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