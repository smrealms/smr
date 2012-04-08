<?php

if(!$sector->hasPort()) {
	create_error('This sector does not have a port.');
}

$template->assign('PageTopic','Port Raid');

$template->assign('PortAttackHREF',SmrSession::getNewHREF(create_container('port_attack_processing.php')));

?>