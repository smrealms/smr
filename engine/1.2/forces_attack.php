<?php

print("<p><big><b>Force Results</b></big></p>");

foreach ($var["force_msg"] as $msg)
	print("$msg<br>");

print("<p><img src=\"images/creonti_cruiser.jpg\"></p>");

print("<p><big><b>Attacker Results</b></big></p>");
foreach ($var["attacker_total_msg"] as $attacker_total)
	foreach ($attacker_total as $msg)
		print("$msg<br>");

if ($var["continue"] == "yes") {

	$container = array();
	$container["url"] = "forces_attack_processing.php";
	transfer("owner_id");
	print_form($container);
	if ($var["forced"] == "yes")
		print_submit("Attack (3)");
	else
		print_submit("Continue Attack (3)");
	print("</form>");

} else {

	$container = array();
	$container["url"] = "skeleton.php";
	$container["body"] = "current_sector.php";
	$container["msg"] = "<span style=\"color;yellow;\">You have destroyed the forces.</span>";
	print_form($container);
	print_submit("Current Sector");
	print("</form>");

}

?>