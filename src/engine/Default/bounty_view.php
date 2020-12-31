<?php declare(strict_types=1);
$bountyPlayer = SmrPlayer::getPlayer($var['id'], $player->getGameID());
$template->assign('PageTopic', 'Viewing Bounties');
$template->assign('BountyPlayer', $bountyPlayer);
