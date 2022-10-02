<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

if (!$player->getGame()->hasStarted()) {
	create_error('You cannot leave newbie protection before the game begins!');
}

$template->assign('PageTopic', 'Leave Newbie Protection');
