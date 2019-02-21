<?php

$alliance = $player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceName(false, true));
Menu::alliance($player->getAllianceID(), $alliance->getLeaderID());

$container = create_container('alliance_leadership_processing.php');
$template->assign('HandoverHREF', SmrSession::getNewHREF($container));

$template->assign('AlliancePlayers', $alliance->getMembers());
