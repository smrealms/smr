<?php declare(strict_types=1);

$password = trim($_REQUEST['password']);

if (empty($password)) {
	create_error('You cannot use a blank password!');
}

$db->query('SELECT MAX(anon_id) FROM anon_bank WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
if ($db->nextRecord()) {
	$new_acc = $db->getInt('MAX(anon_id)') + 1;
}
$db->query('INSERT INTO anon_bank (game_id, anon_id, owner_id, password, amount) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($new_acc) . ', ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeString($password) . ', 0)');

$container = create_container('skeleton.php', 'bank_anon.php');
$container['message'] = '<p>Account #' . $new_acc . ' has been opened for you.</p>';
forward($container);
