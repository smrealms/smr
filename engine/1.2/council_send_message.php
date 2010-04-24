<?php

$db->query("SELECT * FROM race WHERE race_id = " . $var["race_id"]);
if ($db->next_record())
	$race_name = $db->f("race_name");

print_topic("Send message to ruling council of the $race_name");

include(get_file_loc('menue.inc'));
print_message_menue();

print("<p>");

$container = array();
$container["url"] = "council_send_message_processing.php";
transfer("race_id");

print_form($container);
print("<p><small><b>From:</b> $player->player_name ($player->player_id)<br>");

print("<b>To:</b> Ruling Council of $race_name</small></p>");

print("<textarea name=\"message\" id=\"InputFields\" style=\"width:350px;height:100px;\"></textarea><br><br>");
print_submit("Send message");
print("</form>");
print("</p>");

?>