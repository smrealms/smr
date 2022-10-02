<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;
use Smr\Request;

$session = Smr\Session::getInstance();
$account = $session->getAccount();

$message = Request::get('message');
if (Request::get('action') == 'Preview announcement') {
	$container = Page::create('admin/announcement_create.php');
	$container['preview'] = $message;
	$container->go();
}

// put the msg into the database
$db = Database::getInstance();
$db->insert('announcement', [
	'time' => $db->escapeNumber(Epoch::time()),
	'admin_id' => $db->escapeNumber($account->getAccountID()),
	'msg' => $db->escapeString($message),
]);

Page::create('admin/admin_tools.php')->go();
