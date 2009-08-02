<?php


$db->query("SELECT * FROM beta_test");
if (!$db->next_record() || $db->f("open") == "FALSE") {
	
	print_error("Beta Applications are currently not being accepted.");
	return;
}

print_topic("Apply for Beta");
print("The information on this page will be used by the beta team leader in choosing applicants.<br>");
print("You must fill in all fields for your application to be considered.");

$container = array();
$container["url"] = "beta_apply_processing.php";

print_form($container);

print("<table>");
print("<tr>");
print("<td style=\"font-weight:bold;\">Login:</td>");
print("<input type=\"hidden\" name=\"login\" value=\"$account->login\">");
print("<td>$account->login</td>");
print("</tr>");

print("<tr>");
print("<td style=\"font-weight:bold;\">eMail:</td>");
print("<input type=\"hidden\" name=\"email\" value=\"$account->email\">");
print("<td>$account->email</td>");
print("</tr>");

print("<tr>");
print("<td style=\"font-weight:bold;\">Account ID:</td>");
print("<input type=\"hidden\" name=\"account_id\" value=\"$account->account_id\">");
print("<td>$account->account_id</td>");
print("</tr>");

print("<tr>");
print("<td style=\"font-weight:bold;\">WebBoard Name:</td>");
print("<td><input type=\"text\" name=\"webboard\" id=\"InputFields\" style=\"width:300px;\"></td>");
print("</tr>");

print("<tr>");
print("<td style=\"font-weight:bold;\">IRC Nick:</td>");
print("<td><input type=\"text\" name=\"ircnick\" id=\"InputFields\" style=\"width:300px;\"></td>");
print("</tr>");

print("<tr>");
print("<td style=\"font-weight:bold;\">Approx. time you started playing:</td>");
print("<td><input type=\"text\" name=\"started\" id=\"InputFields\" style=\"width:300px;\"></td>");
print("</tr>");

print("<tr>");
print("<td style=\"font-weight:bold;\" valign=\"top\">Why you think you should become a beta tester:</td>");
print("<td><textarea id=\"InputFields\" name=\"reasons\" style=\"width:300px;height:100px;\"></textarea></td>");
print("</tr>");

print("<tr>");
print("<td style=\"font-weight:bold;\" valign=\"top\">How much time you can spend on beta per week:</td>");
print("<td><input type=\"text\" name=\"time\" id=\"InputFields\" style=\"width:300px;\"></td>");;
print("</tr>");

print("<tr>");
print("<td style=\"font-weight:bold;\" valign=\"top\">Most frequent online times (in server time):</td>");
print("<td><input type=\"text\" name=\"online\" id=\"InputFields\" style=\"width:300px;\"></td>");
print("</tr>");

print("<tr>");
print("<td></td>");
print("<td>");
print_submit("Submit");
print("</td>");
print("</tr>");

print("</table>");
print("</form>");

?>