<?php

if (empty($var['message']) || $var['message'] == '') $var['message'] = 'File not found';

if (SmrSession::hasGame() && is_object($player) && $lock) {
	$container = create_container('skeleton.php', 'current_sector.php');
	$errorMsg = '<span class="red bold">ERROR:</span> ' . $var['message'];
	$container['errorMsg'] = $errorMsg;
	forward($container);
}
else {
	$PHP_OUTPUT.=('<h1>ERROR</h1>');
	$PHP_OUTPUT.=('<p class="big bold">' . $var['message'] . '</p>');
	$PHP_OUTPUT.=('<br /><br /><br />');
	$PHP_OUTPUT.=('<p><small>If the error was caused by something you entered, press back and try again.</small></p>');
	$PHP_OUTPUT.=('<p><small>If it was a DB Error, press back and try again, or logoff and log back on.</small></p>');
	$PHP_OUTPUT.=('<p><small>If the error was unrecognizable, please notify the administrators.</small></p>');
}
