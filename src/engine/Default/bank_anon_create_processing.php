<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$password = trim(Request::get('password'));

if (empty($password)) {
	create_error('You cannot use a blank password!');
}

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT MAX(anon_id) FROM anon_bank WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
if ($dbResult->hasRecord()) {
	$new_acc = $dbResult->record()->getInt('MAX(anon_id)') + 1;
}
$db->write('INSERT INTO anon_bank (game_id, anon_id, owner_id, password, amount) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($new_acc) . ', ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeString($password) . ', 0)');

$container = Page::create('skeleton.php', 'bank_anon.php');
$container['message'] = '<p>Account #' . $new_acc . ' has been opened for you.</p>';
$container->go();
