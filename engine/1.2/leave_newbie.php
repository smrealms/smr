<?php

print_topic("LEAVE NEWBIE PROTECTION");

print_form(create_container("leave_newbie_processing.php", ""));
print("Do you really want to leave Newbie Protection?<br><br>");
print_submit("Yes!");
print("&nbsp;&nbsp;");
print_submit("No!");
print("</form>");

?>