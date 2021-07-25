<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if (Smr\Request::has('ignore_globals')) {
	$player->setIgnoreGlobals(Smr\Request::get('ignore_globals') == 'Yes');
} elseif (Smr\Request::has('group_scouts')) {
	$player->setGroupScoutMessages(strtoupper(Smr\Request::get('group_scouts')));
}

$container = Page::create('skeleton.php', 'message_view.php');
$container->addVar('folder_id');
$container->go();
