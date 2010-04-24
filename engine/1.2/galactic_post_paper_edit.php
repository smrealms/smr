<?php

$db2 = new SmrMySqlDatabase();
$db->query("SELECT * FROM galactic_post_paper WHERE paper_id = $var[id] AND game_id = $player->game_id");
$db->next_record();
$paper_title = stripslashes($db->f("title"));
$db->query("SELECT * FROM galactic_post_paper_content WHERE paper_id = $var[id]");
include(get_file_loc('menue.inc'));
print_galactic_post_menue();
print("$paper_title<br><br><ul>");
while ($db->next_record()) {

    $db2->query("SELECT * FROM galactic_post_article WHERE game_id = $player->game_id AND article_id = " . $db->f("article_id"));
    $db2->next_record();
    $article_title = stripslashes($db2->f("title"));
    $article_text = stripslashes($db2->f("text"));
    print("<li>$article_title<br><li>$article_text<br></li>");
    $container = array();
    $container["url"] = "galactic_post_paper_edit_processing.php";
    $container["article_id"] = $db->f("article_id");
    transfer("id");
    print_link($container, "Remove this article from $paper_title</li>");
    print("<br><br>");

}
print("</ul>");
if (!$db->nf())
    print("This paper has no articles");

?>