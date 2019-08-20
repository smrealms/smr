<?php declare(strict_types=1);

$template->assign('PageTopic', 'Extended User Rankings');
if (SmrSession::hasGame()) {
	Menu::trader();
}
