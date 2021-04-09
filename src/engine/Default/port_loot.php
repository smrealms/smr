<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Looting The Port');
$template->assign('ThisPort', $player->getSectorPort());
