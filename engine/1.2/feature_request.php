<?php

print_topic("FEATURE REQUEST");

$db->query("SELECT * FROM account_votes_for_feature WHERE account_id = ".SmrSession::$old_account_id);
if ($db->next_record())
	$feature_vote = $db->f("feature_request_id");

$db->query("SELECT f.feature_request_id AS feature_id, " .
				  "f.feature AS feature_msg, " .
				  "f.submitter_id AS submitter_id, " .
				  "COUNT(v.feature_request_id) AS votes " .
  				"FROM feature_request f LEFT OUTER JOIN account_votes_for_feature v ON f.feature_request_id = v.feature_request_id " .
  				"GROUP BY feature_id, feature_msg " .
  				"ORDER BY votes DESC, feature_id");

if ($db->nf() > 0) {

	print_form(create_container("feature_request_vote.php", ""));
	print("<p><table cellspacing=\"0\" cellpadding=\"3\" border=\"0\" class=\"standard\" width=\"100%\">");
	print("<tr>");
	print("<th width=\"30\">Votes</th>");
	print("<th>Feature</th>");
	print("<th width=\"20\">&nbsp;</th>");
	print("</tr>");

	while ($db->next_record()) {

		$feature_request_id = $db->f("feature_id");
		$submitter_id = $db->f("submitter_id");
		$message = stripslashes($db->f("feature_msg"));
		$votes = $db->f("votes");

		print("<tr>");
		print("<td valign=\"top\" align=\"center\">$votes</td>");
		print("<td valign=\"top\">$message</td>");
		print("<td valign=\"middle\" align=\"center\"><input type=\"radio\" name=\"vote\" value=\"$feature_request_id\"");
		if ($feature_request_id == $feature_vote) print(" checked");
		print("></td>");
		print("</tr>");

	}

	print("</table></p>");
	print("<div align=\"right\"><input type=\"submit\" value=\"Vote\"></div>");
	print("</form>");

}

print("<p>");
print_form(create_container("feature_request_processing.php", ""));
print("<table border=\"0\" cellpadding=\"5\">");
print("<tr>");
print("<td align=\"center\">Please describe the feature here:</td>");
print("</tr>");
print("<tr>");
print("<td align=\"center\"><textarea name=\"feature\" id=\"InputFields\" style=\"width:350px;height:100px;\"></textarea></td>");
print("</tr>");
print("<tr>");
print("<td align=\"center\">");
print_submit("Submit New Feature");
print("</td>");
print("</tr>");
print("</table>");
print("</form></p>");

?>