<?php

print_topic("VALIDATION REMINDER");

$container = array();
$container["url"] = "validate_processing.php";

print_form($container);

print("<p>Welcome " . $account->first_name . ",</p>");
print("<p>");
print("Thank you for trying out Space Merchant Realms! We hope that you are enjoying the game. However,");
print("in order for you to experience the full features of the game, you need to validate your login.");
print("When you first created your login, you should have received an email confirmation which includes");
print("your validation code. If you have not received this, please verify that you gave us the correct");
print("email address by going to the user preferences page. If it");
print("is incorrect, please edit the email address and it will generate a new code and have it sent to");
print("you.");
print("</p>");
print("<p>");
print("The following restrictions are placed on users who have not validated their account:");
print("<ul>");
print("<li>No additional turns are granted to your traders while you are not validated.");
print("<li>Bank access is denied.");
print("<li>You will be unable to land on a planet.");
print("<li>You will be unable to access alliances.");
print("<li>You will be unable to vote in the daily politics of the universe.");
print("</ul>");
print("</p>");
print("<p>");
print("Enter validation code:&nbsp;&nbsp;");
print("<input type=\"text\" name=\"validation_code\" maxlength=\"10\" size=\"10\" id=\"InputFields\" style=\"text-align:center;\">");
print("</p>");
print("<p align=\"center\">");
print_submit("Validate me now!");
print("&nbsp;&nbsp;");
print_submit("I'll validate later.");
print("</p>");
print("</form>");

?>