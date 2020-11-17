<?php declare(strict_types=1);

// Use this exception to help override container forwarding for NPC's
class ForwardException extends Exception {}

function overrideForward($container) {
	global $forwardedContainer;
	$forwardedContainer = $container;
	if ($container['body'] == 'error.php') {
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

// Raise exceptions for all types of errors for improved error reporting
// and to attempt to shut down the NPCs cleanly on errors.
set_error_handler("exception_error_handler");

const SHIP_UPGRADE_PATH = array(
	RACE_ALSKANT => array(
		SHIP_TYPE_TRADE_MASTER,
		SHIP_TYPE_TRIP_MAKER,
		SHIP_TYPE_SMALL_TIMER
	),
	RACE_CREONTI => array(
		SHIP_TYPE_LEVIATHAN,
		SHIP_TYPE_MEDIUM_CARGO_HULK
	),
	RACE_HUMAN => array(
		SHIP_TYPE_AMBASSADOR,
		SHIP_TYPE_RENAISSANCE,
		SHIP_TYPE_LIGHT_FREIGHTER
	),
	RACE_IKTHORNE => array(
		SHIP_TYPE_FAVOURED_OFFSPRING,
		SHIP_TYPE_PROTO_CARRIER,
		SHIP_TYPE_TINY_DELIGHT
	),
	RACE_SALVENE => array(
		SHIP_TYPE_DRUDGE,
		SHIP_TYPE_HATCHLINGS_DUE
	),
	RACE_THEVIAN => array(
		SHIP_TYPE_EXPEDITER,
		SHIP_TYPE_SWIFT_VENTURE
	),
	RACE_WQHUMAN => array(
		SHIP_TYPE_BLOCKADE_RUNNER,
		SHIP_TYPE_NEGOTIATOR,
		SHIP_TYPE_SLIP_FREIGHTER
	),
	RACE_NIJARIN => array(
		SHIP_TYPE_VENGEANCE,
		SHIP_TYPE_REDEEMER
	)
);


try {
	MySqlDatabase::getInstance();
	debug('Script started');

	// Make sure NPC's have been set up in the database
	$db->query('SELECT 1 FROM npc_logins LIMIT 1');
	if (!$db->nextRecord()) {
		debug('No NPCs have been created yet!');
		exit;
	}

	try {
		changeNPCLogin();
	} catch (ForwardException $e) {}

	NPCStuff();
} catch (Throwable $e) {
	logException($e);
	// Try to shut down cleanly
	exitNPC();
}


function NPCStuff() {
	global $actions, $var, $previousContainer, $db, $player;

	$underAttack = false;
	$actions = -1;

	while (true) {
		// Clear the $_REQUEST global, in case we had set it, to avoid
		// contaminating subsequent page processing.
		$_REQUEST = [];

		$actions++;

		// Avoid infinite loops by restricting the number of actions
		if ($actions > NPC_MAX_ACTIONS) {
			debug('Reached maximum number of actions: ' . NPC_MAX_ACTIONS);
			changeNPCLogin();
		}

		try {
			$TRADE_ROUTE =& $GLOBALS['TRADE_ROUTE'];
			debug('Action #' . $actions);

			//We have to reload player on each loop
			$player = SmrPlayer::getPlayer(SmrSession::getAccountID(), SmrSession::getGameID(), true);
			// Sanity check to be certain we actually have an NPC
			if (!$player->isNPC()) {
				throw new Exception('Player is not an NPC!');
			}
			$player->updateTurns();

			if ($actions == 0) {
				if ($player->getTurns() <= mt_rand($player->getMaxTurns() / 2, $player->getMaxTurns()) && ($player->hasNewbieTurns() || $player->hasFederalProtection())) {
					debug('We don\'t have enough turns to bother starting trading, and we are protected: ' . $player->getTurns());
					changeNPCLogin();
				}

				// Ensure the NPC doesn't think it's under attack at startup,
				// since this could cause it to get stuck in a loop in Fed.
				$player->getShip()->removeUnderAttack();
				$player->getShip()->updateHardware();
			}

			if (!isset($TRADE_ROUTE)) { //We only want to change trade route if there isn't already one set.
				$TRADE_ROUTES =& findRoutes($player);
				$TRADE_ROUTE =& changeRoute($TRADE_ROUTES);
			}

			if ($player->isDead()) {
				debug('Some evil person killed us, let\'s move on now.');
				$previousContainer = null; //We died, we don't care what we were doing beforehand.
				$TRADE_ROUTE =& changeRoute($TRADE_ROUTES); //Change route
				processContainer(create_container('death_processing.php'));
			}
			if ($player->getNewbieTurns() <= NEWBIE_TURNS_WARNING_LIMIT && $player->getNewbieWarning()) {
				processContainer(create_container('newbie_warning_processing.php'));
			}

			$fedContainer = null;
			if (isset($var['url']) && $var['url'] == 'shop_ship_processing.php' && ($fedContainer = plotToFed($player, true)) !== true) { //We just bought a ship, we should head back to our trade gal/uno - we use HQ for now as it's both in our gal and a UNO, plus it's safe which is always a bonus
				processContainer($fedContainer);
			} elseif ($player->getShip()->isUnderAttack() === true
				&&($player->hasPlottedCourse() === false || $player->getPlottedCourse()->getEndSector()->offersFederalProtection() === false)
				&&($fedContainer == null ? $fedContainer = plotToFed($player, true) : $fedContainer) !== true) { //We're under attack and need to plot course to fed.
				// Get the lock, remove under attack and update.
				acquire_lock($player->getSectorID());
				$ship = $player->getShip(true);
				$ship->removeUnderAttack();
				$ship->updateHardware();
				release_lock();

				debug('Under Attack');
				$underAttack = true;
				processContainer($fedContainer);
			} elseif ($player->hasPlottedCourse() === true && $player->getPlottedCourse()->getEndSector()->offersFederalProtection()) { //We have a route to fed to follow, figure it's probably a damned sensible thing to follow.
				debug('Follow Course: ' . $player->getPlottedCourse()->getNextOnPath());
				processContainer(moveToSector($player, $player->getPlottedCourse()->getNextOnPath()));
			} elseif (($container = canWeUNO($player, true)) !== false) { //We have money and are at a uno, let's uno!
				debug('We\'re UNOing');
				processContainer($container);
			} elseif ($player->hasPlottedCourse() === true) { //We have a route to follow, figure it's probably a sensible thing to follow.
				debug('Follow Course: ' . $player->getPlottedCourse()->getNextOnPath());
				processContainer(moveToSector($player, $player->getPlottedCourse()->getNextOnPath()));
			} elseif ($player->getTurns() < NPC_LOW_TURNS || ($player->getTurns() < $player->getMaxTurns() / 2 && $player->getNewbieTurns() < NPC_LOW_NEWBIE_TURNS) || $underAttack) { //We're low on turns or have been under attack and need to plot course to fed
				if ($player->getTurns() < NPC_LOW_TURNS) {
					debug('Low Turns:' . $player->getTurns());
				}
				if ($underAttack) {
					debug('Fedding after attack.');
				}
				if ($player->hasNewbieTurns()) { //We have newbie turns, we can just wait here.
					debug('We have newbie turns, let\'s just switch to another NPC.');
					changeNPCLogin();
				}
				if ($player->hasFederalProtection()) {
					debug('We are in fed, time to switch to another NPC.');
					changeNPCLogin();
				}
				$ship = $player->getShip();
				processContainer(plotToFed($player, !$ship->hasMaxShields() || !$ship->hasMaxArmour() || !$ship->hasMaxCargoHolds()));
			} elseif (($container = checkForShipUpgrade($player)) !== false) { //We have money and are at a uno, let's uno!
				debug('We\'re reshipping!');
				processContainer($container);
			} elseif (($container = canWeUNO($player, false)) !== false) { //We need to UNO and have enough money to do it properly so let's do it sooner rather than later.
				debug('We need to UNO, so off we go!');
				processContainer($container);
			} elseif ($TRADE_ROUTE instanceof \Routes\Route) {
				debug('Trade Route');
				$forwardRoute = $TRADE_ROUTE->getForwardRoute();
				$returnRoute = $TRADE_ROUTE->getReturnRoute();
				if ($forwardRoute->getBuySectorId() == $player->getSectorID() || $returnRoute->getBuySectorId() == $player->getSectorID()) {
					if ($forwardRoute->getBuySectorId() == $player->getSectorID()) {
						$buyRoute = $forwardRoute;
						$sellRoute = $returnRoute;
					} elseif ($returnRoute->getBuySectorId() == $player->getSectorID()) {
						$buyRoute = $returnRoute;
						$sellRoute = $forwardRoute;
					}

					$ship = $player->getShip();
					if ($ship->getUsedHolds() > 0) {
						if ($ship->hasCargo($sellRoute->getGoodID())) { //Sell goods
							$goodID = $sellRoute->getGoodID();

							$port = $player->getSector()->getPort();
							$tradeable = checkPortTradeable($port, $player);

							if ($tradeable === true && $port->getGoodAmount($goodID) >= $ship->getCargo($sellRoute->getGoodID())) { //TODO: Sell what we can rather than forcing sell all at once?
								//Sell goods
								debug('Sell Goods');
								processContainer(tradeGoods($goodID, $player, $port));
							} else {
								//Move to next route or fed.
								if (($TRADE_ROUTE =& changeRoute($TRADE_ROUTES)) === false) {
									debug('Changing Route Failed');
									processContainer(plotToFed($player));
								} else {
									debug('Route Changed');
									continue;
								}
							}
						} elseif ($ship->hasCargo($buyRoute->getGoodID()) === true) { //We've bought goods, plot to sell
							debug('Plot To Sell: ' . $buyRoute->getSellSectorId());
							processContainer(plotToSector($player, $buyRoute->getSellSectorId()));
						} else {
							//Dump goods
							debug('Dump Goods');
							processContainer(dumpCargo($player));
						}
					} else { //Buy goods
						$goodID = $buyRoute->getGoodID();

						$port = $player->getSector()->getPort();
						$tradeable = checkPortTradeable($port, $player);

						if ($tradeable === true && $port->getGoodAmount($goodID) >= $ship->getEmptyHolds()) { //Buy goods
							debug('Buy Goods');
							processContainer(tradeGoods($goodID, $player, $port));
						} else {
							//Move to next route or fed.
							if (($TRADE_ROUTE =& changeRoute($TRADE_ROUTES)) === false) {
								debug('Changing Route Failed');
								processContainer(plotToFed($player));
							} else {
								debug('Route Changed');
								continue;
							}
						}
					}
				} else {
					debug('Plot To Buy: ' . $forwardRoute->getBuySectorId());
					processContainer(plotToSector($player, $forwardRoute->getBuySectorId()));
				}
			} else { //Something weird is going on.. Let's fed and wait.
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
		} catch (ForwardException $e) {
			global $lock;
			if ($lock) { //only save if we have the lock.
				SmrSector::saveSectors();
				SmrShip::saveShips();
				SmrPlayer::savePlayers();
				SmrForce::saveForces();
				SmrPort::savePorts();
				if (class_exists('WeightedRandom', false)) {
					WeightedRandom::saveWeightedRandoms();
				}
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
	global $player, $var, $db;
	echo date('Y-m-d H:i:s - ') . $message . ($debugObject !== null ?EOL.var_export($debugObject, true) : '') . EOL;
	if (NPC_LOG_TO_DATABASE) {
		$db->query('INSERT INTO npc_logs (script_id, npc_id, time, message, debug_info, var) VALUES (' . (defined('SCRIPT_ID') ?SCRIPT_ID:0) . ', ' . (is_object($player) ? $player->getAccountID() : 0) . ',NOW(),' . $db->escapeString($message) . ',' . $db->escapeString(var_export($debugObject, true)) . ',' . $db->escapeString(var_export($var, true)) . ')');

		// On the first call to debug, we need to update the script_id retroactively
		if (!defined('SCRIPT_ID')) {
			define('SCRIPT_ID', $db->getInsertID());
			$db->query('UPDATE npc_logs SET script_id=' . SCRIPT_ID . ' WHERE log_id=' . SCRIPT_ID);
		}
	}
}

function processContainer($container) {
	global $forwardedContainer, $previousContainer, $player;
	if ($container == $previousContainer && $forwardedContainer['body'] != 'forces_attack.php') {
		debug('We are executing the same container twice?', array('ForwardedContainer' => $forwardedContainer, 'Container' => $container));
		if ($player->hasNewbieTurns() || $player->hasFederalProtection()) {
			// Only throw the exception if we have protection, otherwise let's hope that the NPC will be able to find its way to safety rather than dying in the open.
			throw new Exception('We are executing the same container twice?');
		}
	}
	clearCaches(); //Clear caches of anything we have used for decision making before processing container and getting lock.
	$previousContainer = $container;
	debug('Executing container', $container);
 	//Redefine MICRO_TIME and TIME, the rest of the game expects them to be the single point in time that the script is executing, with it being redefined for each page load - unfortunately NPCs are one consistent script so we have to do a hack and redefine it (or change every instance of the TIME constant.
	uopz_redefine('MICRO_TIME', microtime(true));
	uopz_redefine('TIME', IFloor(MICRO_TIME));
	resetContainer($container);
	acquire_lock($player->getSectorID()); // Lock now to skip var update in do_voodoo
	do_voodoo();
}

function sleepNPC() {
	usleep(mt_rand(MIN_SLEEP_TIME, MAX_SLEEP_TIME)); //Sleep for a random time
}

// Releases an NPC when it is done working
function releaseNPC() {
	if (!SmrSession::hasAccount()) {
		debug('releaseNPC: no NPC to release');
		return;
	}
	$login = SmrSession::getAccount()->getLogin();
	MySqlDatabase::getInstance();
	$db->query('UPDATE npc_logins SET working=' . $db->escapeBoolean(false) . ' WHERE login=' . $db->escapeString($login));
	if ($db->getChangedRows() > 0) {
		debug('Released NPC: ' . $login);
	} else {
		debug('Failed to release NPC: ' . $login);
	}
	SmrSession::destroy();
}

function exitNPC() {
	debug('Exiting NPC script.');
	releaseNPC();
	release_lock();
	exit;
}

function changeNPCLogin() {
	global $actions, $previousContainer;
	if ($actions > 0) {
		debug('We have taken actions and now want to change NPC, let\'s exit and let next script choose a new NPC to reset execution time', getrusage());
		exitNPC();
	}

	$actions = -1;
	$GLOBALS['TRADE_ROUTE'] = null;

	// Release previous NPC, if any
	releaseNPC();

	// We chose a new NPC, we don't care what we were doing beforehand.
	$previousContainer = null;

	// Lacking a convenient way to get up-to-date turns, order NPCs by how
	// recently they have taken an action.
	debug('Choosing new NPC');
	static $availableNpcs = null;
	MySqlDatabase::getInstance();
	if (is_null($availableNpcs)) {
		// Make sure to select NPCs from active games only
		$db->query('SELECT account_id, game_id FROM player JOIN account USING(account_id) JOIN npc_logins USING(login) JOIN game USING(game_id) WHERE active=\'TRUE\' AND working=\'FALSE\' AND start_time < ' . $db->escapeNumber(TIME) . ' AND end_time > ' . $db->escapeNumber(TIME) . ' ORDER BY last_turn_update ASC');
		while ($db->nextRecord()) {
			$availableNpcs[] = [
				'account_id' => $db->getInt('account_id'),
				'game_id' => $db->getInt('game_id'),
			];
		}
	}

	if (empty($availableNpcs)) {
		debug('No free NPCs');
		exitNPC();
	}

	// Pop an NPC off the top of the stack to activate
	$npc = array_shift($availableNpcs);

	// Update session info for this chosen NPC
	$account = SmrAccount::getAccount($npc['account_id']);
	SmrSession::init();
	SmrSession::setAccount($account);
	SmrSession::updateGame($npc['game_id']);

	$db->query('UPDATE npc_logins SET working=' . $db->escapeBoolean(true) . ' WHERE login=' . $db->escapeString($account->getLogin()));
	debug('Chosen NPC: ' . $account->getLogin() . ' (game ' . SmrSession::getGameID() . ')');

	throw new ForwardException;
}

function canWeUNO(AbstractSmrPlayer $player, $oppurtunisticOnly) {
	if ($player->getCredits() < MINUMUM_RESERVE_CREDITS) {
		return false;
	}
	$ship = $player->getShip();
	if ($ship->hasMaxShields() && $ship->hasMaxArmour() && $ship->hasMaxCargoHolds()) {
		return false;
	}
	$sector = $player->getSector();

	// We buy armour in preference to shields as it's cheaper.
	// We buy cargo holds last if we have no newbie turns because we'd rather not die
	$hardwareArray = array(HARDWARE_ARMOUR, HARDWARE_SHIELDS, HARDWARE_CARGO);

	$amount = 0;

	foreach ($sector->getLocations() as $location) {
		if ($location->isHardwareSold()) {
			$hardwareSold = $location->getHardwareSold();
			if ($player->getNewbieTurns() > MIN_NEWBIE_TURNS_TO_BUY_CARGO && !$ship->hasMaxCargoHolds() && isset($hardwareSold[HARDWARE_CARGO]) && ($amount = floor(($player->getCredits() - MINUMUM_RESERVE_CREDITS) / Globals::getHardwareCost(HARDWARE_CARGO))) > 0) { // Buy cargo holds first if we have plenty of newbie turns left.
				$hardwareID = HARDWARE_CARGO;
			} else {
				foreach ($hardwareArray as $hardwareArrayID) {
					if (!$ship->hasMaxHardware($hardwareArrayID) && isset($hardwareSold[$hardwareArrayID]) && ($amount = floor(($player->getCredits() - MINUMUM_RESERVE_CREDITS) / Globals::getHardwareCost($hardwareArrayID))) > 0) {
						$hardwareID = $hardwareArrayID;
						break;
					}
				}
			}
			if (isset($hardwareID)) {
				return doUNO($hardwareID, min($ship->getMaxHardware($hardwareID) - $ship->getHardware($hardwareID), $amount));
			}
		}
	}

	if ($oppurtunisticOnly === true) {
		return false;
	}

	if ($player->getCredits() - $ship->getCostToUNO() < MINUMUM_RESERVE_CREDITS) {
		return false; //Only do non-oppurtunistic UNO if we have the money to do it properly!
	}

	foreach ($hardwareArray as $hardwareArrayID) {
		if (!$ship->hasMaxHardware($hardwareArrayID)) {
			$hardwareNeededID = $hardwareArrayID;
			return plotToNearest($player, Globals::getHardwareTypes($hardwareArrayID));
		}
	}
}

function doUNO($hardwareID, $amount) {
	debug('Buying ' . $amount . ' units of "' . Globals::getHardwareName($hardwareID) . '"');
	$_REQUEST = [
		'amount' => $amount,
		'action' => 'Buy',
	];
	return create_container('shop_hardware_processing.php', '', array('hardware_id'=>$hardwareID));
}

function tradeGoods($goodID, AbstractSmrPlayer $player, SmrPort $port) {
	sleepNPC(); //We have an extra sleep at port to make the NPC more vulnerable.
	$ship = $player->getShip();
	$relations = $player->getRelation($port->getRaceID());

	$transaction = $port->getGoodTransaction($goodID);

	if ($transaction == 'Buy') {
		$amount = $ship->getEmptyHolds();
	} else {
		$amount = $ship->getCargo($goodID);
	}

	$idealPrice = $port->getIdealPrice($goodID, $transaction, $amount, $relations);
	$offeredPrice = $port->getOfferPrice($idealPrice, $relations, $transaction);

	$_REQUEST = ['action' => $transaction];
	return create_container('shop_goods_processing.php', '', array('offered_price'=>$offeredPrice, 'ideal_price'=>$idealPrice, 'amount'=>$amount, 'good_id'=>$goodID, 'bargain_price'=>$offeredPrice));
}

function dumpCargo($player) {
	$ship = $player->getShip();
	$cargo = $ship->getCargo();
	debug('Ship Cargo', $cargo);
	foreach ($cargo as $goodID => $amount) {
		if ($amount > 0) {
			return create_container('cargo_dump_processing.php', '', array('good_id'=>$goodID, 'amount'=>$amount));
		}
	}
}

function plotToSector($player, $sectorID) {
	return create_container('course_plot_processing.php', '', array('from'=>$player->getSectorID(), 'to'=>$sectorID));
}

function plotToFed($player, $plotToHQ = false) {
	debug('Plotting To Fed', $plotToHQ);

	// Always drop illegal goods before heading to fed space
	if ($player->getShip()->hasIllegalGoods()) {
		debug('Dumping illegal goods');
		processContainer(dumpCargo($player));
	}

	$fedLocID = $player->getRaceID() + ($plotToHQ ? LOCATION_GROUP_RACIAL_HQS : LOCATION_GROUP_RACIAL_BEACONS);
	if ($player->getSector()->hasLocation($fedLocID)) {
		debug('Plotted to fed whilst in fed, switch NPC and wait for turns');
		changeNPCLogin();
		return true;
	}
	return plotToNearest($player, SmrLocation::getLocation($fedLocID));
}

function plotToNearest(AbstractSmrPlayer $player, $realX) {
	debug('Plotting To: ', $realX); //TODO: Can we make the debug output a bit nicer?

	if ($player->getSector()->hasX($realX)) { //Check if current sector has what we're looking for before we attempt to plot and get error.
		debug('Already available in sector');
		return true;
	}

	return create_container('course_plot_nearest_processing.php', '', array('RealX'=>$realX));
}
function moveToSector($player, $targetSector) {
	debug('Moving from #' . $player->getSectorID() . ' to #' . $targetSector);
	return create_container('sector_move_processing.php', '', array('target_sector'=>$targetSector, 'target_page'=>''));
}

function checkForShipUpgrade(AbstractSmrPlayer $player) {
	foreach (SHIP_UPGRADE_PATH[$player->getRaceID()] as $upgradeShipID) {
		if ($player->getShipTypeID() == $upgradeShipID) {
			//We can't upgrade, only downgrade.
			return false;
		}
		$cost = $player->getShip()->getCostToUpgrade($upgradeShipID);
		if ($cost <= 0 || $player->getCredits() - $cost > MINUMUM_RESERVE_CREDITS) {
			return doShipUpgrade($player, $upgradeShipID);
		}
	}
	debug('Could not find a ship on the upgrade path.');
	return false;
}

function doShipUpgrade(AbstractSmrPlayer $player, $upgradeShipID) {
	$plotNearest = plotToNearest($player, AbstractSmrShip::getBaseShip(Globals::getGameType($player->getGameID()), $upgradeShipID));

	if ($plotNearest == true) { //We're already there!
		//TODO: We're going to want to UNO after upgrading
		return create_container('shop_ship_processing.php', '', array('ship_id'=>$upgradeShipID));
	} //Otherwise return the plot
	return $plotNearest;
}

function &changeRoute(array &$tradeRoutes) {
	$false = false;
	if (count($tradeRoutes) == 0) {
		return $false;
	}
	$routeKey = array_rand($tradeRoutes);
	$tradeRoute =& $tradeRoutes[$routeKey];
	unset($tradeRoutes[$routeKey]);
	$GLOBALS['TRADE_ROUTE'] =& $tradeRoute;
	debug('Switched route', $tradeRoute);
	return $tradeRoute;
}

function &findRoutes($player) {
	debug('Finding Routes');

	$tradeGoods = array(GOODS_NOTHING => false);
	foreach (Globals::getGoods() as $goodID => $good) {
		if ($player->meetsAlignmentRestriction($good['AlignRestriction'])) {
			$tradeGoods[$goodID] = true;
		} else {
			$tradeGoods[$goodID] = false;
		}
	}

	// Only allow NPCs to trade at ports of their race and neutral ports
	$tradeRaces = array();
	foreach (Globals::getRaces() as $raceID => $race) {
		$tradeRaces[$raceID] = false;
	}
	$tradeRaces[$player->getRaceID()] = true;
	$tradeRaces[RACE_NEUTRAL] = true;

	$galaxy = $player->getSector()->getGalaxy();

	$maxNumberOfPorts = 2;
	$routesForPort = -1;
	$numberOfRoutes = 1000;
	$maxDistance = 15;

	$startSectorID = $galaxy->getStartSector();
	$endSectorID = $galaxy->getEndSector();

	MySqlDatabase::getInstance();
	$db->query('SELECT routes FROM route_cache WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' AND max_ports=' . $db->escapeNumber($maxNumberOfPorts) . ' AND goods_allowed=' . $db->escapeObject($tradeGoods) . ' AND races_allowed=' . $db->escapeObject($tradeRaces) . ' AND start_sector_id=' . $db->escapeNumber($startSectorID) . ' AND end_sector_id=' . $db->escapeNumber($endSectorID) . ' AND routes_for_port=' . $db->escapeNumber($routesForPort) . ' AND max_distance=' . $db->escapeNumber($maxDistance));
	if ($db->nextRecord()) {
		$routes = unserialize(gzuncompress($db->getField('routes')));
		debug('Using Cached Routes: #' . count($routes));
		return $routes;
	} else {
		debug('Generating Routes');
		$allSectors = array();
		foreach (SmrGalaxy::getGameGalaxies($player->getGameID()) as $galaxy) {
			$allSectors += $galaxy->getSectors(); //Merge arrays
		}

		$distances = Plotter::calculatePortToPortDistances($allSectors, $maxDistance, $startSectorID, $endSectorID);

		if ($maxNumberOfPorts == 1) {
			$allRoutes = \Routes\RouteGenerator::generateOneWayRoutes($allSectors, $distances, $tradeGoods, $tradeRaces, $routesForPort);
		} else {
			$allRoutes = \Routes\RouteGenerator::generateMultiPortRoutes($maxNumberOfPorts, $allSectors, $tradeGoods, $tradeRaces, $distances, $routesForPort, $numberOfRoutes);
		}

		unset($distances);

		$routesMerged = array();
		foreach ($allRoutes[\Routes\RouteGenerator::EXP_ROUTE] as $multi => $routesByMulti) {
			$routesMerged += $routesByMulti; //Merge arrays
		}

		unset($allSectors);
		SmrPort::clearCache();
		SmrSector::clearCache();

		if (count($routesMerged) == 0) {
			debug('Could not find any routes! Try another NPC.');
			changeNPCLogin();
		}

		$db->query('INSERT INTO route_cache ' .
				'(game_id, max_ports, goods_allowed, races_allowed, start_sector_id, end_sector_id, routes_for_port, max_distance, routes)' .
				' VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($maxNumberOfPorts) . ', ' . $db->escapeObject($tradeGoods) . ', ' . $db->escapeObject($tradeRaces) . ', ' . $db->escapeNumber($startSectorID) . ', ' . $db->escapeNumber($endSectorID) . ', ' . $db->escapeNumber($routesForPort) . ', ' . $db->escapeNumber($maxDistance) . ', ' . $db->escapeObject($routesMerged, true) . ')');
		debug('Found Routes: #' . count($routesMerged));
		return $routesMerged;
	}
}
