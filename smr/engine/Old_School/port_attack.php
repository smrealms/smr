<?
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);
//kill the dead people
/*
$dead_traders = $var['dead_traders'];
foreach ($dead_traders as $acc_id) {

	$dead_acc =& SmrPlayer::getPlayer($acc_id, $player->getGameID());
	$dead_acc->died_by_port();
	$dead_ship = new SMR_SHIP($acc_id, $player->getGameID());
	$dead_ship->get_pod();

}*/
//make sure the planet is actually alive
if (!isset($var['dead'])) {

	$PHP_OUTPUT.=('<p><big><b>Port Results</b></big></p>');

	foreach ($var['portdamage'] as $msg) {

		//all this info comes from the last page look there for more info
		//this is the planet shooting.
		$PHP_OUTPUT.=($msg.'<br>');

	}

	// port shot show the pic
	$PHP_OUTPUT.=('<p><img src="images/creonti_cruiser.jpg"></p>');
	$PHP_OUTPUT.=('<p><big><b>Attacker Results</b></big></p>');

	//now we need to get the player shooting results
	foreach ($var['attacker'] as $playerdamage) {

		foreach ($playerdamage as $msg) {

			//same as above...came from port_attack_processing.php
			//players have shot here
			$PHP_OUTPUT.=($msg.'<br>');

		}

	}

} else
	$PHP_OUTPUT.=('The port has no more defences!<br><br>');

//now we have all the info we need so lets check if this player can fire again
if ($sector->hasPort())
{
	require_once(get_file_loc('SmrPort.class.inc'));
	$port =& SmrPort::getPort($player->getGameID(),$player->getSectorID());
	if ($port->shields > 0 || $port->drones > 0 || $port->armor > 0) {

		//we can fire again
		$PHP_OUTPUT.=create_echo_form(create_container('port_attack_processing.php', ''));
		$PHP_OUTPUT.=create_submit('Continue Attack (3)');
		$PHP_OUTPUT.=('</form>');

	} else {

		//we can now claim
		$PHP_OUTPUT.=create_echo_form(create_container('port_claim_processing.php', ''));
		$PHP_OUTPUT.=create_submit('Claim the port for your race');
		$PHP_OUTPUT.=('</form>');

		$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'port_loot.php'));
		$PHP_OUTPUT.=create_submit('Loot the port');
		$PHP_OUTPUT.=('</form>');

	}

}

?>