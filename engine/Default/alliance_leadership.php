<?php

$alliance = $player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceName(false, true));
require_once(get_file_loc('menu.inc'));
create_alliance_menu($player->getAllianceID(),$alliance->getLeaderID());

$container = create_container('alliance_leadership_processing.php');
$template->assign('HandoverHREF', SmrSession::getNewHREF($container));

$template->assign('AlliancePlayers', $alliance->getMembers());
$template->assign('ThisPlayer', $player);

?>
