<?php declare(strict_types=1);

$alliance = $player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceDisplayName(false, true));
Menu::alliance($alliance->getAllianceID());

$container = create_container('alliance_leave_processing.php');
$container['action'] = 'YES';
$template->assign('YesHREF', SmrSession::getNewHREF($container));

$container['action'] = 'NO';
$template->assign('NoHREF', SmrSession::getNewHREF($container));
