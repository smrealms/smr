<?php

print_topic("VIEWING MEMBERS");
include(get_file_loc('menue.inc'));
print_galactic_post_menue();

$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "galactic_post_view_members.php";
if ($action == "Remove")
	$db->query("DELETE FROM galactic_post_writer WHERE game_id = $player->game_id AND account_id = $var[id]");

$db->query("SELECT * FROM galactic_post_writer WHERE game_id = $player->game_id AND account_id != $player->account_id");
if ($db->nf()) {

	print_table();
	print("<tr>");
	print("<th align=\"center\">Player Name</th>");
	print("<th align=\"center\">Last Wrote</th>");
    print("<th align=\"center\">Options</th>");
	print("</tr>");

    while ($db->next_record()) {

	    $curr_writter = new SMR_PLAYER($db->f("account_id"), $player->game_id);
    	$time = $db->f("last_wrote");
        print("<tr>");
	    print("<td align=\"center\">$curr_writter->player_name</td>");
    	print("<td align=\"center\"> " . date("n/j/Y g:i:s A", $time) . "</td>");
	    $container["id"] = $curr_writter->account_id;
	    print_form($container);
        print("<td>");
    	print_submit("Remove");
        print("</td>");
        print("</tr>");
        print("</form>");

	}
    print("</table>");

}
?>