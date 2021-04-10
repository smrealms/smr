<?php declare(strict_types=1);

if (!$sector->hasPort()) {
	create_error('This sector does not have a port.');
}

if ($sector->getPort()->isDestroyed()) {
	Page::create('skeleton.php', 'port_attack.php')->go();
}

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Port Raid');

$template->assign('PortAttackHREF', Page::create('port_attack_processing.php')->href());
$template->assign('Port', $sector->getPort());
