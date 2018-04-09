<?php
$exp = $_REQUEST['exp'];
$message = $_REQUEST['message'];
$amount = $_REQUEST['amount'];
if (empty($exp) || empty($message) || empty($amount))
	create_error('You left some value blank.');
if(!is_numeric($amount)) {
	create_error('Articles per day must be a number.');
}
if ($exp == 1)
	$value = 'YES';
else
	$value = 'NO';
$db->query('SELECT * FROM galactic_post_applications WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND account_id = ' . $db->escapeNumber($player->getAccountID()));
if ($db->nextRecord())
	create_error('You have already applied once.  Please be patient and your application will be answered at a later time.');

$db->query('INSERT INTO galactic_post_applications (game_id, account_id, description, written_before, articles_per_day) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escape_string($message,true) . ', '.$db->escapeString($value).', '.$db->escapeNumber($amount).')');

$container = create_container('skeleton.php', 'current_sector.php');
$container['msg'] = 'Thank you for your application.  It has been sent to the main editor and he will let you know if you have been accepted.';
forward($container);
