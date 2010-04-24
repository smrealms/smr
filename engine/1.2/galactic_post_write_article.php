<?php

print_topic("WRITTING AN ARTICLE");
include(get_file_loc('menue.inc'));
print_galactic_post_menue();
print("What is the title?<br>");
$container = array();
$container["url"] = "galactic_post_write_article_processing.php";
print_form($container);
print("<input type=\"text\" name=\"title\" id=\"InputFields\" style=\"text-align:center;width:525;\"><br><br>");
print("<br>Write what you want to write here!<br>");
print("<textarea name=\"message\" id=\"InputFields\" style=\"width:350px;height:100px;\"></textarea><br><br>");
print_submit("Enter the article");
print("</form>");

?>