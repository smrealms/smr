<?php

$results = $var['results'];
$PHP_OUTPUT.= $results[0];
$PHP_OUTPUT.= '<br /><img src="images/planetAttack.jpg" alt="Planet Attack" title="Planet Attack"><br />';
$PHP_OUTPUT.= $results[1];
$PHP_OUTPUT.= '<br />';
if($var['continue'] && !isset($var['override_death'])) {
	$container = array();
	$container['url'] = 'planet_attack_processing.php';
	$PHP_OUTPUT.= '<div align="center">';
	$PHP_OUTPUT.=create_button($container, 'Continue Attack');
	$PHP_OUTPUT.= '</div>';
} else {
	$PHP_OUTPUT.= '<div align="center"><h2>The battle has ended!</h2><br />';
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'current_sector.php';
	$PHP_OUTPUT.=create_button($container, 'Current Sector');
	$PHP_OUTPUT.= '</div>';
}

?>