<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Send Message');

Menu::messages();

$container = Page::create('message_send_processing.php');

if (isset($var['receiver'])) {
	$container->addVar('receiver');
	$template->assign('Receiver', SmrPlayer::getPlayer($var['receiver'], $player->getGameID()));
} else {
	$template->assign('Receiver', 'All Online');
}

$template->assign('MessageSendFormHref', $container->href());

if (isset($var['preview'])) {
	$template->assign('Preview', $var['preview']);
}
