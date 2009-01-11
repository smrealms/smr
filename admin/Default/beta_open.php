<?php

$PHP_OUTPUT.=('<h1>OPEN/CLOSE BETA APPLICATION</h1>');

$db->query('SELECT * FROM beta_test');
$db->nextRecord();

$open = $db->getField('open');

$container = array();
$container['url'] = 'beta_open_processing.php';

if ($open == 'TRUE') {

	$PHP_OUTPUT.=('<p>Beta Application Status: <span style="color:green;">OPEN</span></p>');

	$container['open'] = true;

	$PHP_OUTPUT.=create_link($container, '<b>Close Application Form</b>');

} else {

	$PHP_OUTPUT.=('<p>Beta Application Status: <span style="color:red;">CLOSED</span></p>');

	$container['open'] = false;

	$PHP_OUTPUT.=create_link($container, '<b>Open Application Form</b>');

}

?>