<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$bountyPlayer = SmrPlayer::getPlayer($var['id'], $player->getGameID());
$template->assign('PageTopic', 'Viewing Bounties');
$template->assign('BountyPlayer', $bountyPlayer);
