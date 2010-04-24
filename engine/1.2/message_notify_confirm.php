<?php

print_topic("Report a Message");

if (empty($var["message_id"]))
	create_error("Please click the small yellow icon to report a message!");

// get message form db
$db->query("SELECT message_text
			FROM message
			WHERE message_id = " . $var["message_id"]);
if (!$db->next_record())
	create_error("Could not find the message you selected!");

print("You have selected the following message:<br><br>");
print("<textarea id=\"InputFields\" style=\"width:400px;height:300px;\">" . stripslashes($db->f("message_text")) . "</textarea>");

print("<p>Are you sure you want to notify this message to the admins?<br>");
print("<small><b>Please note:</b> Abuse of this system could end in disablement<br>Therefore, please only notify if the message is inappropriate</small></p>");

$container = create_container("message_notify_processing.php", "");
transfer("message_id");
transfer("sent_time");
transfer("notified_time");

print_form($container);
print_submit("Yes");
print("&nbsp;&nbsp;");
print_submit("No");
print("</form>");

?>