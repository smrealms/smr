<?php
$results = unserialize($var['results']);
$template->assign('TraderCombatResults', $results);
if ($var['target']) {
	$template->assign('Target', SmrPlayer::getPlayer($var['target'], $player->getGameID()));
}
if (isset($var['override_death'])) {
	$template->assign('OverrideDeath', true);
} else {
	$template->assign('OverrideDeath', false);
}
