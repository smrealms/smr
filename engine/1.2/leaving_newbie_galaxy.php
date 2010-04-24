<?php

print_topic("Warning");

print("<p>As you approach the warp you notice a warning beacon nearby. The beacon sends an automated message to your ship.</p>");

print("<p>\"Your racial government cannot protect low-ranking traders in the galaxy you are about to enter. In this area you will be vulnerable to attack by high-ranked ships. It is not recommended that you enter this area at your current status.\"</p>");

$container = create_container("sector_" . $var["method"] . "_processing.php", "");
transfer("target_page");
transfer("target_sector");

print("Are you sure you want to leave the newbie galaxy?");
print_form($container);

// for jump we need a 'to' field
print("<input type=\"hidden\" name=\"to\" value=\"$var[target_sector]\">");

print_submit("Yes");
print("&nbsp;");
print_submit("No");
print("</form>");

?>