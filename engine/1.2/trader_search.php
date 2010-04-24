<?php

print_topic("SEARCH TRADER");
print("<p>&nbsp;</p>");

print_form(create_container("skeleton.php", "trader_search_result.php"));

print("<span style=\"font-size:75%;\">Player name:</span><br>");
print("<input type=\"text\" name=\"player_name\" id=\"InputFields\" style=\"width:150px\">&nbsp;");
print_submit("Search");

print("<p>&nbsp;</p>");

print("<span style=\"font-size:75%;\">Player ID:</span><br>");
print("<input type=\"text\" name=\"player_id\" id=\"InputFields\" style=\"width:50px\">&nbsp;");
print_submit("Search");

print("</form>");

?>