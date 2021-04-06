<?php declare(strict_types=1);

$session = Smr\Session::getInstance();

$template->assign('PageTopic', 'Extended User Rankings');
if ($session->hasGame()) {
	Menu::trader();
}
