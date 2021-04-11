<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

if (Request::get('action') == 'Yes') {
	$db = Smr\Database::getInstance();
	$db->query('DELETE
				FROM album
				WHERE account_id = ' . $db->escapeNumber($account->getAccountID()) . ' LIMIT 1');

	$db->query('DELETE
				FROM album_has_comments
				WHERE album_id = ' . $db->escapeNumber($account->getAccountID()));
}

$container = Page::create('skeleton.php');
if ($session->hasGame()) {
	$container['body'] = 'current_sector.php';
} else {
	$container['body'] = 'game_play.php';
}

$container->go();
