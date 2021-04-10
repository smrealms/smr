<?php declare(strict_types=1);

if (empty($var['message']) || $var['message'] == '') {
	$var['message'] = 'File not found';
}

if (Smr\Session::getInstance()->hasGame() && $lock) {
	$container = Page::create('skeleton.php', 'current_sector.php');
	$errorMsg = '<span class="red bold">ERROR:</span> ' . $var['message'];
	$container['errorMsg'] = $errorMsg;
	$container->go();
} else {
	$template = Smr\Template::getInstance();
	$template->assign('PageTopic', 'Error');
	$template->assign('Message', $var['message']);
}
