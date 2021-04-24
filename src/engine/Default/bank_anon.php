<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$account = $session->getAccount();
$player = $session->getPlayer();

// is account validated?
if (!$account->isValidated()) {
	create_error('You are not validated so you cannot use banks.');
}

$template->assign('PageTopic', 'Anonymous Account');
Menu::bank();

$container = Page::create('skeleton.php', 'bank_anon_detail.php');
$template->assign('AccessHREF', $container->href());

$template->assign('Message', $var['message'] ?? '');

$db = Smr\Database::getInstance();
$db->query('SELECT * FROM anon_bank
			WHERE owner_id=' . $db->escapeNumber($player->getAccountID()) . '
			AND game_id=' . $db->escapeNumber($player->getGameID()));

$ownedAnon = [];
$db2 = Smr\Database::getInstance();
while ($db->nextRecord()) {
	$anon = [];
	$anon['anon_id'] = $db->getInt('anon_id');
	$anon['password'] = $db->getField('password');
	$anon['amount'] = $db->getInt('amount');

	$db2->query('SELECT MAX(time) FROM anon_bank_transactions
				WHERE game_id=' . $db2->escapeNumber($player->getGameID()) . '
				AND anon_id=' . $db2->escapeNumber($db->getInt('anon_id')) . ' LIMIT 1');
	if ($db2->nextRecord() && $db2->getInt('MAX(time)')) {
		$anon['last_transaction'] = date($account->getDateTimeFormat(), $db2->getInt('MAX(time)'));
	} else {
		$anon['last_transaction'] = 'No transactions';
	}

	$container['account_num'] = $anon['anon_id'];
	$container['password'] = $anon['password'];
	$anon['href'] = $container->href();

	$ownedAnon[] = $anon;
}
$template->assign('OwnedAnon', $ownedAnon);

$container = Page::create('skeleton.php', 'bank_anon_create.php');
$template->assign('CreateHREF', $container->href());
