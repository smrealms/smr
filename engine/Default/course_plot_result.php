<?php declare(strict_types=1);

$path = $var['Path'];
$fullPath = $var['FullPath'];

$template->assign('PageTopic', 'Plot A Course');
Menu::navigation($template, $player);

$template->assign('Path', $path);
$template->assign('FullPath', $fullPath);
