<?php

print_topic("SEND MESSAGE");

include(get_file_loc('menue.inc'));
print_message_menue();

print("<p>");

$container = array();
$container["url"] = "message_send_processing.php";
transfer("receiver");

print_form($container);
print("<p><small><b>From:</b> $player->player_name ($player->player_id)<br>");

if (!empty($var["receiver"])) {

	$receiver = new SMR_PLAYER($var["receiver"], SmrSession::$game_id);
	print("<b>To:</b> $receiver->player_name ($receiver->player_id)</small></p>");

} else print("<b>To:</b> All Online</small></p>");

print("<textarea name=\"message\" id=\"InputFields\" style=\"width:350px;height:100px;\"></textarea><br><br>");
print_submit("Send message");
print("</form>");
print("</p>");

?>