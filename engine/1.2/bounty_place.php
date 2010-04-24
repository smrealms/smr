<?php
require_once(get_file_loc('smr_sector.inc'));
		$sector = new SMR_SECTOR($player->sector_id, SmrSession::$game_id, SmrSession::$old_account_id);

print_topic("Place a Bounty");

include(get_file_loc('menue.inc'));
if ($sector->has_hq())
	print_hq_menue();
else
	print_ug_menue();

$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "bounty_place_confirm.php";
print_form($container);

print("Select the player you want to add the bounty to<br>");
print("<select name=\"account_id\" size=\"1\" id=\"InputFields\">");
print("<option value=0>[Please Select]</option>");
$db->query("SELECT * FROM player WHERE game_id = $player->game_id ORDER BY player_name");
while($db->next_record()) {
	print("<option value=\"" . $db->f("account_id") . "\">" . stripslashes($db->f("player_name")) . "</option>");
}
print("</select>");

print("<br><br>");
print("Enter the amount you wish to place on this player<br>");
print("<input type=\"text\" name=\"amount\" maxlength=\"10\" size=\"10\" id=\"InputFields\">");

print("<br><br>");
print_submit("Place");

?>