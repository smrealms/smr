<?php

print_topic("Galactic Post Application");
include(get_file_loc('menue.inc'));
print_galactic_post_menue();
$container = array();
$container["url"] = "galactic_post_application_processing.php";
print_form($container);
print("<br>Have you ever written for any type of newspaper before?<br>");
print("Yes : <input type=\"radio\" name=\"exp\" value=\"1\"><br>");
print("No : <input type=\"radio\" name=\"exp\" value=\"2\"><br>");
print("<br>");
print("How many articles would you write per day if you were selected?<br>");
print("<input type=\"text\" name=\"amount\" value=\"0\" id=\"InputFields\" style=\"text-align:right;width:25;\">");
print("<br>");
print("In 255 characters or less please describe why you should be accepted<br>");
print("<textarea name=\"message\" id=\"InputFields\" style=\"width:350px;height:100px;\"></textarea>");
print("<br><br>");
print_submit("Apply");
print("</form>");

?>