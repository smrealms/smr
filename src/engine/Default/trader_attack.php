<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$template->assign('TraderCombatResults', $var['results']);
if ($var['target']) {
	$template->assign('Target', SmrPlayer::getPlayer($var['target'], $player->getGameID()));
}
$template->assign('OverrideDeath', $player->isDead());
