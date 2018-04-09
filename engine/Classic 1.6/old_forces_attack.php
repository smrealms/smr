<?php

$PHP_OUTPUT.=('<p><big><b>Force Results</b></big></p>');

foreach ($var['force_msg'] as $msg)
	$PHP_OUTPUT.=($msg.'<br>');

$PHP_OUTPUT.=('<p><img src="images/creonti_cruiser.jpg"></p>');

$PHP_OUTPUT.=('<p><big><b>Attacker Results</b></big></p>');
foreach ($var['attacker_total_msg'] as $attacker_total)
	foreach ($attacker_total as $msg)
		$PHP_OUTPUT.=($msg.'<br>');

if ($var['continue'] == 'yes') {

	$container = array();
	$container['url'] = 'forces_attack_processing.php';
	transfer('owner_id');
	$PHP_OUTPUT.=create_echo_form($container);
	if ($var['forced'] == 'yes')
		$PHP_OUTPUT.=create_submit('Attack (3)');
	else
		$PHP_OUTPUT.=create_submit('Continue Attack (3)');
	$PHP_OUTPUT.=('</form>');

} else {

	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'current_sector.php';
	$container['msg'] = '<span style="color;yellow;">You have destroyed the forces.</span>';
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=create_submit('Current Sector');
	$PHP_OUTPUT.=('</form>');

}
