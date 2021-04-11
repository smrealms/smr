<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

if (isset($var['results'])) {
	$template->assign('FullPlanetCombatResults', $var['results']);
	$template->assign('AlreadyDestroyed', false);
} else {
	$template->assign('AlreadyDestroyed', true);
}
$template->assign('MinimalDisplay', false);
if (isset($var['override_death'])) {
	$template->assign('OverrideDeath', true);
} else {
	$template->assign('OverrideDeath', false);
}
$template->assign('Planet', SmrPlanet::getPlanet($player->getGameID(), $var['sector_id']));
