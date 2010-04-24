<?php

$db2 = new SmrMySqlDatabase();
$db3 = new SmrMySqlDatabase();
if (isset($var["article"])) {

    $db->query("SELECT * FROM galactic_post_article WHERE article_id = $var[id] AND game_id = $player->game_id");
    $db->next_record();
    $title = $db->f("title");
    print("Are you sure you want to delete the article named $title?");
    $container = array();
    $container["url"] = "galactic_post_delete.php";
    transfer("article");
    transfer("id");
    print_form($container);
    print_submit("Yes");
    print("</form>");
    $container = array();
    $container["url"] = "skeleton.php";
    $container["body"] = "galactic_post_view_article.php";
    transfer("id");
    print_form($container);
    print_submit("No");
    print("</form>");

} else {

    $db->query("SELECT * FROM galactic_post_paper WHERE game_id = $player->game_id AND paper_id = $var[id]");
    $db->next_record();
    $title = $db->f("title");
    print("Are you sure you want to delete the paper titled $title and the following articles with it<br><br>");
    $db2->query("SELECT * FROM galactic_post_paper_content WHERE game_id = $player->game_id AND paper_id = $var[id]");
    while($db2->next_record()) {

        $article_id = $db2->f("article_id");
        $db3->query("SELECT * FROM galactic_post_article WHERE game_id = $player->game_id AND article_id = $article_id");
        $db3->next_record();
        $article_title = stripslashes($db3->f("title"));
        print("$article_title<br>");

    }
    print("<br>");

    $container = array();
    $container["url"] = "galactic_post_delete.php";
    transfer("paper");
    transfer("id");
    print_form($container);
    print_submit("Yes");
    print("</form>");
    $container = array();
    $container["url"] = "skeleton.php";
    $container["body"] = "galactic_post_view_article.php";
    transfer("id");
    print_form($container);
    print_submit("No");
    print("</form>");

}

?>