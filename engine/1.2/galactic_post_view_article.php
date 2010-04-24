<?php
print_topic("VIEWING ARTICLES");
include(get_file_loc('menue.inc'));
print_galactic_post_menue();
$db2 = new SmrMySqlDatabase();
$db3 = new SmrMySqlDatabase();
if (isset($var["news"])) {

$db3->query("INSERT INTO news " .
                     "(game_id, time, news_message, type) " .
                     "VALUES($player->game_id, " . time() . ", " . format_string($var["news"], false) . ", 'breaking')");

}
$db->query("SELECT * FROM galactic_post_article WHERE game_id = $player->game_id");
if ($db->nf()) {

    print("It is your responsibility to make sure ALL HTML tages are closed!<br>");
    print("You have the following articles to view.<br><br>");

}
else
    print("There are no articles to view");
while ($db->next_record()) {

    $db2->query("SELECT * FROM galactic_post_paper_content WHERE game_id = $player->game_id AND article_id = " . $db->f("article_id"));
    if (!$db2->next_record()) {

        $title = stripslashes($db->f("title"));
        $writter = new SMR_PLAYER($db->f("writer_id"), $player->game_id);
        $container = array();
        $container["url"] = "skeleton.php";
        $container["body"] = "galactic_post_view_article.php";
        $container["id"] = $db->f("article_id");
        print_link($container, "<font color=yellow>$title</font> written by $writter->player_name");
        print("<br>");

    }

}
print("<br><br>");
if (isset($var["id"])) {

    $db->query("SELECT * FROM galactic_post_article WHERE game_id = $player->game_id AND article_id = $var[id]");
    $db->next_record();
    $title = stripslashes($db->f("title"));
    $message = stripslashes($db->f("text"));
    print("$title");
    print("<br><br>$message<br>");
    print("<br>");
    $container = array();
    $container["url"] = "skeleton.php";
    $container["body"] = "galactic_post_edit_article.php";
    transfer("id");
    print_link($container, "<b>Edit this article</b>");
    print("<br>");
    $container = array();
    $container["url"] = "skeleton.php";
    $container["body"] = "galactic_post_delete_confirm.php";
    $container["article"] = "yes";
    transfer("id");
    print_link($container, "<b>Delete This article</b>");
    print("<br><br>");
    $db->query("SELECT * FROM galactic_post_paper WHERE game_id = ".SmrSession::$game_id);
    $container = array();
    $container["url"] = "galactic_post_add_article_to_paper.php";
    transfer("id");
    if (!$db->nf()) {

        print("You have no papers made that you can add an article to.");
        print_link(create_container("skeleton.php", "galactic_post_make_paper.php"), "<b>Click Here</b>");
        print("To make a new one.");

    }
    while ($db->next_record()) {

        $paper_title = $db->f("title");
        $paper_id = $db->f("paper_id");
        $container["paper_id"] = $paper_id;
        print_link($container, "<b>Add this article to $paper_title!</b>");
        print("<br>");

    }
    $container = array();
    $container["url"] = "skeleton.php";
    $container["body"] = "galactic_post_view_article.php";
    $container["news"] = $message;
    transfer("id");
    print("<small><br>note: breaking news is in the news section.<br></small>");
    print_link($container, "Add to Breaking News");

}

?>