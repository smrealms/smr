<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();
$sector = $player->getSector();

if (!$sector->hasPort()) {
	create_error('This sector does not have a port.');
}
$port = $sector->getPort();

if ($port->isDestroyed()) {
	Page::create('port_attack.php')->go();
}

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Port Raid');

$template->assign('PortAttackHREF', Page::create('port_attack_processing.php')->href());
$template->assign('Port', $port);

$eligibleAttackers = $sector->getFightingTradersAgainstPort($player, $port, allEligible: true);
$template->assign('VisiblePlayers', $eligibleAttackers);
$template->assign('SectorPlayersLabel', 'Attackers');
