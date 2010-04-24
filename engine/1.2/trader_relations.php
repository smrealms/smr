<?php
$player->get_relations();
print_topic("TRADER RELATIONS");

include(get_file_loc('menue.inc'));
print_trader_menue();

print("<p align=\"center\">");
print("<table cellspacing=\"0\" cellpadding=\"5\" width=\"60%\" border=\"0\" class=\"standard\">");
print("<tr>");
print("<th valign=\"top\" width=\"50%\">Relations (Global)</th>");
print("<th valign=\"top\" width=\"50%\">Relations (Personal)</th>");
print("</tr>");
print("<tr>");
print("<td valign=\"top\" width=\"50%\">");

print("<p>");
$db->query("SELECT * FROM race");
while ($db->next_record()) {

	$race_id = $db->f("race_id");

	if ($race_id == 1) continue;

	$race_name = $db->f("race_name");
	print("$race_name : " . get_colored_text($player->relations_global_rev[$race_id], $player->relations_global_rev[$race_id]) . "<br>");

}
print("</p>");

print("</td>");
print("<td valign=\"top\">");

print("<p>");
$db->query("SELECT * FROM race");
while ($db->next_record()) {

	$race_id = $db->f("race_id");

	if ($race_id == 1) continue;

	$race_name = $db->f("race_name");
	print("$race_name : " . get_colored_text($player->relations[$race_id], $player->relations[$race_id]) . "<br>");

}
print("</p>");

print("</td>");
print("</tr>");
print("</table>");
print("</p>");

?>