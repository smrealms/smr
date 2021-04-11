<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('PageTopic', 'Delete Game - Confirmation');

$session->getRequestVarInt('delete_game_id');
$template->assign('Game', SmrGame::getGame($var['delete_game_id']));

$container = Page::create('game_delete_processing.php');
$container->addVar('delete_game_id');
$template->assign('ProcessingHREF', $container->href());
