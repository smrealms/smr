<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();

$template->assign('PageTopic', 'Extended User Rankings');
if ($session->hasGame()) {
	Menu::trader();
}
