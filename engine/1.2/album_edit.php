<?php

create_error('Please use main album edit.');

print_topic("Edit Photo");

print("<p><span style=\"font-size:80%;\">Here you have a chance to add an entry to the Space Merchant Realms - The Photo Album!<br>");
print("We only accept jpg or gif images to a maximum of 500 x 500 in size.<br>");
print("Your image will be posted under your <i>Hall Of Fame</i> nick!<br>");
print("<b>Please Note:</b> Your entry needs to be approved by an admin before going online</p>");

print("<p style=\"font-size:150%;\">");
print("Status of your album entry: ");

$db->query("SELECT * FROM album WHERE account_id = ".SmrSession::$old_account_id);
if ($db->next_record()) {

	$location = stripslashes($db->f("location"));
	$email = stripslashes($db->f("email"));
	$website = stripslashes($db->f("website"));
	$day = $db->f("day");
	$month = $db->f("month");
	$year = $db->f("year");
	$other = stripslashes($db->f("other"));
	$approved = $db->f("approved");
	$disabled = $db->f("disabled");

	if ($approved == "TBC")
		print("<span style=\"color:orange;\">Waiting approval</span>");
	elseif ($approved == "NO")
		print("<span style=\"color:red;\">Approval denied</span>");
	elseif ($disabled == "TRUE")
		print("<span style=\"color:red;\">Disabled</span>");
	elseif ($approved == "YES")
		print("<a href=\"".URL."/album/?$account->HoF_name\" style=\"color:green;\">Online</a>");

} else
	print("<span style=\"color:orange;\">No entry</span>");

print("</p>");


if (empty($location))
	$location = "N/A";
if (empty($email))
	$email = "N/A";
if (empty($website))
	$website = "http://";
if (empty($day))
	$day = "N/A";
if (empty($month))
	$month = "N/A";
if (empty($year))
	$year = "N/A";
if (empty($other))
	$other = "N/A";


print_form_parameter(create_container("album_edit_processing.php", ""), "enctype=\"multipart/form-data\"");
print("<table>");

print("<tr>");
print("<td align=\"right\" style=\"font-weight:bold;\">Nick:</td>");
print("<td>$account->HoF_name</td>");
print("</tr>");

print("<tr>");
print("<td align=\"right\" style=\"font-weight:bold;\">Location:</td>");
print("<td><input type=\"text\" name=\"location\" id=\"InputFields\" value=\"$location\" onFocus=\"javascript:if (this.value == 'N/A') {this.value = '';}\" onBlur=\"javascript:if (this.value == '') {this.value = 'N/A';}\"></td>");
print("</tr>");

print("<tr>");
print("<td align=\"right\" style=\"font-weight:bold;\">Email Address:</td>");
print("<td><input type=\"text\" name=\"email\" id=\"InputFields\" value=\"$email\" style=\"width:303px;\" onFocus=\"javascript:if (this.value == 'N/A') {this.value = '';}\" onBlur=\"javascript:if (this.value == '') {this.value = 'N/A';}\"></td>");
print("</tr>");

print("<tr>");
print("<td align=\"right\" style=\"font-weight:bold;\">Website:</td>");
print("<td><input type=\"text\" name=\"website\" id=\"InputFields\" style=\"width:303px;\" value=\"$website\" onBlur=\"javascript:if (this.value == '') {this.value = 'http://';}\"></td>");
print("</tr>");

print("<tr>");
print("<td align=\"right\" style=\"font-weight:bold;\">Birthdate:</td>");
print("<td>Month:&nbsp;<input type=\"text\" name=\"day\" id=\"InputFields\" value=\"$day\" size=\"3\" maxlength=\"2\" style=\"text-align:center;\" onFocus=\"javascript:if (this.value == 'N/A') {this.value = '';}\" onBlur=\"javascript:if (this.value == '') {this.value = 'N/A';}\">&nbsp;&nbsp;&nbsp;");
print("Day:&nbsp;<input type=\"text\" name=\"month\" id=\"InputFields\" value=\"$month\" size=\"3\" maxlength=\"2\" style=\"text-align:center;\" onFocus=\"javascript:if (this.value == 'N/A') {this.value = '';}\" onBlur=\"javascript:if (this.value == '') {this.value = 'N/A';}\">&nbsp;&nbsp;&nbsp;");
print("Year:&nbsp;<input type=\"text\" name=\"year\" id=\"InputFields\" value=\"$year\" size=\"3\" maxlength=\"4\" style=\"text-align:center;\" onFocus=\"javascript:if (this.value == 'N/A') {this.value = '';}\" onBlur=\"javascript:if (this.value == '') {this.value = 'N/A';}\"></td>");
print("</tr>");

print("<tr>");
print("<td align=\"right\" valign=\"top\" style=\"font-weight:bold;\">Other Info:<br><small>(AIM/ICQ)</small></td>");
print("<td><textarea name=\"other\" id=\"InputFields\" style=\"width:303px;height:100px;\" onFocus=\"javascript:if (this.value == 'N/A') {this.value = '';}\" onBlur=\"javascript:if (this.value == '') {this.value = 'N/A';}\">$other</textarea></td>");
print("</tr>");

print("<tr>");
print("<td align=\"right\" valign=\"top\" style=\"font-weight:bold;\">Image:</td>");
print("<td>");
if (is_readable(UPLOAD . SmrSession::$old_account_id))
	print("<img src=\"".URL."/upload/".SmrSession::$old_account_id."\"><br />");
print("<input type=\"file\" name=\"photo\" accept=\"image/jpeg\" id=\"InputFields\" style=\"width:303px;\" ></td>");
print("</tr>");

print("<tr>");
print("<td>&nbsp;</td>");
print("<td>");
print_submit("Submit");
print("&nbsp;&nbsp;&nbsp;");
print_submit("Delete Entry");
print("</td>");
print("</tr>");

print("</table>");
print("</form>");


?>