<?php

// Use this exception to help override container forwarding for NPC's
class ForwardException extends Exception {}

function overrideForward($container) {
	global $forwardedContainer;
	$forwardedContainer = $container;
	if($container['body']=='error.php') {
		// We hit a create_error - this shouldn't happen for an NPC often,
		// for now we want to throw an exception for it for testing.
		debug('Hit an error');
		throw new Exception($container['message']);
	}
	// We have to throw the exception to get back up the stack,
	// otherwise we quickly hit problems of overflowing the stack.
	throw new ForwardException;
}
const OVERRIDE_FORWARD = true;

// Must be defined before anything that might throw an exception
const NPC_SCRIPT = true;

// UOPZ overrides exit by default, which we don't want
uopz_allow_exit(true);

// global config
require_once(realpath(dirname(__FILE__)) . '/../../htdocs/config.inc');
// bot config
require_once(CONFIG . 'npc/config.specific.php');
// needed libs
require_once(get_file_loc('smr.inc'));
require_once(get_file_loc('shop_goods.inc'));

const SHIP_UPGRADE_PATH = array(
	2 => array( //Alskant
		SHIP_TYPE_TRADE_MASTER,
		SHIP_TYPE_TRIP_MAKER,
		SHIP_TYPE_NEWBIE_MERCHANT_VESSEL,
		SHIP_TYPE_SMALL_TIMER
	),
	3 => array( //Creonti
		SHIP_TYPE_LEVIATHAN,
		SHIP_TYPE_NEWBIE_MERCHANT_VESSEL,
		SHIP_TYPE_MEDIUM_CARGO_HULK
	),
	4 => array( //Human
		SHIP_TYPE_AMBASSADOR,
		SHIP_TYPE_NEWBIE_MERCHANT_VESSEL,
		SHIP_TYPE_RENAISSANCE,
		SHIP_TYPE_LIGHT_FREIGHTER
	),
	5 => array( //Ik'Thorne
		SHIP_TYPE_FAVOURED_OFFSPRING,
		SHIP_TYPE_NEWBIE_MERCHANT_VESSEL,
		SHIP_TYPE_PROTO_CARRIER,
		SHIP_TYPE_TINY_DELIGHT
	),
	6 => array( //Salvene
		SHIP_TYPE_DRUDGE,
		SHIP_TYPE_NEWBIE_MERCHANT_VESSEL,
		SHIP_TYPE_HATCHLINGS_DUE
	),
	7 => array( //Thevian
		SHIP_TYPE_EXPEDITER,
		SHIP_TYPE_NEWBIE_MERCHANT_VESSEL,
		SHIP_TYPE_SWIFT_VENTURE
	),
	8 => array( //WQ Human
		SHIP_TYPE_BLOCKADE_RUNNER,
		SHIP_TYPE_NEWBIE_MERCHANT_VESSEL,
		SHIP_TYPE_NEGOTIATOR,
		SHIP_TYPE_SLIP_FREIGHTER
	),
	9 => array( //Nijarin
		SHIP_TYPE_VENGEANCE,
		SHIP_TYPE_NEWBIE_MERCHANT_VESSEL,
		SHIP_TYPE_REDEEMER
	)
);


try {
	$db = new SmrMySqlDatabase();
	debug('Script started');

	define('SCRIPT_ID', $db->getInsertID());
	$db->query('UPDATE npc_logs SET script_id='.SCRIPT_ID.' WHERE log_id='.SCRIPT_ID);

	$NPC_LOGINS_USED = array('');

	$HIDDEN_PLAYERS = array();

	// Make sure NPC's have been set up in the database
	$db = new SmrMySqlDatabase();
	$db->query('SELECT 1 FROM npc_logins LIMIT 1');
	if (!$db->nextRecord()) {
		debug('No NPCs have been created yet!');
		exit;
	}

	try {
		changeNPCLogin();
	}
	catch(ForwardException $e) {}

	NPCStuff();
}
catch(Throwable $e) {
	logException($e);
	// Try to shut down cleanly
	exitNPC();
}


function NPCStuff() {
	global $actions,$var,$previousContainer,$underAttack,$NPC_LOGIN,$db;

	$underAttack = false;
	$actions=-1;

	while(true) {
		$actions++;
		try {
			$TRADE_ROUTE =& $GLOBALS['TRADE_ROUTE'];
			debug('Action #'.$actions);

			SmrSession::updateGame(NPC_GAME_ID);

			debug('Getting player for account id: '.SmrSession::$account_id);
			//We have to reload player on each loop
			$player = SmrPlayer::getPlayer(SmrSession::$account_id, SmrSession::getGameID(), true);
			$player->updateTurns();

			if($actions==0) {
				if($player->getAllianceName() != $NPC_LOGIN['AllianceName']) {
					// dirty hack so we can revisit the init block here on next iteration
					$actions--;

					if($player->hasAlliance())
						processContainer(leaveAlliance());

					// figure out if the selected alliance already exist
					$db->query('SELECT alliance_id FROM alliance WHERE alliance_name='.$db->escapeString($NPC_LOGIN['AllianceName']).' AND game_id='.$db->escapeNumber(SmrSession::getGameID()));
					if ($db->nextRecord()) {
						processContainer(joinAlliance($db->getField('alliance_id'),'*--NPCS--*'));
					}
					else {
						processContainer(createAlliance($NPC_LOGIN['AllianceName'],'*--NPCS--*'));
					}
				}
				if($player->getTurns() <= mt_rand($player->getMaxTurns() / 2, $player->getMaxTurns()) && ($player->hasNewbieTurns() || $player->hasFederalProtection())) {
					debug('We don\'t have enough turns to bother starting trading, and we are protected: '.$player->getTurns());
					changeNPCLogin();
				}
			}

			if(!isset($TRADE_ROUTE)) { //We only want to change trade route if there isn't already one set.
				$TRADE_ROUTES =& findRoutes($player);
				$TRADE_ROUTE =& changeRoute($TRADE_ROUTES);
			}

			if($player->isDead()) {
				debug('Some evil person killed us, let\'s move on now.');
				$previousContainer = null; //We died, we don't care what we were doing beforehand.
				$TRADE_ROUTE =& changeRoute($TRADE_ROUTES); //Change route
				processContainer(create_container('death_processing.php'));
			}
			if($player->getNewbieTurns() <= NEWBIE_TURNS_WARNING_LIMIT && $player->getNewbieWarning()) {
				processContainer(create_container('newbie_warning_processing.php'));
			}

			$fedContainer = null;
			if($var['url']=='shop_ship_processing.php'&&($fedContainer = plotToFed($player,true))!==true) { //We just bought a ship, we should head back to our trade gal/uno - we use HQ for now as it's both in our gal and a UNO, plus it's safe which is always a bonus
				processContainer($fedContainer);
			}
			else if($player->getShip()->isUnderAttack()===true
				&&($player->hasPlottedCourse()===false||$player->getPlottedCourse()->getEndSector()->offersFederalProtection()===false)
				&&($fedContainer==null?$fedContainer = plotToFed($player,true):$fedContainer)!==true) { //We're under attack and need to plot course to fed.
				// Get the lock, remove under attack and update.
				acquire_lock($player->getSectorID());
				$ship =& $player->getShip(true);
				$ship->removeUnderAttack();
				$ship->updateHardware();
				release_lock();

				debug('Under Attack');
				$underAttack = true;
				processContainer($fedContainer);
			}
			else if($player->hasPlottedCourse()===true&&$player->getPlottedCourse()->getEndSector()->offersFederalProtection()) { //We have a route to fed to follow, figure it's probably a damned sensible thing to follow.
				debug('Follow Course: '.$player->getPlottedCourse()->getNextOnPath());
				processContainer(moveToSector($player,$player->getPlottedCourse()->getNextOnPath()));
			}
			else if(($container = canWeUNO($player,true))!==false) { //We have money and are at a uno, let's uno!
				debug('We\'re UNOing');
				processContainer($container);
			}
			else if($player->hasPlottedCourse()===true) { //We have a route to follow, figure it's probably a sensible thing to follow.
				debug('Follow Course: '.$player->getPlottedCourse()->getNextOnPath());
				processContainer(moveToSector($player,$player->getPlottedCourse()->getNextOnPath()));
			}
			else if($player->getTurns()<NPC_LOW_TURNS || ($player->getTurns() < $player->getMaxTurns() / 2 && $player->getNewbieTurns()<NPC_LOW_NEWBIE_TURNS) || $underAttack) { //We're low on turns or have been under attack and need to plot course to fed
				if($player->getTurns()<NPC_LOW_TURNS) {
					debug('Low Turns:'.$player->getTurns());
				}
				if($underAttack) {
					debug('Fedding after attack.');
				}
				if($player->hasNewbieTurns()) { //We have newbie turns, we can just wait here.
					debug('We have newbie turns, let\'s just switch to another NPC.');
					changeNPCLogin();
				}
				if($player->hasFederalProtection()) {
					debug('We are in fed, time to switch to another NPC.');
					changeNPCLogin();
				}
				$ship =& $player->getShip();
				processContainer(plotToFed($player,!$ship->hasMaxShields()||!$ship->hasMaxArmour()||!$ship->hasMaxCargoHolds()));
			}
			else if(($container = checkForShipUpgrade($player))!==false) { //We have money and are at a uno, let's uno!
				debug('We\'re reshipping!');
				processContainer($container);
			}
			else if(($container = canWeUNO($player,false))!==false) { //We need to UNO and have enough money to do it properly so let's do it sooner rather than later.
				debug('We need to UNO, so off we go!');
				processContainer($container);
			}
			else if($TRADE_ROUTE instanceof Route) {
				debug('Trade Route');
				$forwardRoute =& $TRADE_ROUTE->getForwardRoute();
				$returnRoute =& $TRADE_ROUTE->getReturnRoute();
				if($forwardRoute->getBuySectorId()==$player->getSectorID()||$returnRoute->getBuySectorId()==$player->getSectorID()) {
					if($forwardRoute->getBuySectorId()==$player->getSectorID()) {
						$buyRoute =& $forwardRoute;
						$sellRoute =& $returnRoute;
					}
					else if($returnRoute->getBuySectorId()==$player->getSectorID()) {
						$buyRoute =& $returnRoute;
						$sellRoute =& $forwardRoute;
					}

					$ship =& $player->getShip();
					if($ship->getUsedHolds()>0) {
						if($ship->hasCargo($sellRoute->getGoodID())) { //Sell goods
							$goodID = $sellRoute->getGoodID();

							$port =& $player->getSector()->getPort();
							$tradeable = checkPortTradeable($port,$player);

							if($tradeable===true && $port->getGoodAmount($goodID)>=$ship->getCargo($sellRoute->getGoodID())) { //TODO: Sell what we can rather than forcing sell all at once?
								//Sell goods
								debug('Sell Goods');
								processContainer(tradeGoods($goodID,$player,$port));
							}
							else {
								//Move to next route or fed.
								if(($TRADE_ROUTE =& changeRoute($TRADE_ROUTES))===false) {
									debug('Changing Route Failed');
									processContainer(plotToFed($player));
								}
								else {
									debug('Route Changed');
									continue;
								}
							}
						}
						else if($ship->hasCargo($buyRoute->getGoodID())===true) { //We've bought goods, plot to sell
							debug('Plot To Sell: '.$buyRoute->getSellSectorId());
							processContainer(plotToSector($player,$buyRoute->getSellSectorId()));
						}
						else {
							//Dump goods
							debug('Dump Goods');
							processContainer(dumpCargo($player));
						}
					}
					else { //Buy goods
						$goodID = $buyRoute->getGoodID();

						$port =& $player->getSector()->getPort();
						$tradeable = checkPortTradeable($port,$player);

						if($tradeable===true && $port->getGoodAmount($goodID)>=$ship->getEmptyHolds()) { //Buy goods
							debug('Buy Goods');
							processContainer(tradeGoods($goodID,$player,$port));
						}
						else {
							//Move to next route or fed.
							if(($TRADE_ROUTE =& changeRoute($TRADE_ROUTES))===false) {
								debug('Changing Route Failed');
								processContainer(plotToFed($player));
							}
							else {
								debug('Route Changed');
								continue;
							}
						}
					}
				}
				else {
					debug('Plot To Buy: '.$forwardRoute->getBuySectorId());
					processContainer(plotToSector($player,$forwardRoute->getBuySectorId()));
				}
			}
			else { //Something weird is going on.. Let's fed and wait.
				debug('No actual action? Wtf?');
				processContainer(plotToFed($player));
			}
			/*
			else { //Otherwise let's run around at random.
				$links = $player->getSector()->getLinks();
				$moveTo = $links[array_rand($links)];
				debug('Random Wanderings: '.$moveTo);
				processContainer(moveToSector($player,$moveTo));
			}
			*/
		}
		catch(ForwardException $e) {
			global $lock;
			if($lock) { //only save if we have the lock.
				SmrSector::saveSectors();
				SmrShip::saveShips();
				SmrPlayer::savePlayers();
				SmrForce::saveForces();
				SmrPort::savePorts();;
				if(class_exists('WeightedRandom', false))
					WeightedRandom::saveWeightedRandoms();
				release_lock();
			}
			//Clean up the caches as the data may get changed by other players
			clearCaches();
			//Clear up some global vars
			global $locksFailed;
			$locksFailed = array();
			$_REQUEST = array();
			//Have a sleep between actions
			sleepNPC();
		}
	}
	debug('Actions Finished.');
	exitNPC();
}

function clearCaches() {
	SmrSector::clearCache();
	SmrPlayer::clearCache();
	SmrShip::clearCache();
	SmrForce::clearCache();
	SmrPort::clearCache();
}

function debug($message, $debugObject = null) {
	global $account,$var,$db;
	echo date('Y-m-d H:i:s - ').$message.($debugObject!==null?EOL.var_export($debugObject,true):'').EOL;
	$db->query('INSERT INTO npc_logs (script_id, npc_id, time, message, debug_info, var) VALUES ('.(defined('SCRIPT_ID')?SCRIPT_ID:0).', '.(is_object($account)?$account->getAccountID():0).',NOW(),'.$db->escapeString($message).','.$db->escapeString(var_export($debugObject,true)).','.$db->escapeString(var_export($var,true)).')');
}

function processContainer($container) {
	global $forwardedContainer, $previousContainer, $player;
	if($container == $previousContainer && $forwardedContainer['body'] != 'forces_attack.php') {
		debug('We are executing the same container twice?', array('ForwardedContainer' => $forwardedContainer, 'Container' => $container));
		if($player->hasNewbieTurns() || $player->hasFederalProtection()) {
			// Only throw the exception if we have protection, otherwise let's hope that the NPC will be able to find its way to safety rather than dying in the open.
			throw new Exception('We are executing the same container twice?');
		}
	}
	clearCaches(); //Clear caches of anything we have used for decision making before processing container and getting lock.
	$previousContainer = $container;
	debug('Executing container',$container);
 	//Redefine MICRO_TIME and TIME, the rest of the game expects them to be the single point in time that the script is executing, with it being redefined for each page load - unfortunately NPCs are one consistent script so we have to do a hack and redefine it (or change every instance of the TIME constant.
	uopz_redefine('MICRO_TIME', microtime(true));
	uopz_redefine('TIME', intval(MICRO_TIME));
	resetContainer($container);
	do_voodoo();
}

function sleepNPC() {
	usleep(mt_rand(MIN_SLEEP_TIME,MAX_SLEEP_TIME)); //Sleep for a random time
}

// Releases an NPC when it is done working
function releaseNPC($login) {
	if (empty($login)) {
		debug('releaseNPC: no login specified to release');
	} else {
		$db = new SmrMySqlDatabase();
		$db->query('UPDATE npc_logins SET working='.$db->escapeBoolean(false).' WHERE login='.$db->escapeString($login));
		if ($db->getChangedRows()>0) {
			debug('Released NPC: '.$login);
		} else {
			debug('Failed to release NPC: '.$login);
		}
	}
}

function exitNPC() {
	global $NPC_LOGIN;
	debug('Exiting NPC script.');
	releaseNPC($NPC_LOGIN['Login']);
	release_lock();
	exit;
}

function changeNPCLogin() {
	global $NPC_LOGIN,$actions,$NPC_LOGINS_USED,$underAttack,$previousContainer;
	if($actions > 0) {
		debug('We have taken actions and now want to change NPC, let\'s exit and let next script choose a new NPC to reset execution time', getrusage ());
		exitNPC();
	}

	$actions=-1;
	$GLOBALS['TRADE_ROUTE'] = null;

	// Release previous NPC, if any
	releaseNPC($NPC_LOGIN['Login']);
	$NPC_LOGIN = null;

	// We chose a new NPC, we don't care what we were doing beforehand.
	$previousContainer = null;

	debug('Choosing new NPC');
	$db = new SmrMySqlDatabase();
	$db2 = new SmrMySqlDatabase();
	$db->query('SELECT login, npc.player_name, alliance_name
				FROM npc_logins npc
				LEFT JOIN account a USING(login)
				LEFT JOIN player p ON a.account_id = p.account_id AND p.game_id = ' . $db->escapeNumber(NPC_GAME_ID) . '
				WHERE active=' . $db->escapeBoolean(true) . ' AND working=' . $db->escapeBoolean(false) . ' AND login NOT IN (' . $db->escapeArray($NPC_LOGINS_USED) . ')
				ORDER BY (turns IS NOT NULL), turns DESC');
	while($db->nextRecord()) {
		$db2->query('UPDATE npc_logins SET working='.$db2->escapeBoolean(true).' WHERE login='.$db2->escapeString($db->getField('login')).' AND working='.$db2->escapeBoolean(false));
		if($db2->getChangedRows()>0) {
			$NPC_LOGIN = array(
					'Login' => $db->getField('login'),
					'PlayerName' => $db->getField('player_name'),
					'AllianceName' => $db->getField('alliance_name')
			);
			break;
		}
	}
	$NPC_LOGINS_USED[] = $NPC_LOGIN['Login'];

	if($NPC_LOGIN===null) {
		debug('No free NPCs');
		exitNPC();
	}
	debug('Chosen NPC: '.$NPC_LOGIN['Login']);

	if(SmrAccount::getAccountByName($NPC_LOGIN['Login'])==null) {
		debug('Creating account for: '.$NPC_LOGIN['Login']);
		$account = SmrAccount::createAccount($NPC_LOGIN['Login'],'','NPC@smrealms.de','NPC','NPC',0,0);
		$account->setValidated(true);
	}
	else {
		$account = SmrAccount::getAccountByName($NPC_LOGIN['Login']);
	}

	$GLOBALS['account'] =& $account;
	SmrSession::$account_id = $account->getAccountID();
	$underAttack = false;

	//Auto-create player if need be.
	$db->query('SELECT 1 FROM player WHERE account_id = '.$account->getAccountID().' AND game_id = '.NPC_GAME_ID.' LIMIT 1');
	if(!$db->nextRecord()) {
		SmrSession::updateGame(0); //Have to be out of game to join game.
		debug('Auto-creating player: '.$account->getLogin());
		processContainer(joinGame(SmrSession::getGameID(), $NPC_LOGIN['PlayerName']));
	}

	throw new ForwardException;
}

function canWeUNO(AbstractSmrPlayer &$player, $oppurtunisticOnly) {
	if($player->getCredits()<MINUMUM_RESERVE_CREDITS)
		return false;
	$ship =& $player->getShip();
	if($ship->hasMaxShields()&&$ship->hasMaxArmour()&&$ship->hasMaxCargoHolds())
		return false;
	$sector =& $player->getSector();

	// We buy armour in preference to shields as it's cheaper.
	// We buy cargo holds last if we have no newbie turns because we'd rather not die
	$hardwareArray = array(HARDWARE_ARMOUR,HARDWARE_SHIELDS,HARDWARE_CARGO);

	$amount = 0;

	$locations =& $sector->getLocations();
	foreach($locations as &$location) {
		if($location->isHardwareSold()) {
			$hardwareSold =& $location->getHardwareSold();
			if($player->getNewbieTurns() > MIN_NEWBIE_TURNS_TO_BUY_CARGO && !$ship->hasMaxCargoHolds() && isset($hardwareSold[HARDWARE_CARGO]) && ($amount = floor(($player->getCredits()-MINUMUM_RESERVE_CREDITS)/Globals::getHardwareCost(HARDWARE_CARGO))) > 0) { // Buy cargo holds first if we have plenty of newbie turns left.
				$hardwareID = HARDWARE_CARGO;
			}
			else {
				foreach($hardwareArray as $hardwareArrayID) {
					if(!$ship->hasMaxHardware($hardwareArrayID) && isset($hardwareSold[$hardwareArrayID]) && ($amount = floor(($player->getCredits()-MINUMUM_RESERVE_CREDITS)/Globals::getHardwareCost($hardwareArrayID))) > 0) {
						$hardwareID = $hardwareArrayID;
						break;
					}
				}
			}
			if(isset($hardwareID)) {
				return doUNO($hardwareID,min($ship->getMaxHardware($hardwareID)-$ship->getHardware($hardwareID),$amount));
			}
		}
	}

	if($oppurtunisticOnly===true)
		return false;

	if($player->getCredits()-$ship->getCostToUNO()<MINUMUM_RESERVE_CREDITS)
		return false; //Only do non-oppurtunistic UNO if we have the money to do it properly!

	foreach($hardwareArray as $hardwareArrayID) {
		if(!$ship->hasMaxHardware($hardwareArrayID)) {
			$hardwareNeededID = $hardwareArrayID;
			return plotToNearest($player, Globals::getHardwareTypes($hardwareArrayID));
		}
	}
}

function doUNO($hardwareID,$amount) {
	debug('Buying '.$amount.' units of "'.Globals::getHardwareName($hardwareID).'"');
	$_REQUEST['amount'] = $amount;
	return create_container('shop_hardware_processing.php','',array('hardware_id'=>$hardwareID));
}

function tradeGoods($goodID,AbstractSmrPlayer &$player,SmrPort &$port) {
	sleepNPC(); //We have an extra sleep at port to make the NPC more vulnerable.
	$ship =& $player->getShip();
	$relations = $player->getRelation($port->getRaceID());

	$transaction = $port->getGoodTransaction($goodID);

	if ($transaction == 'Buy') {
		$amount = $ship->getEmptyHolds();
	} else {
		$amount = $ship->getCargo($goodID);
	}

	$idealPrice = $port->getIdealPrice($goodID, $transaction, $amount, $relations);
	$offeredPrice = $port->getOfferPrice($idealPrice, $relations, $transaction);

	return create_container('shop_goods_processing.php','',array('offered_price'=>$offeredPrice,'ideal_price'=>$idealPrice,'amount'=>$amount,'good_id'=>$goodID,'bargain_price'=>$offeredPrice));
}

function dumpCargo(&$player) {
	$ship =& $player->getShip();
	$cargo =& $ship->getCargo();
	debug('Ship Cargo',$cargo);
	foreach($cargo as $goodID => $amount) {
		if($amount > 0) {
			return create_container('cargo_dump_processing.php','',array('good_id'=>$goodID,'amount'=>$amount));
		}
	}
}

function plotToSector(&$player,$sectorID) {
	return create_container('course_plot_processing.php','',array('from'=>$player->getSectorID(),'to'=>$sectorID));
}

function plotToFed(&$player,$plotToHQ=false) {
	debug('Plotting To Fed',$plotToHQ);

	if($plotToHQ === false && $player->getSector()->offersFederalProtection()) {
		if(!$player->hasNewbieTurns() && !$player->hasFederalProtection() && $player->getShip()->hasIllegalGoods()) { //We have illegals and no newbie turns, dump the illegals to get fed protection.
			debug('Dumping illegals');
			processContainer(dumpCargo($player));
		}
		debug('Plotted to fed whilst in fed, switch NPC and wait for turns');
		changeNPCLogin();
		return true;
	}
	if($plotToHQ===true) {
		return plotToNearest($player,SmrLocation::getLocation($player->getRaceID()+LOCATION_GROUP_RACIAL_HQS));
	}
	return plotToNearest($player,SmrLocation::getLocation($player->getRaceID()+LOCATION_GROUP_RACIAL_BEACONS));
//	return plotToNearest($player,$plotToHQ===true?'HQ':'Fed');
}

function plotToNearest(AbstractSmrPlayer &$player, &$realX) {
	debug('Plotting To: ',$realX); //TODO: Can we make the debug output a bit nicer?

	if($player->getSector()->hasX($realX)) { //Check if current sector has what we're looking for before we attempt to plot and get error.
		debug('Already available in sector');
		return true;
	}

	return create_container('course_plot_nearest_processing.php','',array('RealX'=>$realX));
}
function moveToSector(&$player,$targetSector) {
	debug('Moving from #'.$player->getSectorID().' to #'.$targetSector);
	return create_container('sector_move_processing.php','',array('target_sector'=>$targetSector,'target_page'=>''));
}

function checkForShipUpgrade(AbstractSmrPlayer &$player) {
	foreach(SHIP_UPGRADE_PATH[$player->getRaceID()] as $upgradeShipID) {
		if($player->getShipTypeID()==$upgradeShipID) //We can't upgrade, only downgrade.
			return false;
		if($upgradeShipID == SHIP_TYPE_NEWBIE_MERCHANT_VESSEL) //We can't actually buy the NMV, we just don't want to downgrade from it if we have it.
			continue;
		$cost = $player->getShip()->getCostToUpgrade($upgradeShipID);
		if($cost <= 0 || $player->getCredits()-$cost > MINUMUM_RESERVE_CREDITS) {
			return doShipUpgrade($player, $upgradeShipID);
		}
	}
	debug('Could not find a ship on the upgrade path.');
	return false;
}

function doShipUpgrade(AbstractSmrPlayer &$player,$upgradeShipID) {
	$plotNearest = plotToNearest($player,AbstractSmrShip::getBaseShip(Globals::getGameType($player->getGameID()),$upgradeShipID));

	if($plotNearest == true) { //We're already there!
		//TODO: We're going to want to UNO after upgrading
		return create_container('shop_ship_processing.php','',array('ship_id'=>$upgradeShipID));
	} //Otherwise return the plot
	return $plotNearest;
}

function &changeRoute(array &$tradeRoutes) {
	$false = false;
	if(count($tradeRoutes)==0)
		return $false;
	$routeKey = array_rand($tradeRoutes);
	$tradeRoute =& $tradeRoutes[$routeKey];
	unset($tradeRoutes[$routeKey]);
	$GLOBALS['TRADE_ROUTE'] =& $tradeRoute;
	debug('Switched route',$tradeRoute);
	return $tradeRoute;
}

function joinGame($gameID,$playerName) {
	global $NPC_LOGIN;
	debug('Creating player for: '.$NPC_LOGIN['Login']);
	$races = Globals::getRaces();
	while(($raceID = array_rand($races))===1); //Random race that's not neutral.

	debug('Chosen race "'.$races[$raceID]['Race Name'].'": '.$raceID);

	$_REQUEST['player_name'] = $playerName;
	$_REQUEST['race_id'] = $raceID;

	return create_container('game_join_processing.php','',array('game_id'=>NPC_GAME_ID));
}

function joinAlliance($allianceID,$password) {
	debug('Joining alliance: '.$allianceID);
	$_REQUEST['password'] = $password;
	return create_container('alliance_join_processing.php','',array('alliance_id'=>$allianceID));
}

function createAlliance($allianceName,$password) {
	debug('Creating alliance: '.$allianceName);
	$_REQUEST['name'] = $allianceName;
	$_REQUEST['password'] = $password;
	$_REQUEST['perms'] = 'full';
	return create_container('alliance_create_processing.php');
}

function leaveAlliance() {
	debug('Leaving alliance');
	return create_container('alliance_leave_processing.php','',array('action'=>'YES'));
}

function &findRoutes(&$player) {
	debug('Finding Routes');

	$galaxies =& SmrGalaxy::getGameGalaxies($player->getGameID());

	$tradeGoods = array(GOOD_NOTHING => false);
	$goods =& Globals::getGoods();
	foreach($goods as $goodID => &$good) {
		if($player->meetsAlignmentRestriction($good['AlignRestriction']))
			$tradeGoods[$goodID] = true;
		else
			$tradeGoods[$goodID] = false;
	} unset($good);
	$tradeRaces = array();
	$races =& Globals::getRaces();
	foreach($races as $raceID => &$race) {
		$tradeRaces[$raceID] = false;
	} unset($race);
	$tradeRaces[$player->getRaceID()] = true;

	$galaxy =& $player->getSector()->getGalaxy();

	$maxNumberOfPorts = 2;
	$routesForPort=-1;
	$numberOfRoutes=1000;
	$maxDistance=15;

	$startSectorID=$galaxy->getStartSector();
	$endSectorID=$galaxy->getEndSector();

	$db = new SmrMySqlDatabase();
	$db->query('SELECT routes FROM route_cache WHERE game_id='.$db->escapeNumber($player->getGameID()).' AND max_ports='.$db->escapeNumber($maxNumberOfPorts).' AND goods_allowed='.$db->escapeObject($tradeGoods).' AND races_allowed='.$db->escapeObject($tradeRaces).' AND start_sector_id='.$db->escapeNumber($startSectorID).' AND end_sector_id='.$db->escapeNumber($endSectorID).' AND routes_for_port='.$db->escapeNumber($routesForPort).' AND max_distance='.$db->escapeNumber($maxDistance));
	if($db->nextRecord()) {
		$routes = unserialize(gzuncompress($db->getField('routes')));
		debug('Using Cached Routes: #'.count($routes));
		return $routes;
	}
	else {
		debug('Generating Routes');
		$allSectors = array();
		foreach($galaxies as &$galaxy) {
			$allSectors += $galaxy->getSectors(); //Merge arrays
		} unset($galaxy);

		$distances =& Plotter::calculatePortToPortDistances($allSectors,$maxDistance,$startSectorID,$endSectorID);


		if ($maxNumberOfPorts == 1)
			$allRoutes = RouteGenerator::generateOneWayRoutes($allSectors, $distances, $tradeGoods, $tradeRaces, $routesForPort);
		else
			$allRoutes = RouteGenerator::generateMultiPortRoutes($maxNumberOfPorts, $allSectors, $tradeGoods, $tradeRaces, $distances, $routesForPort, $numberOfRoutes);

		unset($distances);

		$allRoutes =& $allRoutes[RouteGenerator::EXP_ROUTE];
		$routesMerged = array();
		foreach($allRoutes as $multi => &$routesByMulti) {
			$routesMerged += $routesByMulti; //Merge arrays
		} unset($routesByMulti);

		unset($allSectors);
		SmrPort::clearCache();
		SmrSector::clearCache();

		if (count($routesMerged) == 0) {
			debug('Could not find any routes! Try another NPC.');
			changeNPCLogin();
		}

		$db->query('INSERT INTO route_cache ' .
				'(game_id, max_ports, goods_allowed, races_allowed, start_sector_id, end_sector_id, routes_for_port, max_distance, routes)' .
				' VALUES ('.$db->escapeNumber($player->getGameID()).', '.$db->escapeNumber($maxNumberOfPorts).', '.$db->escapeObject($tradeGoods).', '.$db->escapeObject($tradeRaces).', '.$db->escapeNumber($startSectorID).', '.$db->escapeNumber($endSectorID).', '.$db->escapeNumber($routesForPort).', '.$db->escapeNumber($maxDistance).', '.$db->escapeObject($routesMerged,true).')');
		debug('Found Routes: #'.count($routesMerged));
		return $routesMerged;
	}
}
