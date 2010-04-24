<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

print_topic("READING THE WALL");

include(get_file_loc('menue.inc'));
print_bar_menue();

$db = new SmrMySqlDatabase();
$db->query("SELECT message_id FROM bar_wall WHERE sector_id = $sector->sector_id AND game_id = ".SmrSession::$game_id." ORDER BY message_id DESC");
if ($db->next_record())
	$amount = $db->f("message_id") + 1;
else
	$amount = 1;
$time_now = time();
$db2 = new SmrMySqlDatabase();
$wall = $_REQUEST['wall'];
if (isset($wall))
	$db2->query("INSERT INTO bar_wall (sector_id, game_id, message_id, message, time) VALUES ($sector->sector_id, ".SmrSession::$game_id.", $amount,  " . format_string($wall, true) . " , $time_now)");
$db->query("SELECT * FROM bar_wall WHERE game_id = $player->game_id AND sector_id = $player->sector_id ORDER BY time DESC");
if ($db->nf()) {

	print("<table cellspacing=\"0\" cellpadding=\"3\" border=\"0\" class=\"standard\">");
	print("<tr>");
	print("<th align=\"center\">Time written</th>");
	print("<th align=\"center\">Message</th>");
	print("</tr>");

	while ($db->next_record()) {

		$time = $db->f("time");
		$message_on_wall = stripslashes($db->f("message"));

		print("<tr>");
		print("<td align=\"center\"><b> " . date("n/j/Y g:i:s A", $time) . " </b></td>");
		print("<td align=\"center\"><b>$message_on_wall</b></td>");
		print("</tr>");

	}
    print("</table>");
}
print_topic("Write on the wall");

print("<br>");

print_form(create_container("skeleton.php", "bar_read_wall.php"));
print("<textarea rows=7 cols=50 name=wall id=\"InputFieldsText\"></textarea><br><br>");
print_submit("Write it");
print("</form>");

?>