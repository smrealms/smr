<?php
		require_once(get_file_loc("smr_port.inc"));
print_topic("PORT RAID");
print("<font color=red>WARNING WARNING</font> port assault about to commence!!<br>");
print("Are you sure you want to attack this port?<br><br>");
$port = new SMR_PORT($player->sector_id, $player->game_id);
$time = time();
if ($port->refresh_defense < $time) {

	//defences restock (check for fed arrival)
	$minsToStay = 30;
	if ($port->refresh_defense + $minsToStay * 60 > $time)
		$federal_mod = ($time - $port->refresh_defense - $minsToStay * 60) / (-6 * $minsToStay);
	else $federal_mod = 0;
	if ($federal_mod < 0) $federal_mod = 0;
	if ($federal_mod > 0) print("Ships dispatched by the Federal Government have just arrived and are in a defensive position around the port.<br>");
	$rich_mod = floor( $port->credits * 1e-7 );
	if($rich_mod < 0) $rich_mod = 0;
	$port->shields = round(($port->level * 1000 + 1000) + ($rich_mod * 500) + ($federal_mod * 500));
	$port->armor = round(($port->level * 1000 + 1000) + ($rich_mod * 500) + ($federal_mod * 500));
	$port->drones = round(($port->level * 100 + 100) + ($rich_mod * 50) + ($federal_mod * 50));
    $port->update();

}
if ($ship->hardware[HARDWARE_SCANNER] == 1) {

	//they can scan the port
   print("Your scanners detect that there ");
   if ($port->shields == 1)
   	print("is 1 shield, ");
   else
   	print("are $port->shields shields, ");
   if ($port->drones == 1)
   	print("1 combat drone, ");
   else
   	print("$port->drones combat drones, ");
   if ($port->armor == 1)
   	print("and 1 plate of armor.");
   else
   	print("and $port->armor plates of armor.");

}

$container = array();
$container["url"] = "port_attack_processing_new.php";
print_form($container);
print_submit("Yes");
print("</form>");
print("  ");
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "current_sector.php";
print_form($container);
print_submit("No");
print("</form>");

?>