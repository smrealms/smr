<?php

if (empty($var['message']) || $var['message'] == '') $var['message'] = 'File not found';

if (SmrSession::hasGame() && is_object($player) && $lock) {
	$container = create_container('skeleton.php', 'current_sector.php');
	$errorMsg = '<span class="red bold">ERROR:</span> ' . $var['message'];
	$container['errorMsg'] = $errorMsg;
	forward($container);
}
else {
	$template->assign('PageTopic', 'Error');
	$template->assign('Message', $var['message']);
}
