<?php

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $player->alliance_id . ' LIMIT 1');
$db->next_record();
print_topic($player->alliance_name . ' (' . $player->alliance_id . ')');
include(get_file_loc('menue.inc'));
print_alliance_menue($player->alliance_id,$db->f('leader_id'));

echo '<h2>Exemption Requests</h2><br />';
print("Selecting a box will authorize it, leaving a box unselected will make it unauthorized after you submit.<br />");
//get rid of already approved entries
$db->f("UPDATE alliance_bank_transactions SET request_exempt = 0 WHERE exempt = 1");
//build player array
$db->query("SELECT * FROM player WHERE alliance_id = $player->alliance_id AND game_id = $player->game_id");
while ($db->next_record()) $players[$db->f("account_id")] = stripslashes($db->f("player_name"));
$db->query("SELECT * FROM alliance_bank_transactions WHERE request_exempt = 1 " . 
			"AND alliance_id = $player->alliance_id AND game_id = $player->game_id AND exempt = 0");
if ($db->nf()) {
	$container=array();
	$container['url'] = 'bank_alliance_exempt_processing.php';
	$container['body'] = '';
	$form = create_form($container,'Make Exempt');
	echo $form['form'];
	print_table();
	print("<tr><th>Player Name</th><th>Type</th><th>Reason</th><th>Amount</th><th>Approve</th></tr>");
	while ($db->next_record()) {
		if ($db->f("transaction") == "Payment") $trans = "Withdraw";
		else $trans = "Deposit";
		print("<tr><td>" . $players[$db->f("payee_id")] . "</td><td>" . $trans . "</td><td>" . $db->f("reason") . "</td><td>" . $db->f("amount") . "</td>");
		print("<td><input type=\"checkbox\" name=\"exempt[" . $db->f("transaction_id") . "]\"></td>");
		print("</tr>");
		$temp[$db->f("payee_id")] = array($db->f("reason"), $db->f("amount"));
	}
	print("</table>");
	print("<div align=\"center\">");
	echo $form['submit'];
	print("</div></form>");
} else print("<div align=\"center\">Nothing to authorize.</div>");
?>