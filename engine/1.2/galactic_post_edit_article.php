<?php

print_topic("EDITING AN ARTICLE");
include(get_file_loc('menue.inc'));
print_galactic_post_menue();
$db->query("SELECT * FROM galactic_post_article WHERE game_id = $player->game_id AND article_id = $var[id]");
$db->next_record();
$title = $db->f("title");
$text = $db->f("text");
$container = array();
$container["url"] = "galactic_post_edit_article_processing.php";
transfer("id");
print_form($container);
print("What is the title?<br>");
print("<input type=\"text\" name=\"title\" align=\"center\" value=\"$title\" id=\"InputFields\" style=\"text-align:center;width:525;\"><br><br>");
print("<br>Write what you want to write here!<br>");
print("<textarea name=text rows=10 cols=65 wrap=soft id=InputFieldsText>$text</textarea><br><br>");
print_submit("Enter the article");
print("</form>");

?>