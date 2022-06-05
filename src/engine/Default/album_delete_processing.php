<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

if (Smr\Request::get('action') == 'Yes') {
	$db = Smr\Database::getInstance();
	$db->write('DELETE
				FROM album
				WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));

	$db->write('DELETE
				FROM album_has_comments
				WHERE album_id = ' . $db->escapeNumber($account->getAccountID()));
}

if ($session->hasGame()) {
	$container = Page::create('current_sector.php');
} else {
	$container = Page::create('game_play.php');
}

$container->go();
