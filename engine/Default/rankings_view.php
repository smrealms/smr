<?php

$template->assign('PageTopic', 'Extended User Rankings');
if (SmrSession::hasGame()) {
	Menu::trader();
}
