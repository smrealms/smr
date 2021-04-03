<?php declare(strict_types=1);

if (empty($var['message']) || $var['message'] == '') {
	$var['message'] = 'File not found';
}

if (SmrSession::hasGame() && is_object($player) && $lock) {
	$container = Page::create('skeleton.php', 'current_sector.php');
	$errorMsg = '<span class="red bold">ERROR:</span> ' . $var['message'];
	$container['errorMsg'] = $errorMsg;
	$container->go();
} else {
	$template->assign('PageTopic', 'Error');
	$template->assign('Message', $var['message']);
}
