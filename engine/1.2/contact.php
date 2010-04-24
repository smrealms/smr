<?php

print_topic("Report a Bug");

print("<span style=\"font-size:75%;\">Please use this form to either send your feedback or<br>");
print("questions to the admin team of Space Merchant Realms!</span>");

print_form(create_container("contact_processing.php", ""));

print("<table>");

print("<tr>");
print("<td style=\"font-weight:bold;\">From:</td>");
print("<td>$account->login</td>");
print("</tr>");

print("<tr>");
print("<td style=\"font-weight:bold;\">To:</td>");
print("<td>");
print("<select name=\"receiver\">");
print("<option default>support@smrealms.de</option>");
print("<option>multi@smrealms.de</option>");
print("<option>beta@smrealms.de</option>");
print("<option>chat@smrealms.de</option>");
print("</select>");
print("</td>");
print("</tr>");

print("<tr>");
print("<td style=\"font-weight:bold;\">Subject:</td>");
print("<td><input type=\"text\" name=\"subject\" id=\"InputFields\" style=\"width:500px;\"></td>");
print("</tr>");

print("<tr>");
print("<td style=\"font-weight:bold;\" valign=\"top\">Message:</td>");
print("<td><textarea id=\"InputFields\" name=\"msg\" style=\"width:500px;height:400px;\"></textarea></td>");
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