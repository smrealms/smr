<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();
$port = $player->getSector()->getPort();

if (isset($var['results'])) {
	$template->assign('FullPortCombatResults', $var['results']);
	$template->assign('AlreadyDestroyed', false);
	$template->assign('CreditedAttacker', true);
} else {
	$template->assign('AlreadyDestroyed', true);
	$template->assign('CreditedAttacker', in_array($player, $port->getAttackersToCredit()));
}
$template->assign('MinimalDisplay', false);

if (isset($var['override_death'])) {
	$template->assign('OverrideDeath', true);
} else {
	$template->assign('OverrideDeath', false);
}
$template->assign('Port', $port);
