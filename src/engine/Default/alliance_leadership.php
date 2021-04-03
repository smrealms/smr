<?php declare(strict_types=1);

$alliance = $player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($player->getAllianceID());

$container = Page::create('alliance_leadership_processing.php');
$template->assign('HandoverHREF', $container->href());

$template->assign('AlliancePlayers', $alliance->getMembers());
