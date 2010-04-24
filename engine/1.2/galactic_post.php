<?php

print_topic("GALACTIC POST");
$db2 = new SmrMySqlDatabase();
$db->query("SELECT * FROM galactic_post_writer WHERE game_id = ".SmrSession::$game_id." AND account_id = $player->account_id");
if ($db->next_record()) {

    $position = $db->f("position");
    if ($position == "writer")
        $allowed_write = "yes";
    else
        $allowed_edit = "yes";

} else {

    $allowed_write = "no";
    $allowed_edit = "no";

}

if ($allowed_edit == "yes") {

	include(get_file_loc('menue.inc'));
    print_galactic_post_menue();
    print("<b>EDITOR OPTIONS<br></b>");
    print("Welcome $player->player_name your position is <i>Editor</i><br>");
    print_link(create_container("skeleton.php", "galactic_post_view_applications.php"), "View the applications");
    print("<br>");
    print_link(create_container("skeleton.php", "galactic_post_view_article.php"), "View the articles");
    print("<br>");
    print_link(create_container("skeleton.php", "galactic_post_make_paper.php"), "Make a paper");
    print("<br>");
    print_link(create_container("skeleton.php", "galactic_post_view_members.php"), "View Members");
    print("<br>");
    $db->query("SELECT * FROM galactic_post_paper WHERE game_id = $player->game_id");
    if ($db->nf())
        print("The following papers are already made (note papers must have 3-8 articles to go to the press)");
    while($db->next_record()) {

        $paper_name = $db->f("title");
        $paper_id = $db->f("paper_id");
        print("<font color=red>***</font><i>$paper_name</i>");
        $db2->query("SELECT * FROM galactic_post_paper_content WHERE paper_id = $paper_id AND game_id = $player->game_id");
        print(" which contains <font color=red> " . $db2->nf() . " </font>articles. ");
        if ($db2->nf() > 2 && $db2->nf() < 9) {

            $container = array();
            $container["url"] = "galactic_post_make_current.php";
            $container["id"] = $paper_id;
            print_link($container, "<b>HIT THE PRESS!</b>");

        }
        print("<br>");
        $container = array();
        $container["url"] = "skeleton.php";
        $container["body"] = "galactic_post_delete_confirm.php";
        $container["paper"] = "yes";
        $container["id"] = $paper_id;
        print_link($container, "Delete $paper_name");
        print("<br>");
        $container = array();
        $container["url"] = "skeleton.php";
        $container["body"] = "galactic_post_paper_edit.php";
        $container["id"] = $paper_id;
        print_link($container, "Edit $paper_name");
        print("<br><br>");

    }
    print("<br>");
    print("<font color=blue>If you wish to edit an article you must first view it.</font>");
    print("<br><br>");

}

?>