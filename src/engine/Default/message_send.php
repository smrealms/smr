<?php declare(strict_types=1);

$template->assign('PageTopic', 'Send Message');

Menu::messages();

$container = Page::create('message_send_processing.php');
$container->addVar('receiver');
$template->assign('MessageSendFormHref', $container->href());

if (!empty($var['receiver'])) {
	$template->assign('Receiver', SmrPlayer::getPlayer($var['receiver'], $player->getGameID()));
} else {
	$template->assign('Receiver', 'All Online');
}
if (isset($var['preview'])) {
	$template->assign('Preview', $var['preview']);
}
