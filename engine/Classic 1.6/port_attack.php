<?php

$results = $var['results'];
$PHP_OUTPUT.= $results[0];
$PHP_OUTPUT.= '<br /><img src="images/portAttack.jpg" width="480px" height="330px" alt="Port Attack" title="Port Attack"><br />';
$PHP_OUTPUT.= $results[1];
$PHP_OUTPUT.= '<br />';
if($var['continue'] && !isset($var['override_death'])) {
	$container = array();
	$container['url'] = 'port_attack_processing_new.php';
	$PHP_OUTPUT.= '<div align="center">';
	$PHP_OUTPUT.=create_button($container, 'Continue Attack');
	$PHP_OUTPUT.= '</div>';
} elseif (isset($var['override_death'])) {
	$PHP_OUTPUT.= '<div align="center"><h2>The battle has ended!</h2><br />';
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'current_sector.php';
	$PHP_OUTPUT.=create_button($container, 'Current Sector');
} else {
	$PHP_OUTPUT.= '<div align="center"><h2>The battle has ended!</h2><br />';
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'current_sector.php';
	$PHP_OUTPUT.=create_button($container, 'Current Sector');
	$PHP_OUTPUT.= '&nbsp;';
	//we can now claim
	$PHP_OUTPUT.=create_button(create_container('port_claim_processing.php', ''), 'Claim this port for your race');
	$PHP_OUTPUT.= '&nbsp;';
	$PHP_OUTPUT.=create_button(create_container('skeleton.php', 'port_loot.php'), 'Loot the port');
	$PHP_OUTPUT.= '</div>';
}

?>