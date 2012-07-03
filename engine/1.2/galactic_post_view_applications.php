<?php

print_topic("VIEWING APPLICATIONS");
include(get_file_loc('menue.inc'));
print_galactic_post_menue();
$db->query("SELECT * FROM galactic_post_applications WHERE game_id = $player->game_id");
if ($db->nf()) {

    print("You have received an application from the following players (click name to view description)<br>");
    print("Becareful when choosing your writters.  Make sure it is someone who will actually help you.<br><br>");

} else
    print("You have no applications to view at the current time.");
while ($db->next_record()) {

    $appliee = new SMR_PLAYER($db->f("account_id"), $player->game_id);

    $container = array();
    $container["url"] = "skeleton.php";
    $container["body"] = "galactic_post_view_applications.php";
    $container["id"] = $appliee->account_id;
    print_link($container, "<font color=yellow>$appliee->player_name</font>");
    print(" who has ");
    if ($db->f("written_before") == "YES")
        print("written for some kind of a newspaper before.");
    else
        print("not written for a newspaper before.");
    print("<br>");

}
print("<br><br>");
if (isset($var["id"])) {

    $db->query("SELECT * FROM galactic_post_applications WHERE game_id = $player->game_id AND account_id = $var[id]");
    $db->next_record();
    $desc = stripslashes($db->f("description"));
    $applie = new SMR_PLAYER($var["id"], $player->game_id);
    print("Name : $applie->player_name<br>");
    print("Have you written for some kind of newspaper before? " . $db->f("written_before"));
    print("<br>");
    print("How many articles are you willing to write per day? " . $db->f("articles_per_day"));
    print("<br>");
    print("What do you want to tell the editor?<br><br>$desc");
    $container = array();
    $container["url"] = "galactic_post_application_answer.php";
    transfer("id");
    print_form($container);
    print("<br><br>");
    print_submit("Accept");
    print_submit("Reject");
    print("</form>");

}
?>