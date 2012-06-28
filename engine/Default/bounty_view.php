<?php
$bountyPlayer =& SmrPlayer::getPlayer($var['id'], $player->getGameID());
$template->assign('PageTopic', 'Viewing ' . $bountyPlayer->getPlayerName());
$template->assignByRef('BountyPlayer', $bountyPlayer);
?>