<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('PageTopic', 'Jump Drive');
Menu::navigation($player);

$targetSector = SmrSector::getSector($player->getGameID(), $var['target']);
$jumpInfo = $player->getJumpInfo($targetSector);

$template->assign('Target', $targetSector->getSectorID());
$template->assign('TurnCost', $jumpInfo['turn_cost']);
$template->assign('MaxMisjump', $jumpInfo['max_misjump']);

$container = Page::create('sector_jump_processing.php');
$container['target'] = $targetSector->getSectorID();
$container['target_page'] = 'current_sector.php';
$template->assign('JumpProcessingHREF', $container->href());
