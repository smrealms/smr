<?php

print_topic("Delete Album Entry - Confirmation");

print("Are you sure you want to delete your photo album entry and all comments added to it?<br>");
print("This action can't be undone.");

print_form(create_container("album_delete_processing.php", ""));

print_submit("Yes");
print("&nbsp;&nbsp;&nbsp;");
print_submit("No");

print("</form>");

?>