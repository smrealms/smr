<?php

if(!$sector->hasPort()) {
	create_error('This sector does not have a port.');
}

if($sector->getPort()->isDestroyed()) {
	forward(create_container('skeleton.php', 'port_attack.php'));
}

$template->assign('PageTopic','Port Raid');

$template->assign('PortAttackHREF',SmrSession::getNewHREF(create_container('port_attack_processing.php')));
