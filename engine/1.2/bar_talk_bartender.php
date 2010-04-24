<?php

include(get_file_loc('menue.inc'));
print_bar_menue();

$db->query("SELECT message_id FROM bar_tender WHERE game_id = ".SmrSession::$game_id." ORDER BY message_id DESC");
if ($db->next_record())
	$amount = $db->f("message_id") + 1;
else
	$amount = 1;
$gossip_tell = $_REQUEST['gossip_tell'];
if (isset($gossip_tell))
	$db->query("INSERT INTO bar_tender (game_id, message_id, message) VALUES (".SmrSession::$game_id.", $amount,  " . format_string($gossip_tell, true) . " )");

$db->query("SELECT * FROM bar_tender WHERE game_id = ".SmrSession::$game_id." ORDER BY rand() LIMIT 1");

if ($db->next_record()) {

	print("I heard ");
	$message = stripslashes($db->f("message"));
	print("$message<br><br>");
	print("Got anything else to tell me?<br>");

} else
	print("I havent heard anything recently...got anything to tell me?<br><br>");


print_form(create_container("skeleton.php", "bar_talk_bartender.php"));
print("<input type=\"text\" name=\"gossip_tell\" size=\"30\" id=\"InputFields\">");
print_submit("Tell him");
print("</form><br>");

print("What else can I do for ya?");
print("<br><br>");

print_form(create_container("skeleton.php", "bar_buy_drink.php"));
print_submit("Buy a drink ($10)");
print("<br>");
print_submit("Buy some water ($10)");
print("</form><br>");

print_form(create_container("skeleton.php", "bar_talk_bartender.php"));
print_submit("Talk to bartender");
print("</form>");

?>