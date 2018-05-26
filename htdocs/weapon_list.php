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
		color: #80C870; }
	optgroup {
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
			filterSelect("racePick", 1);	
		}
		function powerPickf() {
			filterSelect("powerPick", 6);	
		}
		function restrictPickf() {0
			filterSelect("restrictPick", 7);			
		}
		function filterSelect(selectId, filterId) {
			var option 	= document.getElementById(selectId);
			var selected = option.options[option.selectedIndex].value;
			
			window.filter[filterId] = selected;
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
				var show = true;
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
	echo ('<div id="main" style="width:810px; margin-left:auto; margin-right:auto;">');
	echo (buildRaceBox($db));	
	$db->query('SELECT * FROM weapon_type JOIN race USING(race_id) ORDER BY '.$order_by.' '.$seq);
	echo ('<table id="table" class="standard">');
	echo ('<tr>');
	echo ('<th align="center" style="width: 240px;"><a href="?order=weapon_name&amp;seq='.$seq.'"><span style=color:#80C870;>Weapon Name</span></a></th>');
	echo ('<th align="center" style="width: 90px;"><a href="?order=race_name&amp;seq='.$seq.'"><span style=color:#80C870;>Race</span></a>'.$race.'</th>');
	echo ('<th align="center" style="width: 64px;"><a href="?order=cost&amp;seq='.$seq.'"><span style=color:#80C870;>Cost</span></a></th>');
	echo ('<th align="center" style="width: 74px;"><a href="?order=shield_damage&amp;seq='.$seq.'"><span style=color:#80C870;>Shield<br>Damage</span></a></th>');
	echo ('<th align="center" style="width: 74px;"><a href="?order=armour_damage&amp;seq='.$seq.'"><span style=color:#80C870;>Armour<br>Damage</span></a></th>');
	echo ('<th align="center" style="width: 85px;"><a href="?order=accuracy&amp;seq='.$seq.'"><span style=color:#80C870;>Accuracy<br>%</span></a></th>');
	echo ('<th align="center" style="width: 51px;"><a href="?order=power_level&amp;seq='.$seq.'"><span style=color:#80C870;>Level</span></a>'.$power.'</th>');
	echo ('<th align="center" style="width: 92px;"><a href="?order=buyer_restriction&amp;seq='.$seq.'"><span style=color:#80C870;>Restriction</span></a>'.$restrict.'</th>');
	echo ('</tr>');
	while ($db->nextRecord()) {
		echo ('<tr>');
		echo ('<td align="center">'.$db->getField('weapon_name').'</td>');
		echo ('<td align="center" class="race'.$db->getInt('race_id').'">'.$db->getField('race_name').'</td>');
		echo ('<td align="center">'.number_format($db->getInt('cost')).'</td>');
		echo ('<td align="center">'.$db->getInt('shield_damage').'</td>');
		echo ('<td align="center">'.$db->getInt('armour_damage').'</td>');
		echo ('<td align="center">'.$db->getInt('accuracy').'</td>');
		echo ('<td align="center">'.$db->getInt('power_level').'</td>');
		switch($db->getInt('buyer_restriction')) {
			case BUYER_RESTRICTION_GOOD:
				echo ('<td align="center" style="color: green;">Good</td>');
			break;
			case BUYER_RESTRICTION_EVIL:
				echo ('<td align="center" style="color: red;">Evil</td>');
			break;
			case BUYER_RESTRICTION_NEWBIE:
				echo ('<td align="center" style="color: #06F;">Newbie</td>');
			break;
			case BUYER_RESTRICTION_PORT:
				echo ('<td align="center" style="color: yellow;">Port</td>');
			break;
			case BUYER_RESTRICTION_PLANET:
				echo ('<td align="center" style="color: yellow;">Planet</td>');
			break;
			default:
				echo ('<td align="center">-</td>');
		}
		echo ('</tr>');
	}
	echo ('</table></div></div>');
	


}
catch(Throwable $e) {
	handleException($e);
}

function buildSelector($db, $id, $name, $table) {
	$selector = '<br><select id="'.$id.'" name="'.$name.'" onchange="'.$id.'f()"><option value="All">All</option>';
	$db->query("SELECT DISTINCT ".$name." FROM ".$table." ORDER BY ".$name);
	while ($db->nextRecord()) {
		$selector .= '<option value="'.$db->getField($name).'">'
		.$db->getField($name).'</option>';
	}
	$selector .= '</select>';
	return $selector;
}

function buildRestriction() {
	$restrict = '<br><select id="restrictPick" name="restrict" onchange="restrictPickf()">'
	.'<option value="All">All</option>'
	.'<option value="-">None</option>'
	."<option value='Good'>Good</option>"
	."<option value='Evil' style=\"color: red;\">Evil</option>"
	."<option value='Newbie' style=\"color: #06F;\">Newbie</option>"
	."</select>";
	
	return $restrict;

}

function buildRaceBox($db) {
	$racebox = '<form id="raceform" name="raceform" align="center" style="text-align:center;">';
	$db->query('SELECT race_id, race_name FROM race ORDER BY race_name');
	while ($db->nextRecord()) {
		$raceID = $db->getInt('race_id');
		$raceName = $db->getField('race_name');
		$racebox .= '
			<input type="checkbox" id="race'.$raceID.'" name="races" value="'.$raceName.'" onClick="raceToggle()">
			<label for="race'.$raceID.'" class="race'.$raceID.'">'.$raceName.'</label>';
	}
	$racebox .= '</form>';
	return $racebox;
}
?>
