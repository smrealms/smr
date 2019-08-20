<?php declare(strict_types=1);

$alliance = $player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceName(false, true));
Menu::alliance($alliance->getAllianceID(), $alliance->getLeaderID());

$container = create_container('alliance_leave_processing.php');
$container['action'] = 'YES';
$template->assign('YesHREF', SmrSession::getNewHREF($container));

$container['action'] = 'NO';
$template->assign('NoHREF', SmrSession::getNewHREF($container));
