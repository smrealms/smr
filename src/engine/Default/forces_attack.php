<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$template->assign('FullForceCombatResults', $var['results']);

if ($var['owner_id'] > 0) {
	$template->assign('Target', SmrForce::getForce($player->getGameID(), $player->getSectorID(), $var['owner_id']));
}

if (isset($var['override_death'])) {
	$template->assign('OverrideDeath', true);
} else {
	$template->assign('OverrideDeath', false);
}
