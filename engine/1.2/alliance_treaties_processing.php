<?php

//get the alliances
if (isset($var['alliance_id'])) $alliance_id_1 = $var['alliance_id'];
else $alliance_id_1 = $player->alliance_id;
if ($alliance_id_1 == 0) create_error("You are not in an alliance!");
if (isset($var['accept'])) {
	if ($var['accept']) {
		$db->query("UPDATE alliance_treaties SET official = 'TRUE' WHERE alliance_id_1 = " . $var['alliance_id_1'] . " AND alliance_id_2 = $alliance_id_1 AND game_id = $player->game_id");
		if ($var['aa']) {
			//make an AA entry to the alliance, use treaty_created column
			// get last id
			$db->query("SELECT MAX(role_id)
						FROM alliance_has_roles
						WHERE game_id = $player->game_id AND
							  alliance_id = $alliance_id_1");
			if ($db->next_record())
				$role_id = $db->f("MAX(role_id)") + 1;
			else
				$role_id = 1;
			$allianceName = $var['alliance_name'];
			$db->query("INSERT INTO alliance_has_roles
				(alliance_id, game_id, role_id, role, treaty_created)
				VALUES ($alliance_id_1, $player->game_id, $role_id, '" . addslashes($allianceName) . "',1)");
			$db->query("SELECT MAX(role_id)
						FROM alliance_has_roles
						WHERE game_id = $player->game_id AND
							  alliance_id = " . $var['alliance_id_1']);
			if ($db->next_record())
				$role_id = $db->f("MAX(role_id)") + 1;
			else
				$role_id = 1;
			$allianceName = $player->alliance_name;
			$db->query("INSERT INTO alliance_has_roles
				(alliance_id, game_id, role_id, role, treaty_created)
				VALUES (" . $var['alliance_id_1'] . ", $player->game_id, $role_id, '" . addslashes($allianceName) . "',1)");
		}
	}
	else $db->query("DELETE FROM alliance_treaties WHERE alliance_id_1 = " . $var['alliance_id_1'] . " AND alliance_id_2 = $alliance_id_1 AND game_id = $player->game_id");
	$container = create_container('skeleton.php','alliance_treaties.php');
	$container['alliance_id'] = $alliance_id_1;
	forward($container);
}
if (isset($_REQUEST['proposedAlliance'])) {
	
	$alliance_id_2 = $_REQUEST['proposedAlliance'];
	$db->query("SELECT alliance_id_1, alliance_id_2, game_id FROM alliance_treaties WHERE (alliance_id_1 = $alliance_id_1 OR alliance_id_1 = $alliance_id_2) AND (alliance_id_2 = $alliance_id_1 OR alliance_id_2 = $alliance_id_2) AND game_id = $player->game_id");
	if ($db->next_record()) {
		$container=array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'alliance_treaties.php';
		$container['alliance_id'] = $alliance_id_1;
		$container['message'] = '<span class="red bold">ERROR:</span> There is already an outstanding treaty with that alliance.';
		forward($container);
	}
	//get the terms, assume false at first
	$traderAssist = 0;
	$raidAssist = 0;
	$traderDefend = 0;
	$traderNAP = 0;
	$planetNAP = 0;
	$forcesNAP = 0;
	$aaAccess = 0;
	$mbRead = 0;
	$mbWrite = 0;
	$modRead = 0;
	$planetLand = 0;
	if (isset($_REQUEST['assistTrader'])) $traderAssist = 1;
	if (isset($_REQUEST['assistRaids'])) $raidAssist = 1;
	if (isset($_REQUEST['defendTrader'])) $traderDefend = 1;
	if (isset($_REQUEST['napTrader'])) $traderNAP = 1;
	if (isset($_REQUEST['napPlanets'])) $planetNAP = 1;
	if (isset($_REQUEST['napForces'])) $forcesNAP = 1;
	if (isset($_REQUEST['aaAccess'])) $aaAccess = 1;
	if (isset($_REQUEST['mbRead'])) $mbRead = 1;
	if (isset($_REQUEST['mbWrite'])) $mbWrite = 1;
	if (isset($_REQUEST['modRead'])) $modRead = 1;
	if (isset($_REQUEST['planetLand'])) $planetLand = 1;
	//make sure its all logical.
	if ($traderAssist) $traderNAP = 1;
	if ($traderDefend) $traderNAP = 1;
	if ($planetLand) $planetNAP = 1;
	if ($mbWrite) $mbRead = 1;
	//get confirmation
	$db->query('SELECT leader_id, alliance_name, alliance_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id_1 . ' LIMIT 1');
	$db->next_record();
	$leader_id = $db->f("leader_id");
	print_topic(stripslashes($db->f("alliance_name")) . ' (' . $db->f("alliance_id") . ')');
	include(get_file_loc('menue.inc'));
	print_alliance_menue($alliance_id_1,$db->f('leader_id'));
	print("<br /><br /");
	print("<div align=\"center\">Are you sure you want to offer a treaty to <span class=\"yellow\">");
	$db->query('SELECT leader_id, alliance_name, alliance_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id_2 . ' LIMIT 1');
	$db->next_record();
	print(stripslashes($db->f("alliance_name")));
	print("</span> with the following conditions:<br /><ul>");
	if ($traderAssist) print("<li>Assist - Trader Attacks</li>");
	if ($traderDefend) print("<li>Defend - Trader Attacks</li>");
	if ($traderNAP) print("<li>Non Aggression - Traders</li>");
	if ($raidAssist) print("<li>Assist - Planet & Port Attacks</li>");
	if ($planetNAP) print("<li>Non Aggression - Planets</li>");
	if ($forcesNAP) print("<li>Non Aggression - Forces</li>");
	if ($aaAccess) print("<li>Alliance Account Access</li>");
	if ($mbRead) print("<li>Message Board Read Rights</li>");
	if ($mbWrite) print("<li>Message Board Write Rights</li>");
	if ($modRead) print("<li>Message of the Day Read Rights</li>");
	if ($planetLand) print("<li>Planet Landing Rights</li>");
	print("</ul>");
	
	//give them options
	$container=array();
	$container['url'] = 'alliance_treaties_processing.php';
	$container['alliance_id'] = $alliance_id_1;
	$container['proposedAlliance'] = $alliance_id_2;
	$container['traderAssist'] = $traderAssist;
	$container['raidAssist'] = $raidAssist;
	$container['traderDefend'] = $traderDefend;
	$container['traderNAP'] = $traderNAP;
	$container['planetNAP'] = $planetNAP;
	$container['forcesNAP'] = $forcesNAP;
	$container['aaAccess'] = $aaAccess;
	$container['mbRead'] = $mbRead;
	$container['mbWrite'] = $mbWrite;
	$container['modRead'] = $modRead;
	$container['planetLand'] = $planetLand;
	print_button($container,'Yes');
	print("&nbsp;");
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'alliance_treaties.php';
	$container['alliance_id'] = $alliance_id_1;
	print_button($container,'No');
	print("</div>");
	
} else {
	define('TIME', time());
	define('MESSAGE_EXPIRES', TIME + 259200);
	$alliance_id_2 = $var['proposedAlliance'];
	$db->query("INSERT INTO alliance_treaties (alliance_id_1,alliance_id_2,game_id,trader_assist,trader_defend,trader_nap,raid_assist,planet_land,planet_nap,forces_nap,aa_access,mb_read,mb_write,mod_read,official) 
				VALUES ($alliance_id_1, $alliance_id_2, $player->game_id, " . $var['traderAssist'] . ", " . 
				$var['traderDefend'] . ", " . $var['traderNAP'] . ", " . $var['raidAssist'] . ", " . $var['planetLand'] . ", " . $var['planetNAP'] . ", " .
				$var['forcesNAP'] . ", " . $var['aaAccess'] . ", " . $var['mbRead'] . ", " . $var['mbWrite'] . ", " . $var['modRead'] . ", 'FALSE')");
	//send a message to the leader letting them know the offer is waiting.
	$db->query('SELECT alliance_name FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id_1 . ' LIMIT 1');
	$db->next_record();
	$alliance_name = stripslashes($db->f("alliance_name"));
	$db->query('SELECT leader_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id_2 . ' LIMIT 1');
	$db->next_record();
	$leader_2 = $db->f("leader_id");
	$message = 'An ambassador from <span class="yellow">' . $alliance_name . '</span> has arrived.';
	$msg = '(' . SmrSession::$game_id . ',' . $leader_2 . ',6,"' . $db->escape_string($message) . '",0,' . TIME . ',"FALSE",' . MESSAGE_EXPIRES . ')';
	$db->query("INSERT INTO message (game_id, account_id, message_type_id, message_text, sender_id, send_time, msg_read, expire_time) VALUES $msg");
	$db->query("INSERT INTO player_has_unread_messages (account_id, game_id, message_type_id) VALUES ($leader_2, ".SmrSession::$game_id.", 6)");
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'alliance_treaties.php';
	$container['alliance_id'] = $alliance_id_1;
	$container['message'] = 'The treaty offer has been sent.';
	forward($container);
}
