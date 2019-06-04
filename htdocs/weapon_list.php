<?php
try {
	require_once('config.inc');
	
	$template = new Template();

	$seq = isset($_REQUEST['seq']) ? $_REQUEST['seq'] : '';
	if (empty($seq))
		$seq = 'ASC';
	elseif ($seq == 'ASC')
		$seq = 'DESC';
	else
		$seq = 'ASC';
	$template->assign('seq', $seq);
	
	$columnNames = array('weapon_name','race_name','cost','shield_damage','armour_damage','accuracy','power_level','buyer_restriction');
	if (isset($_REQUEST['order'])&&in_array($_REQUEST['order'],$columnNames))
		$order_by = $_REQUEST['order'];
	else
		$order_by = 'weapon_type_id';
	
	$db = new SmrMySqlDatabase();
	$template->assign('power', buildSelector($db, "powerPick", "power_level", "weapon_type"));
	$template->assign('restrict', buildRestriction());
	$template->assign('raceBoxes', buildRaceBox($db));

	$weapons = [];
	$db->query('SELECT * FROM weapon_type JOIN race USING(race_id) ORDER BY '.$order_by.' '.$seq);
	while ($db->nextRecord()) {
		switch($db->getInt('buyer_restriction')) {
			case BUYER_RESTRICTION_GOOD:
				$restriction = '<td style="color: green;">Good</td>';
			break;
			case BUYER_RESTRICTION_EVIL:
				$restriction = '<td style="color: red;">Evil</td>';
			break;
			case BUYER_RESTRICTION_NEWBIE:
				$restriction = '<td style="color: #06F;">Newbie</td>';
			break;
			case BUYER_RESTRICTION_PORT:
				$restriction = '<td style="color: yellow;">Port</td>';
			break;
			case BUYER_RESTRICTION_PLANET:
				$restriction = '<td style="color: yellow;">Planet</td>';
			break;
			default:
				$restriction = '<td></td>';
		}
		$weapons[] = [
			'restriction' => $restriction,
			'weapon_name' => $db->getField('weapon_name'),
			'race_id' => $db->getInt('race_id'),
			'race_name' => $db->getField('race_name'),
			'cost' => number_format($db->getInt('cost')),
			'shield_damage' => $db->getInt('shield_damage'),
			'armour_damage' => $db->getInt('armour_damage'),
			'accuracy' => $db->getInt('accuracy'),
			'power_level' => $db->getInt('power_level'),
		];
	}
	$template->assign('Weapons', $weapons);

	$template->display('weapon_list.php');
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
	$racebox = '<form id="raceform" name="raceform" style="text-align:center;">';
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
