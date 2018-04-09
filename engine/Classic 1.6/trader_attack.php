<?php

$PHP_OUTPUT.= $var['results'];

if($var['target']) {
	$container = array();
	$container['url'] = 'trader_attack_processing_new.php';
	$container['target'] = $var['target'];
	$PHP_OUTPUT.= '<div align="center"><div style="width:50%"';
	switch(mt_rand(0,2)) {
		case(0):
			$PHP_OUTPUT.= 'align="center">';
			break;
		case(1):
			$PHP_OUTPUT.= 'align="right">';
			break;
		case(2):
			$PHP_OUTPUT.= 'align="left">';
			break;
	}
	$PHP_OUTPUT.=create_button($container, 'Continue Attack');
	$PHP_OUTPUT.= '</div></div>';
}
else if(isset($var['override_death'])) {
	$PHP_OUTPUT.= '<div align="center"><h2>The battle has ended!</h2><br>';
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'death.php';
	$PHP_OUTPUT.=create_button($container, 'Let there be pod');
	$PHP_OUTPUT.= '</div>';
}
else {
	$PHP_OUTPUT.= '<div align="center"><h2>The battle has ended!</h2><br>';
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'current_sector.php';
	$PHP_OUTPUT.=create_button($container, 'Current Sector');
	$PHP_OUTPUT.= '</div>';
}
