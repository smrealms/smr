<?php

$results = $var["results"];
echo $results[0];
echo '<br /><img src="images/portAttack.jpg" width="480px" height="330px" alt="Port Attack" title="Port Attack"><br />';
echo $results[1];
echo '<br />';
if($var["continue"] && !isset($var['override_death'])) {
	$container = array();
	$container["url"] = "port_attack_processing_new.php";
	echo '<div align="center">';
	print_button($container, "Continue Attack");
	echo '</div>';
} elseif (isset($var['override_death'])) {
	echo '<div align="center"><h2>The battle has ended!</h2><br />';
	$container = array();
	$container["url"] = "skeleton.php";
	$container['body'] = 'current_sector.php';
	print_button($container, 'Current Sector');
} else {
	echo '<div align="center"><h2>The battle has ended!</h2><br />';
	$container = array();
	$container["url"] = "skeleton.php";
	$container['body'] = 'current_sector.php';
	print_button($container, 'Current Sector');
	echo '&nbsp;';
	//we can now claim
	print_button(create_container("port_claim_processing.php", ""), 'Claim this port for your race');
	echo '&nbsp;';
	print_button(create_container("skeleton.php", "port_loot.php"), 'Loot the port');
	echo '</div>';
}

?>