<?php declare(strict_types=1);

$alliance = $player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

$container = Page::create('alliance_leave_processing.php');
$container['action'] = 'YES';
$template->assign('YesHREF', $container->href());

$container['action'] = 'NO';
$template->assign('NoHREF', $container->href());
