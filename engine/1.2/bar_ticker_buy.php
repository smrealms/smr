<?php

$num_creds = $account->get_credits();

if (isset($var["process"])) {
	
	if ($num_creds == 0) {
		print_error("You don't have enough SMR Credits.  Donate money to SMR to gain SMR Credits!");
		return;	
	}
	$type = $_REQUEST["type"];
	$expires = time() + (5*24*60*60);
	$time = time();
	//only scout OR news....but you can have both scout and block or news and block
	if ($type == "scout" || $type == "news") {
		$db->query("SELECT * FROM player_has_ticker WHERE game_id = $player->game_id AND account_id = $player->account_id AND (type = 'news' OR type = 'scout')");
		if ($db->next_record()) $db->query("DELETE FROM player_has_ticker WHERE game_id = $player->game_id AND account_id = $player->account_id AND (type = 'news' OR type = 'scout')");
		$db->query("INSERT INTO player_has_ticker (game_id, account_id, type, expires) VALUES ($player->game_id, $player->account_id, '$type', $expires)");
	} else $db->query("REPLACE INTO player_has_ticker (game_id, account_id, type, expires) VALUES ($player->game_id, $player->account_id, '$type', $expires)");
	//take money
	$account->set_credits($num_creds - 1);
	//offer another drink and such
	print("<div align=center>Your system has been added.  Enjoy!</div><br>");
	include(get_file_loc("bar_opening.php"));

} else {
	
	//they can buy the ticker...first we need to find out what they want
	$db->query("SELECT * FROM player_has_ticker WHERE game_id = $player->game_id AND account_id = $player->account_id");
	while ($db->next_record()) {
		$expire = $db->f("expires");
		$type = $db->f("type");
		if ($type == "news") $type = "News Ticker";
		if ($type == "scout") $type = "Scout Message Ticker";
		if ($type == "block") $type = "Scout Message Blocker";
		$left = $expire - time();
		$days = floor($left / 86400);
		$left -= $days * 86400;
		$hours = floor($left / 3600);
		$left -= $hours * 3600;
		$mins = floor($left / 60);
		$left -= $mins * 60;
		$remain = "$days Days, $hours Hours, $mins Minutes, $left Seconds";
		print("You own a $type for another $remain.<br>");
		if ($type == "News Ticker") print("Note: If you select Scout Message Ticker you will lose your Current News Ticker<br>");
		if ($type == "Scout Message Ticker") print("Note: If you select Current News Ticker you will lose your Scout Message Ticker<br>");
	}
	print("Great idea!  So what do you want us to configure your system to do?<br>");
	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "bar_main.php";
	$container["script"] = "bar_ticker_buy.php";
	$container["process"] = "yes";
	print_form($container);
	print("<input type=radio name=type value=scout>Send Scout Messages<br>");
	print("<input type=radio name=type value=news>Send Recent News<br>");
	print("<input type=radio name=type value=block>Block Scout Message Tickers<br /><small>This will only block messages to tickers, it will not completely block scout messages</small><br>");
	print_submit("Continue");
	print("</form>");
}

?>