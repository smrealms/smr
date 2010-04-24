<?php
$db2 = new SmrMySqlDatabase();
$amount = $_REQUEST['amount'];
$account_id = $_REQUEST['account_id'];
if (!is_numeric($amount)) {

	print_error("Numbers only please");
	return;

}
$amount = round($amount);
if ($amount <= 0) {

	print_error("You can only tranfer a positive amount");
	return;

}

if ($amount > $account->get_credits()) {

	print_error("You can't transfer more than you have!");
	return;

}

print_topic("Confirmation");

print("Are you sure you want to transfer $amount credits to<br>");

$db->query("SELECT * FROM account WHERE account_id = $account_id");
if ($db->next_record())
	$login = $db->f("login");

$db->query("SELECT * FROM player WHERE account_id = $account_id");
if ($db->nf()) {

	while ($db->next_record()) {

	    $player_name = stripslashes($db->f("player_name"));
    	$game_id = $db->f("game_id");

	    $db2->query("SELECT * FROM game WHERE game_id = $game_id");
    	if ($db2->next_record())
			$game_name = $db2->f("game_name");

		print("$player_name in game $game_name($game_id)<br>");

	}

} else
	print("Player with login name $login?<br>");

print("<p>&nbsp;</p>");

$container = array();
$container["url"] = "preferences_processing.php";
$container["account_id"] = $account_id;
$container["amount"] = $amount;
print_form($container);

print_submit("Yes");
print("&nbsp;&nbsp;");
print_submit("No");
print("</form>");

?>