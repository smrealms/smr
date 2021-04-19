<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

require('planet.inc.php');

//echo the dump cargo message or other message.
if (isset($var['errorMsg'])) {
	$template->assign('ErrorMsg', $var['errorMsg']);
}
if (isset($var['msg'])) {
	$template->assign('Msg', bbifyMessage($var['msg']));
}

$db = Smr\Database::getInstance();
doTickerAssigns($template, $player, $db);

$template->assign('LaunchLink', Page::create('planet_launch_processing.php', '')->href());
