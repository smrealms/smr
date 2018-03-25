<?php

$template->assign('PageTopic', 'Jump Drive');
require_once(get_file_loc('menu.inc'));
create_nav_menu($template, $player);

$targetSector = SmrSector::getSector($player->getGameID(), $var['target']);
$jumpInfo = $player->getJumpInfo($targetSector);

$template->assign('Target', $targetSector->getSectorID());
$template->assign('TurnCost', $jumpInfo['turn_cost']);
$template->assign('MaxMisjump', $jumpInfo['max_misjump']);

$container = create_container('sector_jump_processing.php');
$container['target'] = $targetSector->getSectorID();
$container['target_page'] = 'current_sector.php';
$template->assign('JumpProcessingHREF', SmrSession::getNewHREF($container));

?>
