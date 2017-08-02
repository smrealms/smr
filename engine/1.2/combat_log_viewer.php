<?php

print_topic('Combat Logs');
include(get_file_loc('menue.inc'));
print_combat_log_menue();
if (isset($_REQUEST['action'])) {
	$submitAction = $_REQUEST['action'];
	if ($submitAction == 'Save' && isset($_POST['id'])) {
		//save the logs we checked
		$log_ids = array_keys($_POST['id']);
		$db->query('SELECT * FROM combat_logs WHERE log_id IN (' . implode(',', $log_ids) . ') LIMIT ' . count($log_ids));
		$unsavedLogs = array();
		$savedLogs = array();
		while ($db->next_record()) {
			if (!$db->f("saved"))
				$unsavedLogs[] = $db->f("log_id");
			else
				$savedLogs[] = array($db->f("game_id"),$db->f("type"),$db->f("sector_id"),$db->f("timestamp"),$db->f("attacker_id"),$db->f("attacker_alliance_id"),$db->f("defender_id"),$db->f("defender_alliance_id"),$db->f("result"));
		}
		if (sizeof($unsavedLogs))
			$db->query('UPDATE combat_logs SET saved = ' . $player->account_id . ' WHERE log_id IN (' . implode(',', $unsavedLogs) . ') LIMIT ' . count($log_ids));
		if (sizeof($savedLogs)) {
			foreach ($savedLogs as $a) {
				if (!empty($query)) $query .= ",";
				$query .= "($a[0],'$a[1]',$a[2],$a[3],$a[4],$a[5],$a[6],$a[7],'" . $db->escape_string($a[8]) . "'," . $player->account_id . ")";
			}
			$db->query('INSERT INTO combat_logs 
						(game_id,type,sector_id,timestamp,attacker_id,attacker_alliance_id,defender_id,defender_alliance_id,result,saved)
						VALUES ' . $query);
		}
		print("<div align=\"center\">" . count($log_ids) . " logs have been saved.<br>");
		//back to viewing
		$var['action'] = $var['old_action'];
	} elseif (!isset($_POST['id'])) $var['action'] = $var['old_action'];
}
if(!isset($var['action'])) $action = 0;
else $action = $var['action'];

if($action == 5) {

	if(!isset($_POST['id']) && !isset($var['log_ids'])) {
		$action = $var['old_action'];
	}
	else {
		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "combat_log_viewer.php";
		if(!isset($var['log_ids'])) {
			$container['log_ids'] = array_keys($_POST['id']);
			sort($container['log_ids']);
			$container['current_log'] = 0;
		}
		else {
			$container['log_ids'] = $var['log_ids'];
			$container['current_log'] = $var['current_log'];
		}	
		
		$container['action'] = 5;
		
		if($var['direction']) {
			if($var['direction'] == 1) {
				--$container['current_log'];
			}
			else {
				++$container['current_log'];			
			}
		}
		$display_id = $container['log_ids'][$container['current_log']];
		if(count($container['log_ids']) > 1) {
			echo '<div class="center">';
			if($container['current_log']) {
				$container['direction'] = 1;
				print_link($container, '<img src="images/album/rew.jpg" alt="Previous" title="Previous">');
			}
			echo '&nbsp;&nbsp;&nbsp;';
			if($container['current_log'] < count($container['log_ids']) - 1) {
				$container['direction'] = 2;
				print_link($container, '<img src="images/album/fwd.jpg" alt="Next" title="Next">');
			}
			echo '</div>';
		}
	}
}

if(isset($display_id)){
	$db->query('SELECT timestamp,sector_id,result FROM combat_logs WHERE log_id=' . $display_id . ' LIMIT 1');

	if($db->next_record()) {
		echo 'Sector ' . $db->f('sector_id') . '<br />';
		echo date('n/j/Y&\n\b\s\p;g:i:s&\n\b\s\p;&\n\b\s\p;A',$db->f('timestamp'));
		echo '<br><br>';
		echo gzuncompress($db->f('result'));
	}
	else {
		echo '<span class="red bold">Error:</span> log not found';
	}
}

switch($action){
	case(0):
		$db->query('SELECT attacker_id,defender_id,timestamp,sector_id,log_id FROM combat_logs WHERE type="PLAYER" AND game_id=' . SmrSession::$game_id . ' AND (attacker_id=' . $player->account_id . ' OR defender_id=' . $player->account_id . ') ORDER BY log_id DESC,sector_id');
		break;
	case(1):
		$query = 'FROM combat_logs WHERE type="PLAYER" AND game_id=' . SmrSession::$game_id;
		if($player->alliance_id != 0) {
			$query .= ' AND (attacker_alliance_id=' . $player->alliance_id . ' OR defender_alliance_id=' . $player->alliance_id . ') ';
		}
		else {
			$query .= ' AND (attacker_id=' . $player->account_id . ' OR defender_id=' . $player->account_id . ') ';
		}
		$db->query('SELECT attacker_id,defender_id,timestamp,sector_id,log_id ' . $query . ' ORDER BY log_id DESC, sector_id');
		break;
	case(2):
		$query = 'FROM combat_logs WHERE type="PORT" AND game_id=' . SmrSession::$game_id;
		if($player->alliance_id != 0) {
			$query .= ' AND (attacker_alliance_id=' . $player->alliance_id . ' OR defender_alliance_id=' . $player->alliance_id . ') ';
		}
		else {
			$query .= ' AND (attacker_id=' . $player->account_id . ' OR defender_id=' . $player->account_id . ') ';
		}
		$db->query('SELECT attacker_id,defender_id,timestamp,sector_id,log_id ' . $query . ' ORDER BY log_id DESC, sector_id');
		break;
	case(3):
		$query = 'FROM combat_logs WHERE type="PLANET" AND game_id=' . SmrSession::$game_id;
		if($player->alliance_id != 0) {
			$query .= ' AND (attacker_alliance_id=' . $player->alliance_id . ' OR defender_alliance_id=' . $player->alliance_id . ') ';
		}
		else {
			$query .= ' AND (attacker_id=' . $player->account_id . ' OR defender_id=' . $player->account_id . ') ';
		}
		$db->query('SELECT attacker_id,defender_id,timestamp,sector_id,log_id ' . $query . ' ORDER BY log_id DESC, sector_id');
		break;
	case(4):
		$query = 'FROM combat_logs WHERE saved = ' . $player->account_id . ' AND game_id = ' . $player->game_id;
		$db->query('SELECT attacker_id,defender_id,timestamp,sector_id,log_id ' . $query . ' ORDER BY timestamp DESC, sector_id');
		break;
	default:

}

if($action != 5) {
	echo '<div align="center">';
	if($db->nf() > 0) {
		$num = $db->nf();
		echo 'There ';
		if ($num > 1) echo 'are ';
		else echo 'is ';
		echo $num;
		switch($action){
			case(0):
				echo ' personal';
				break;
			case(1):
				echo ' alliance';
				break;
			case(2):
				echo ' port';
				break;
			case(3):
				echo ' planet';
				break;
			case(4):
				echo ' saved';
				break;
		}
		echo ' log';
		if ($num > 1) echo 's';
		echo ' available for viewing.<br /><br />';
		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "combat_log_viewer.php";
		$container["action"] = 5;
		$container["old_action"] = $action;
		$container['direction'] = 0;
		$actions = array();
		$actions[] = array('View','View');
		$actions[] = array('Save','Save');
		$form = create_form($container,$actions);
		echo $form['form'];
		echo $form['submit']['View'];
		echo '&nbsp';
		echo $form['submit']['Save'];
		echo '<br><br><table cellspacing="0" cellpadding="5" class="standard fullwidth">';
		echo '<tr><th>View</th><th>Date</th><th>Sector</th><th>Attacker</th><th>Defender</th></tr>';
		while($db->next_record()) {
			//attacker_id,defender_id,timestamp,sector_id,log_id
			$logs[$db->f('log_id')] = array($db->f("attacker_id"),$db->f("defender_id"),$db->f("timestamp"),$db->f("sector_id"));
			$player_ids[] = $db->f("attacker_id");
			$player_ids[] = $db->f("defender_id");
		}
		array_unique($player_ids);
		$db->query("SELECT player_name, account_id FROM player
					WHERE account_id IN (" . implode(',',$player_ids) . ")
					AND game_id = ".SmrSession::$game_id."
					LIMIT " . sizeof($player_ids));
		while ($db->next_record()) $players[$db->f("account_id")] = stripslashes($db->f('player_name'));
		foreach ($logs as $id => $info) {
			$container["id"] = $id;
			echo '<tr>';
			echo '<td class="center shrink">';
			echo '<input type="checkbox" value="on" name="id[' . $id . ']">';
			echo '</td>';
			if(isset($players[$info[0]]))
				$attacker_name = $players[$info[0]];
			else
				$attacker_name = "Unknown Attacker";
			if(isset($players[$info[1]]))
				$defender_name = $players[$info[1]];
			else
				$defender_name = "Unknown Defender";
			echo '<td class="shrink nowrap">' . date('n/j/Y&\n\b\s\p;g:i:s&\n\b\s\p;&\n\b\s\p;A',$info[2]) . '</td>';
			echo '<td class="center shrink">' . $info[3] . '</td>';
			echo '<td>' . $attacker_name . '</td>';
			echo '<td>' . $defender_name . '</td>';
		}
		echo '</table>';
		echo '</form>';
	}
	else {
		echo "No combat logs found";
	}
	echo '</div>';
}

?>
