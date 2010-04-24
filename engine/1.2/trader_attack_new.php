<?php

echo $var["results"];

if($var["target"]) {
	$container = array();
	$container["url"] = "trader_attack_processing_new.php";
	$container["target"] = $var["target"];
	echo '<div align="center"><div style="width:50%"';
	switch(mt_rand(0,2)) {
		case(0):
			echo 'align="center">';
			break;
		case(1):
			echo 'align="right">';
			break;
		case(2):
			echo 'align="left">';
			break;
	}
	print_button($container, "Continue Attack");
	echo '</div></div>';
}
else if(isset($var['override_death'])) {
	echo '<div align="center"><h2>The battle has ended!</h2><br>';
	$container = array();
	$container["url"] = "skeleton.php";
	$container['body'] = 'death.php';
	print_button($container, 'Let there be pod');
	echo '</div>';
}
else {
	echo '<div align="center"><h2>The battle has ended!</h2><br>';
	$container = array();
	$container["url"] = "skeleton.php";
	$container['body'] = 'current_sector.php';
	print_button($container, 'Current Sector');
	echo '</div>';
}

?>