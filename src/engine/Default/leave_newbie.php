<?php declare(strict_types=1);

if (!$player->getGame()->hasStarted()) {
	create_error('You cannot leave newbie protection before the game begins!');
}

$template->assign('PageTopic', 'Leave Newbie Protection');
