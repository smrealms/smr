<?php
try {
	require_once('config.inc');
	
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
	<script src="js/weapon_list.js"></script>
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
