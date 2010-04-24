<?php

$results = $var["results"];
echo $results[0];
echo '<br /><img src="images/planetAttack.jpg" alt="Planet Attack" title="Planet Attack"><br />';
echo $results[1];
echo '<br />';
if($var["continue"] && !isset($var['override_death'])) {
	$container = array();
	$container["url"] = "planet_attack_processing.php";
	echo '<div align="center">';
	print_button($container, "Continue Attack");
	echo '</div>';
} else {
	echo '<div align="center"><h2>The battle has ended!</h2><br />';
	$container = array();
	$container["url"] = "skeleton.php";
	$container['body'] = 'current_sector.php';
	print_button($container, 'Current Sector');
	echo '</div>';
}

?>