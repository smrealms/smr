<?php

print_topic("MAKING A PAPER");
include(get_file_loc('menue.inc'));
print_galactic_post_menue();
print("What is the title of this edition?<br>");
$container = array();
$container["url"] = "galactic_post_make_paper_processing.php";
print_form($container);
print("<input type=\"text\" name=\"title\" id=\"InputFields\" style=\"text-align:center;width:525;\"><br><br>");
print_submit("Make the paper");
print("</form>");

?>