<?php declare(strict_types=1);

$template->assign('PageTopic', 'Delete Game - Confirmation');

SmrSession::getRequestVar('delete_game_id');
$template->assign('Game', SmrGame::getGame($var['delete_game_id']));

$container = create_container('game_delete_processing.php');
transfer('delete_game_id');
$template->assign('ProcessingHREF', SmrSession::getNewHREF($container));
