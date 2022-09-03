<?php declare(strict_types=1);

use Smr\SectorLock;

$lock = SectorLock::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

if (empty($var['message'])) {
	throw new Exception('Error is missing a player message!');
}

if ($session->hasGame() && $lock->isActive()) {
	$container = Page::create('current_sector.php');
	$errorMsg = '<span class="red bold">ERROR:</span> ' . $var['message'];
	$container['errorMsg'] = $errorMsg;
	$container->go();
} else {
	$template = Smr\Template::getInstance();
	$template->assign('PageTopic', 'Error');
	$template->assign('Message', $var['message']);
}
