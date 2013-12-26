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
	
	echo ('<!DOCTYPE html');
	
	echo ('<html>');
	echo ('<head>');
	echo ('<link rel="stylesheet" type="text/css" href="css/Default.css">');
	echo ('<link rel="stylesheet" type="text/css" href="css/Default/Default.css">');
	echo ('<title>Weapon List</title>');
	echo ('<meta http-equiv="pragma" content="no-cache">');?>
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
		// Benoit Asselin - http://www.ab-d.fr
		Array.prototype.in_array = function(p_val) {
			for(var i = 0, l = this.length; i < l; i++) {
				if(this[i] == p_val) {
					return true;
				}
			}
			return false;
		}
		//JS code by Astax to foster filtering the results
		
		//Use window variable to store filter values, this is kinda like a JS equivellent of global
		window.filter = new Array("All", "All", "All", "All", "All", "All", "All", "All", "All", "All");
		
		//reset all check boxes
		function resetBoxes() {
			var toggle = document.getElementById("raceform");
			for (i = 0; i < toggle.races.length; i++) {
					toggle.races[i].checked = true;
			}
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
		function powerPickf() {
			var option 	= document.getElementById("powerPick");
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
		function restrictPickf() {
			var option 	= document.getElementById("restrictPick");
			var selected;
			
			for (var i=0; i < option.options.length; i++) {
				if (option.options[i].selected)
					selected = option.options[i].value;
			}
			if (selected == "null") 
				window.filter[7] = "All";
			else
				window.filter[7] = selected;
			applyFilter();
		
		}
		
		function raceToggle() {
			var toggle = document.getElementById("raceform");
			window.filter[1] = new Array();
			for (i = 0; i < toggle.races.length; i++) {
				if (toggle.races[i].checked) {
					window.filter[1].push(toggle.races[i].value);
				}
			}
			applyFilter();
		}
		
		function applyFilter() {
			var table 	= document.getElementById("table");
			for (var i=1; i < table.rows.length; i++) {
					show = true;
					for (var j=0; j < table.rows[i].cells.length; j++) {
						if (window.filter[j] == "All")
							continue;
						if( Object.prototype.toString.call( window.filter[j] ) === '[object Array]' ) {
							if (!window.filter[j].in_array(table.rows[i].cells[j].innerHTML)) {
								show = false;
								break;
							}
						} else {
							if (table.rows[i].cells[j].innerHTML != window.filter[j]) {
								show = false;
								break;
							}
						}
					}
					if (show)
						table.rows[i].style.display="";
					else
						table.rows[i].style.display="none";
					
				}
			
		}
	
	</script>
	<?php
	echo ('</head>');
	
	echo ('<body onload="resetBoxes()">');
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
	
	//$race 		= buildSelector($db, "racePick", "race_name", "race");
	$race = "";
	$power 		= buildSelector($db, "powerPick", "power_level", "weapon_type");
	$restrict 	= buildRestriction();
	
	echo ('<div id="container" style="padding: 0;">');
	echo ('<div style="width:800px; margin-left:auto; margin-right:auto;">');
	echo (buildRaceBox($db));	
	$db->query('SELECT * FROM weapon_type, race WHERE weapon_type.race_id = race.race_id ORDER BY '.$order_by.' '.$seq);
	echo ('<table id="table" class="standard">');
	echo ('<tr>');
	echo ('<th align="center"><a href="?order=weapon_name&amp;seq='.$seq.'"><span style=color:#80C870;>Weapon Name</span></a></th>');
	echo ('<th align="center"><a href="?order=race_name&amp;seq='.$seq.'"><span style=color:#80C870;>Race</span></a>'.$race.'</th>');
	echo ('<th align="center"><a href="?order=cost&amp;seq='.$seq.'"><span style=color:#80C870;>Cost</span></a></th>');
	echo ('<th align="center"><a href="?order=shield_damage&amp;seq='.$seq.'"><span style=color:#80C870;>Shield<br>Damage</span></a></th>');
	echo ('<th align="center"><a href="?order=armour_damage&amp;seq='.$seq.'"><span style=color:#80C870;>Armour<br>Damage</span></a></th>');
	echo ('<th align="center"><a href="?order=accuracy&amp;seq='.$seq.'"><span style=color:#80C870;>Accuracy<br>%</span></a></th>');
	echo ('<th align="center"><a href="?order=power_level&amp;seq='.$seq.'"><span style=color:#80C870;>Level</span></a>'.$power.'</th>');
	echo ('<th align="center"><a href="?order=buyer_restriction&amp;seq='.$seq.'"><span style=color:#80C870;>Restriction</span></a>'.$restrict.'</th>');
	echo ('</tr>');
	while ($db->nextRecord()) {
		//we need an array so we dont have 8 td rows
	    $stat = array();
	    $stat[] = $db->getField('weapon_name');
	    $stat[] = $db->getField('race_name');
	    $stat[] = number_format($db->getInt('cost'));
	    $stat[] = $db->getInt('shield_damage');
	    $stat[] = $db->getInt('armour_damage');
	    $stat[] = $db->getInt('accuracy');
	    $stat[] = $db->getInt('power_level');
		switch($db->getInt('buyer_restriction')) {
			case 1:
		    	$restriction = '<font color="green">Good</font>';
			break;
			case 2:
				$restriction = '<font color="red">Evil</font>';
			break;
			case 3:
				$restriction = '<font color="#06F">Newbie</font>';
			break;
			default:
		    	$restriction = '-';
		}
	    $stat[] = $restriction;
	
	    echo ('<tr>');
	    foreach ($stat as $value)
		    echo ('<td align="center">'.$value.'</td>');
	
	    echo ('</tr>');
	
	}
	echo ('</table></div></div>');
	


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
	.'<option value="-">None</option>'
	."<option value='<font color=\"green\">Good</font>'>Good</option>"
	."<option value='<font color=\"red\">Evil</font>' style=\"color: red;\">Evil</option>"
	."<option value='<font color=\"#06F\">Newbie</font>' style=\"color: #06F;\">Newbie</option>"
	."</select></form>";
	
	return $restrict;

}

function buildRaceBox($db) {
	$racebox;
	$racebox = '<form id="raceform" name="raceform" align="center" style="text-align:center;">';
	$db->query("select * from race order by race_id");
	while ($db->nextRecord()) {
		$race = $db->getField("race_name");
		$racebox .= '<input type="checkbox" name="races" value="'.$race.'" onClick="raceToggle()">'.$race;
	}
	$racebox .= '</form>';
	return $racebox;
}
?>