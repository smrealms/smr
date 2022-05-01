<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$template->assign('FullForceCombatResults', $var['results']);

if ($var['owner_id'] > 0) {
	$template->assign('Target', SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']));
}

$template->assign('OverrideDeath', $player->isDead());
