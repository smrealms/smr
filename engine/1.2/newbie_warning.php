<?php

$player->newbie_warning = "FALSE";
$player->update();

print_topic("WARNING!");

print("<p>You have gotten this page because you are almost out of newbie turnz.");
print("What does this mean? You can now do many things that you couldn't do while in newbie turns.</p>");

print("<ol>You can...");
print("<li>attack other players</li>");
print("<li>attack enemy planet's</li>");
print("<li>Port raid</li>");
print("<li>Place forces</li>");
print("</ol>");

print("<p>But remember, with the good comes the bad. In addition to being able to do the above, they can happan to you.</p>");

print("<ol>You can...</li>");
print("<li>Be Attacked</li>");
print("<li>Hit enemy forces and take damage</li>");
print("<li>die...</li>");
print("</ol>");

print("<p>Plan for your safety now. Remember to use federal Protection (Must have an attack rating of 3 or less), know where your alliances strongest planet's are, and watch out for the people looking for you.</p>");

print("<p>For more information visit the <a href=\"".URL."/manual.php\">help files</a></p>");

?>