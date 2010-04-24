<?php

//macro prevent
print '<script language="javascript">
var today = new Date();
var expiry = new Date(today.getTime() + 60 * 1000);
function setCookie(name, value) {
	
	if (value != null && value != "") 
	document.cookie=name + "=" + escape(value) + "; expires=" + expiry.toGMTString();
	bikky= document.cookie;
	
}
</script>';

$results = "<p><big><b>Attacker Results</b></big></p>";

foreach ($var["attackerguy"] as $damage_messages)
	foreach ($damage_messages as $msg)
		$results .= $msg . '<br>';

$results .= '<br><br><p><img src="images/creonti_cruiser.jpg"></p><p><big><b>Defender Results</b></big></p>';

foreach ($var["defenderguy"] as $damage_messages)
	foreach ($damage_messages as $msg)
		$results .= $msg . '<br>';

// Insert the result into the logs table
$defender_alliance_id = 0;

$db->query('SELECT alliance_id FROM player WHERE player_id=' . $var['target'] . ' LIMIT 1');
if($db->next_record()) {
	$defender_alliance_id = $db->f('alliance_id');
}

list($usec, $sec) = explode(" ", microtime());
$usec = (int)($usec * 1000);

$db->query('INSERT INTO combat_logs VALUES("",' . SmrSession::$game_id . ',"PLAYER",' . $player->sector_id . ',' . $sec . ',' . SmrSession::$old_account_id . ',' . $player->alliance_id . ',' . $var['target'] . ',' . $defender_alliance_id . ',"' . mysql_real_escape_string(gzcompress($results)) . '")');

echo $results;

if ($var["continue"] == "Yes") {

	$container = array();
	$container["url"] = "trader_attack_processing.php";
	transfer("target");
	print_form($container);
	//stop scripts/macros
	$sp = chr(mt_rand(0,255));
	for ($i=0; $i <= mt_rand(0,2); $i++) print("<br>");
	print("<button type=\"submit\" name=\"action\" id=\"InputFields\" onMouseOver=setCookie(\"Legit\",1);>Continue<font color=\"#0B2121\">" . $sp . "</font>Attack (3)</button>");
	print("</form>");

}

else {

	print("The battle has ended!<br /><br />");
	print_link(create_container("skeleton.php", "current_sector.php"), "<b>Return to Current Sector</b>");

}

?>