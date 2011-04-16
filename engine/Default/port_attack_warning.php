<?php
require_once(get_file_loc('SmrPort.class.inc'));
$template->assign('PageTopic','Port Raid');
$PHP_OUTPUT.='<span class="red">WARNING WARNING</span> port assault about to commence!!<br />';
$PHP_OUTPUT.='Are you sure you want to attack this port?<br /><br />';
$port =& SmrPort::getPort($player->getGameID(),$player->getSectorID());

if ($ship->hasScanner())
{
	//they can scan the port
	$PHP_OUTPUT.='Your scanners detect that there ';
   	$PHP_OUTPUT.='are '.$port->getShields().' shields, ';
	$PHP_OUTPUT.=($port->getShields()==1?'is':'are').' <span id="portShields">'.$port->getShields().'</span> shield'.($port->getShields()!=1?'s':'').', ';
	$PHP_OUTPUT.='and <span id="portCDs">'.$port->getCDs().'</span> combat drone'.($port->getCDs()!=1?'s':'').', ';
	$PHP_OUTPUT.='and <span id="portArmour">'.$port->getArmour().'</span> plate'.($port->getArmour()!=1?'s':'').' of armour.';
}

$container = create_container('port_attack_processing.php');
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=create_submit('Yes');
$PHP_OUTPUT.='</form>';
$PHP_OUTPUT.='  ';
$container = create_container('skeleton.php', 'current_sector.php');
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=create_submit('No');
$PHP_OUTPUT.='</form>';

?>