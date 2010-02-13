<?php
require_once(get_file_loc('SmrPort.class.inc'));
$template->assign('PageTopic','Port Raid');
$PHP_OUTPUT.=('<span class="red">WARNING WARNING</span> port assault about to commence!!<br />');
$PHP_OUTPUT.=('Are you sure you want to attack this port?<br /><br />');
$port =& SmrPort::getPort($player->getGameID(),$player->getSectorID());
$time = TIME;

if ($ship->hasScanner() == 1)
{
	//they can scan the port
   $PHP_OUTPUT.=('Your scanners detect that there ');
   if ($port->getShields() == 1)
   	$PHP_OUTPUT.=('is 1 shield, ');
   else
   	$PHP_OUTPUT.=('are '.$port->getShields().' shields, ');
   if ($port->getCDs() == 1)
   	$PHP_OUTPUT.=('1 combat drone, ');
   else
   	$PHP_OUTPUT.=($port->getCDs().' combat drones, ');
   if ($port->getArmour() == 1)
   	$PHP_OUTPUT.=('and 1 plate of armour.');
   else
   	$PHP_OUTPUT.=('and '.$port->getArmour().' plates of armour.');

}

$container = array();
$container['url'] = 'port_attack_processing.php';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=create_submit('Yes');
$PHP_OUTPUT.=('</form>');
$PHP_OUTPUT.=('  ');
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'current_sector.php';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=create_submit('No');
$PHP_OUTPUT.=('</form>');

?>