<?
require_once(get_file_loc('SmrPort.class.inc'));
$smarty->assign('PageTopic','PORT RAID');
$PHP_OUTPUT.=('<font color=red>WARNING WARNING</font> port assault about to commence!!<br />');
$PHP_OUTPUT.=('Are you sure you want to attack this port?<br /><br />');
$port =& SmrPort::getPort($player->getGameID(),$player->getSectorID());
$time = TIME;

//PAGE
//if ($port->getReinforceTime() < $time) {
//
//	//defences restock (check for fed arrival)
//	$minsToStay = 30;
//	if ($port->getReinforceTime() + $minsToStay * 60 > $time)
//		$federal_mod = ($time - $port->getReinforceTime() - $minsToStay * 60) / (-6 * $minsToStay);
//	else $federal_mod = 0;
//	if ($federal_mod < 0) $federal_mod = 0;
//	if ($federal_mod > 0) $PHP_OUTPUT.=('Ships dispatched by the Federal Government have just arrived and are in a defensive position around the port.<br />');
//	$rich_mod = floor( $port->getCredits() * 1e-7 );
//	if($rich_mod < 0) $rich_mod = 0;
//	$port->shields = round(($port->getLevel() * 1000 + 1000) + ($rich_mod * 500) + ($federal_mod * 500));
//	$port->armor = round(($port->getLevel() * 1000 + 1000) + ($rich_mod * 500) + ($federal_mod * 500));
//	$port->drones = round(($port->getLevel() * 100 + 100) + ($rich_mod * 50) + ($federal_mod * 50));
//    $port->update();
//	
//}
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
   	$PHP_OUTPUT.=('and 1 plate of armor.');
   else
   	$PHP_OUTPUT.=('and '.$port->getArmour().' plates of armor.');

}

$container = array();
$container['url'] = 'port_attack_processing_new.php';
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