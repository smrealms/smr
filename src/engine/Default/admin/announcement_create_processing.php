<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

$message = Smr\Request::get('message');
if (Smr\Request::get('action') == 'Preview announcement') {
	$container = Page::create('admin/announcement_create.php');
	$container['preview'] = $message;
	$container->go();
}

// put the msg into the database
$db = Smr\Database::getInstance();
$db->insert('announcement', [
	'time' => $db->escapeNumber(Smr\Epoch::time()),
	'admin_id' => $db->escapeNumber($account->getAccountID()),
	'msg' => $db->escapeString($message),
]);

Page::create('admin/admin_tools.php')->go();
