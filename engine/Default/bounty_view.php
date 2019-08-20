<?php declare(strict_types=1);
$bountyPlayer = SmrPlayer::getPlayer($var['id'], $player->getGameID());
$template->assign('PageTopic', 'Viewing ' . $bountyPlayer->getPlayerName());
$template->assign('BountyPlayer', $bountyPlayer);
