<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if (Request::has('ignore_globals')) {
	$player->setIgnoreGlobals(Request::get('ignore_globals') == 'Yes');
} elseif (Request::has('group_scouts')) {
	$player->setGroupScoutMessages(strtoupper(Request::get('group_scouts')));
}

$container = Page::create('skeleton.php', 'message_view.php');
$container->addVar('folder_id');
$container->go();
