<?php

if (!isset($var["folder_id"])) {

	print_topic("VIEW MESSAGES");

	include(get_file_loc('menue.inc'));
	print_message_menue();

	print("<p>Please choose your Message folder!</p>");

	print("<p>");
	print("<table border=\"0\" class=\"standard\" cellspacing=\"0\" cellpadding=\"3\">");
	print("<tr>");
	print("<th>Folder</th>");
	print("<th>Messages</th>");
	print("<th>&nbsp;</th>");
	print("</tr>");

	$db2 = new SmrMySqlDatabase();
	
	include(get_file_loc("council.inc"));

	$db2->query("SELECT * FROM message WHERE account_id = $player->account_id AND message_type_id = ".MSG_POLITICAL." AND game_id = $player->game_id");
	if (onCouncil($player->race_id) || $db2->nf())
		$db->query("SELECT * FROM message_type " .
				   "WHERE message_type_id < 8 " .
				   "ORDER BY message_type_id");
	else
		$db->query("SELECT * FROM message_type " .
					"WHERE message_type_id != 5 " .
				   "ORDER BY message_type_id");

	while ($db->next_record()) {

		$message_type_id = $db->f("message_type_id");
		$message_type_name = $db->f("message_type_name");

		// do we have unread msges in that folder?
		$db2->query("SELECT * FROM message " .
						"WHERE account_id = ".SmrSession::$old_account_id." AND " .
							  "game_id = ".SmrSession::$game_id." AND " .
							  "message_type_id = $message_type_id AND " .
							  "msg_read = 'FALSE'");
		$msg_read = $db2->nf();

		// get number of msges
		$db2->query("SELECT count(message_id) as message_count FROM message " .
						"WHERE account_id = ".SmrSession::$old_account_id." AND " .
							  "game_id = ".SmrSession::$game_id." AND " .
							  "message_type_id = $message_type_id");
		if ($db2->next_record())
			$message_count = $db2->f("message_count");

		print("<tr>");
		print("<td");
		if ($msg_read) print(" style=\"font-weight:bold;\"");
		print(">");
		$container = array();
		$container["url"] = "skeleton.php";
		$container["body"] = "message_view.php";
		$container["folder_id"] = $message_type_id;
		print_link($container, $message_type_name);
		print("</td>");
		print("<td align=\"center\" style=\"color:yellow;");
		if ($msg_read) print("font-weight:bold;");
		print("\">$message_count</td>");
		print("<td");
		if ($msg_read) print(" style=\"font-weight:bold;\"");
		print(">");
		$container = array();
		$container["url"] = "message_delete_processing.php";
		$container["folder_id"] = $message_type_id;
		print_link($container, "Empty");
		print("</td>");
		print("</tr>");

	}

	print("</table>");
	print("</p><p>");
	
	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "message_blacklist.php";
	$container["folder_id"] = $message_type_id;
	print_Link($container,'Manage Player Blacklist');
	
	echo '</p>';

} else {
	
	$db->query("SELECT * FROM message " .
						"WHERE account_id = ".SmrSession::$old_account_id." AND " .
							  "game_id = ".SmrSession::$game_id." AND " .
							  "message_type_id = " . $var["folder_id"] . " AND " .
							  "msg_read = 'FALSE'");
	$unread_messages = $db->nf();
	// remove entry for this folder from unread msg table
	$player->remove_message($var["folder_id"]);

	$db->query("SELECT * FROM message_type WHERE message_type_id = " . $var["folder_id"]);
	if ($db->next_record())
		print_topic("VIEW " . $db->f("message_type_name"));

	include(get_file_loc('menue.inc'));
	print_message_menue();

	if ($var["folder_id"] == MSG_GLOBAL) {

		print_form(create_container("message_global_ignore.php", ""));
		print("<div align=\"center\">Ignore global messages?&nbsp;&nbsp;");

		if ($player->ignore_global == "YES")
			print_submit_style("Yes", "background-color:green;");
		else
			print_submit("Yes");
		print("&nbsp;");
		if ($player->ignore_global == "NO")
			print_submit_style("No", "background-color:green;");
		else
			print_submit("No");
		print("</div></form>");

	}

	print("<br>");
	$container = array();
	$container["url"] = "message_delete_processing.php";
	transfer("folder_id");

	print_form($container);
	print_submit("Delete");
	print("&nbsp;");
	print("<select name=\"action\" size=\"1\" id=\"InputFields\">");
	print("<option>Marked Messages</option>");
	print("<option>All Messages</option>");
	print("</select>");
	$db->query("SELECT * FROM message " .
						"WHERE account_id = $player->account_id AND " .
							  "game_id = $player->game_id AND " .
							  "message_type_id = " . $var["folder_id"] .
						" ORDER BY send_time DESC");
	$message_count = $db->nf();

	print("<p>You have <span style=\"color:yellow;\">$message_count</span> message");
	if ($message_count != 1)
		print("s");
	print(".</p>");
	print("<table width=\"100%\" border=\"0\" class=\"standard\" cellspacing=\"0\" cellpadding=\"1\">");
	if ($var["folder_id"] == MSG_SCOUT && !isset($var['show_all'])) {
		$dispContainer = array();
		$dispContainer['url'] = 'skeleton.php';
		$dispContainer['body'] = 'message_view.php';
		$dispContainer['folder_id'] = MSG_SCOUT;
		$dispContainer['show_all'] = TRUE;
		if ($unread_messages > 25 || $message_count - $unread_messages > 25) {
			print_button($dispContainer, 'Show all Messages');
			print("<br>");
		}
		if ($unread_messages > 25) {
			//here we group new messages
			$query = 'SELECT alignment, player_id, sender_id, player_name AS sender, count( message_id ) AS number, min( send_time ) as first, max( send_time) as last 
					FROM message, player 
					WHERE player.account_id = message.sender_id 
					AND message.account_id = ' . $player->account_id . '
					AND message.game_id = ' . $player->game_id  . '
					AND player.game_id = ' . $player->game_id . '
					AND message_type_id = ' . $var["folder_id"] . '
					AND msg_read = "FALSE" 
					GROUP BY sender_id 
					ORDER BY send_time DESC';
			$db->query($query);
			while ($db->next_record()) {
				//display grouped stuff (allow for deletion)
				$playerName = get_colored_text($db->f('alignment'), stripslashes($db->f('sender')) . ' (' . $db->f('player_id') . ')');
				$message = 'Your forces have spotted ' . $playerName . ' passing your forces ' . $db->f("number") . ' times.';
				displayGrouped($playerName, $db->f("player_id"), $db->f("sender_id"), $message, $db->f("first"), $db->f("last"), TRUE);
			}
		} else {
			//not enough to group, display separatly
			$query = 'SELECT message_id, sender_id, message_text, send_time, msg_read
					FROM message
					WHERE account_id = ' . $player->account_id . '
					AND game_id = ' . $player->game_id . '
					AND message_type_id = ' . $var["folder_id"] . '
					AND msg_read = "FALSE" 
					ORDER BY send_time DESC';
			$db->query($query);
			while ($db->next_record())
				displayMessage($db->f("message_id"), $db->f("sender_id"), stripslashes($db->f("message_text")), $db->f("send_time"), $db->f("msg_read"), $var['folder_id']);
		}
		if ($message_count - $unread_messages > 25) {
			$query = 'SELECT alignment, player_id, sender_id, player_name AS sender, count( message_id ) AS number, min( send_time ) as first, max( send_time) as last 
					FROM message, player 
					WHERE player.account_id = message.sender_id 
					AND message.account_id = ' . $player->account_id  . '
					AND message.game_id = ' . $player->game_id . '
					AND player.game_id = ' . $player->game_id . '
					AND message_type_id = ' . $var["folder_id"] . '
					AND msg_read = "TRUE" 
					GROUP BY sender_id 
					ORDER BY send_time DESC';
			$db->query($query);
			while ($db->next_record()) {
				$playerName = get_colored_text($db->f('alignment'), stripslashes($db->f('sender')) . ' (' . $db->f('player_id') . ')');
				$message = 'Your forces have spotted ' . $playerName . ' passing your forces ' . $db->f("number") . ' times.';
				displayGrouped($playerName, $db->f("player_id"), $db->f("sender_id"), $message, $db->f("first"), $db->f("last"), FALSE);
			}
		} else {
			$query = 'SELECT * FROM message 
					WHERE account_id = ' . $player->account_id . ' AND
					game_id = ' . $player->game_id . '
					AND message_type_id = ' . $var["folder_id"] . '
					AND msg_read = "TRUE"
					ORDER BY send_time DESC';
			$db->query($query);
			while ($db->next_record())
				displayMessage($db->f("message_id"), $db->f("sender_id"), stripslashes($db->f("message_text")), $db->f("send_time"), $db->f("msg_read"),$var['folder_id']);
		}
		$db->query("UPDATE message SET msg_read = 'TRUE' WHERE message_type_id = ".MSG_SCOUT." AND game_id = $player->game_id AND account_id = $player->account_id");
	} else {
		while ($db->next_record()) {
			$message_id = $db->f("message_id");
			$sender_id = $db->f("sender_id");
			$message_text = stripslashes($db->f("message_text"));
			$send_time = $db->f("send_time");
			$msg_read = $db->f("msg_read");
			displayMessage($message_id, $sender_id, $message_text, $send_time, $msg_read, $var['folder_id']);
		}
		$db->query("UPDATE message SET msg_read = 'TRUE' WHERE message_type_id = $var[folder_id] AND game_id = $player->game_id AND account_id = $player->account_id");
	}
	print("</table></form>");

}
function displayGrouped($playerName, $player_id, $sender_id, $message_text, $first, $last, $star) {
	$array = array($sender_id, $first, $last);

	print("<tr><td width=\"10\"><input type=\"checkbox\" name=\"message_id[]\" value=\"" . base64_encode(serialize($array)) . "\">");
	if ($star) print("*");
	print("</td>");
	print("<td nowrap=\"nowrap\" width=\"100%\">From: ");
	$container = array();
	$container["url"]		= "skeleton.php";
	$container["body"]		= "trader_search_result.php";
	$container["player_id"] = $player_id;
	print_link($container, $playerName);
	print("</td>");
	print("<td nowrap=\"nowrap\" colspan=\"2\">Date: " . date("n/j/Y g:i:s A", $first) . " - " . date("n/j/Y g:i:s A", $last) . "</td></tr>");
	print("<tr>");
	print("<td colspan=\"4\">");
	//insert link to expand them.
	print("$message_text");
	print("</td>");
	print("</tr>");
}
function displayMessage($message_id, $sender_id, $message_text, $send_time, $msg_read, $type) {
	global $player, $account;
	$replace = explode("!", $message_text);
	foreach ($replace as $key => $timea)
	{
		if (($final = strtotime($timea)) !== false && $timea != "") //WARNING: Expects PHP 5.1.0 or later
		{
			$final += $account->offset * 3600;
			$message_text = str_replace("!$timea!", date("n/j/Y g:i:s A", $final), "$message_text");
		}
	}
	$replace = explode("?", $message_text);
	foreach ($replace as $key => $timea)
	{
		if (($final = strtotime($timea)) !== false && $sender_id > 0 && $timea != "") //WARNING: Expects PHP 5.1.0 or later
		{	
			$send_acc = new SMR_ACCOUNT();
			$send_acc->get_by_id($sender_id);
			$final += ($account->offset * 3600 - $send_acc->offset * 3600);
			$message_text = str_replace("?$timea?", date("n/j/Y g:i:s A", $final), "$message_text");
		}
	}
	if (!empty($sender_id))
		$sender = new SMR_PLAYER($sender_id, $player->game_id);
	print("<tr>");
	print("<td width=\"10\"><input type=\"checkbox\" name=\"message_id[]\" value=\"$message_id\">");
	// remember id for marking as read
	if ($msg_read == 'FALSE') print("*");
	print("</td>");
	print("<td nowrap=\"nowrap\" width=\"100%\">From: ");
	if (!empty($sender_id)) {
		$container = array();
		$container["url"]		= "skeleton.php";
		$container["body"]		= "trader_search_result.php";
		$container["player_id"] = $sender->player_id;
		print_link($container, $sender->get_colored_name());
	} else {
		if ($type == 7)
			print("<span style=\"font:small-caps bold;color:blue;\">Administrator</span>");
		elseif ($type == 6) {
			print("<span class=\"green\">");
			print("Alliance Ambassador");
			print("</span>");
		} elseif ($type == 2) print("<span class=\"yellow\">Port Defenses</span>");
		else print("Unknown");
	}
	print("</td>");
	print("<td nowrap=\"nowrap\">Date: " . date("n/j/Y g:i:s A", $send_time) . "</td>");
	print("<td>");
	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "message_notify_confirm.php";
	$container["message_id"] = $message_id;
	$container["sent_time"] = $send_time;
	$container["notified_time"] = time();
	print_link($container, "<img src=\"images/notify.gif\" border=\"0\" align=\"right\"title=\"Report this message to an admin\">");
	print("</td>");
	print("</tr>");
	print("<tr>");
	print("<td colspan=\"4\">$message_text</td>");
	print("</tr>");
}
?>