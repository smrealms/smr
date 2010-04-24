<?php

print_topic("Report a Bug");

print("<span style=\"font-size:75%;\">All information you can see on this page will be sent via email to the developer team!<br>");
print("Be as accurate as possible with your bug description.</span>");

print_form(create_container("bug_report_processing.php", ""));

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
print("<td style=\"font-weight:bold;\">Subject:</td>");
print("<td><input type=\"text\" name=\"subject\" id=\"InputFields\" style=\"width:300px;\"></td>");
print("</tr>");

print("<tr>");
print("<td style=\"font-weight:bold;\" valign=\"top\">Description:</td>");
print("<td><textarea id=\"InputFields\" name=\"description\" style=\"width:300px;height:100px;\"></textarea></td>");
print("</tr>");

print("<tr>");
print("<td style=\"font-weight:bold;\" valign=\"top\">Steps to repeat:</td>");
print("<td><textarea id=\"InputFields\" name=\"steps\" style=\"width:300px;height:100px;\"></textarea></td>");
print("</tr>");

print("<tr>");
print("<td style=\"font-weight:bold;\" valign=\"top\">Error Message:</td>");
print("<td><textarea id=\"InputFields\" name=\"error_msg\" style=\"width:300px;height:100px;\"></textarea></td>");
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