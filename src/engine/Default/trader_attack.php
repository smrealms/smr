<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$template->assign('TraderCombatResults', $var['results']);
if ($var['target']) {
	$template->assign('Target', SmrPlayer::getPlayer($var['target'], $player->getGameID()));
}
if (isset($var['override_death'])) {
	$template->assign('OverrideDeath', true);
} else {
	$template->assign('OverrideDeath', false);
}
