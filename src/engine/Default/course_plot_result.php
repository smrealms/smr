<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$path = $var['Path'];
$fullPath = $var['FullPath'];

$template->assign('PageTopic', 'Plot A Course');
Menu::navigation($player);

$template->assign('Path', $path);
$template->assign('FullPath', $fullPath);
