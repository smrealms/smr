<?php declare(strict_types=1);

$session = SmrSession::getInstance();

$template->assign('PageTopic', 'Extended User Rankings');
if ($session->hasGame()) {
	Menu::trader();
}
