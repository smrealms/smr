<?php declare(strict_types=1);

require('planet.inc');

//echo the dump cargo message or other message.
if (isset($var['errorMsg'])) {
	$template->assign('ErrorMsg', $var['errorMsg']);
}
if (isset($var['msg'])) {
	$template->assign('Msg', bbifyMessage($var['msg']));
}

doTickerAssigns($template, $player, $db);

$template->assign('LaunchFormLink', SmrSession::getNewHREF(create_container('planet_launch_processing.php', '')));
