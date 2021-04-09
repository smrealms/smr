<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

$message = trim(Request::get('message'));
if (Request::get('action') == 'Preview announcement') {
	$container = Page::create('skeleton.php', 'announcement_create.php');
	$container['preview'] = $message;
	$container->go();
}

// put the msg into the database
$db->query('INSERT INTO announcement (time, admin_id, msg) VALUES(' . $db->escapeNumber(Smr\Epoch::time()) . ', ' . $db->escapeNumber($account->getAccountID()) . ', ' . $db->escapeString($message) . ')');

Page::create('skeleton.php', 'admin_tools.php')->go();
