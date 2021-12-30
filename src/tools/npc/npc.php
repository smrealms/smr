<?php declare(strict_types=1);

// Use this exception to help override container forwarding for NPC's
class ForwardException extends Exception {}

// Use this exception to indicate that an NPC has taken its final action
class FinalActionException extends Exception {}

function overrideForward(Page $container) : never {
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

// global config
require_once(realpath(dirname(__FILE__)) . '/../../bootstrap.php');
// bot config
require_once(CONFIG . 'npc/config.specific.php');
// needed libs
require_once(get_file_loc('smr.inc.php'));
require_once(get_file_loc('shop_goods.inc.php'));

// Raise exceptions for all types of errors for improved error reporting
// and to attempt to shut down the NPCs cleanly on errors.
set_error_handler("exception_error_handler");

const SHIP_UPGRADE_PATH = array(
	RACE_ALSKANT => array(
		SHIP_TYPE_TRADE_MASTER,
		SHIP_TYPE_DEEP_SPACER,
		SHIP_TYPE_DEAL_MAKER,
		SHIP_TYPE_TRIP_MAKER,
		SHIP_TYPE_SMALL_TIMER
	),
	RACE_CREONTI => array(
		SHIP_TYPE_DEVASTATOR,
		SHIP_TYPE_JUGGERNAUT,
		SHIP_TYPE_GOLIATH,
		SHIP_TYPE_LEVIATHAN,
		SHIP_TYPE_MEDIUM_CARGO_HULK
	),
	RACE_HUMAN => array(
		SHIP_TYPE_DESTROYER,
		SHIP_TYPE_BORDER_CRUISER,
		SHIP_TYPE_AMBASSADOR,
		SHIP_TYPE_RENAISSANCE,
		SHIP_TYPE_LIGHT_FREIGHTER
	),
	RACE_IKTHORNE => array(
		SHIP_TYPE_MOTHER_SHIP,
		SHIP_TYPE_ADVANCED_CARRIER,
		SHIP_TYPE_FAVOURED_OFFSPRING,
		SHIP_TYPE_PROTO_CARRIER,
		SHIP_TYPE_TINY_DELIGHT
	),
	RACE_SALVENE => array(
		SHIP_TYPE_EATER_OF_SOULS,
		SHIP_TYPE_RAVAGER,
		SHIP_TYPE_PREDATOR,
		SHIP_TYPE_DRUDGE,
		SHIP_TYPE_HATCHLINGS_DUE
	),
	RACE_THEVIAN => array(
		SHIP_TYPE_ASSAULT_CRAFT,
		SHIP_TYPE_CARAPACE,
		SHIP_TYPE_BOUNTY_HUNTER,
		SHIP_TYPE_EXPEDITER,
		SHIP_TYPE_SWIFT_VENTURE
	),
	RACE_WQHUMAN => array(
		SHIP_TYPE_DARK_MIRAGE,
		SHIP_TYPE_BLOCKADE_RUNNER,
		SHIP_TYPE_ROGUE,
		SHIP_TYPE_RESISTANCE,
		SHIP_TYPE_SLIP_FREIGHTER
	),
	RACE_NIJARIN => array(
		SHIP_TYPE_FURY,
		SHIP_TYPE_VINDICATOR,
		SHIP_TYPE_VENGEANCE,
		SHIP_TYPE_RETALIATION,
		SHIP_TYPE_REDEEMER
	)
);


try {
	NPCStuff();
} catch (Throwable $e) {
	logException($e);
	// Try to shut down cleanly
	exitNPC();
}


function NPCStuff() : void {
	global $previousContainer;

	$session = Smr\Session::getInstance();
	$session->setCurrentVar(new Page()); // initialize empty var

	debug('Script started');

	// Load the first available NPC
	$changeNPC = true;

	while (true) {
		if ($changeNPC) {
			changeNPCLogin();

			// Reset tracking variables
			$changeNPC = false;
			$allTradeRoutes = [];
			$tradeRoute = null;
			$underAttack = false;
			$actions = 0;

			// We chose a new NPC, we don't care what we were doing beforehand.
			$previousContainer = null;
		}

		try {
			// Avoid infinite loops by restricting the number of actions
			if ($actions > NPC_MAX_ACTIONS) {
				debug('Reached maximum number of actions: ' . NPC_MAX_ACTIONS);
				throw new FinalActionException;
			}

			debug('Action #' . $actions);

			//We have to reload player on each loop
			$player = $session->getPlayer(true);
			// Sanity check to be certain we actually have an NPC
			if (!$player->isNPC()) {
				throw new Exception('Player is not an NPC!');
			}
			$player->updateTurns();

			// Are we starting with a new NPC?
			if ($actions == 0) {
				if ($player->getTurns() <= rand($player->getMaxTurns() / 2, $player->getMaxTurns()) && ($player->hasNewbieTurns() || $player->hasFederalProtection())) {
					debug('We don\'t have enough turns to bother starting trading, and we are protected: ' . $player->getTurns());
					throw new FinalActionException;
				}

				// Ensure the NPC doesn't think it's under attack at startup,
				// since this could cause it to get stuck in a loop in Fed.
				$player->removeUnderAttack();

				// Initialize the trade route for this NPC
				$allTradeRoutes = findRoutes($player);
				$tradeRoute = changeRoute($allTradeRoutes);

				// Upgrade ships if we can
				checkForShipUpgrade($player);

				// Start the NPC with max hardware
				$player->getShip()->setHardwareToMax();

				// Equip the ship with as many lasers as it can hold
				$weaponIDs = [
					WEAPON_TYPE_PLANETARY_PULSE_LASER,
					WEAPON_TYPE_HUGE_PULSE_LASER,
					WEAPON_TYPE_HUGE_PULSE_LASER,
					WEAPON_TYPE_LARGE_PULSE_LASER,
					WEAPON_TYPE_LARGE_PULSE_LASER,
					WEAPON_TYPE_LARGE_PULSE_LASER,
					WEAPON_TYPE_LASER,
				];
				$player->getShip()->removeAllWeapons();
				while ($player->getShip()->hasOpenWeaponSlots()) {
					$weapon = SmrWeapon::getWeapon(array_shift($weaponIDs));
					$player->getShip()->addWeapon($weapon);
				}

				// Update database (not essential to have a lock here)
				$player->update();
				$player->getShip()->update();
			}

			if ($player->isDead()) {
				debug('Some evil person killed us, let\'s move on now.');
				$previousContainer = null; //We died, we don't care what we were doing beforehand.
				$tradeRoute = changeRoute($allTradeRoutes, $tradeRoute);
				processContainer(Page::create('death_processing.php'));
			}
			if ($player->getNewbieTurns() <= NEWBIE_TURNS_WARNING_LIMIT && $player->getNewbieWarning()) {
				processContainer(Page::create('newbie_warning_processing.php'));
			}

			// Do we have a plot that ends in Fed?
			$hasPlotToFed = $player->hasPlottedCourse() && SmrSector::getSector($player->getGameID(), $player->getPlottedCourse()->getEndSectorID())->offersFederalProtection();

			if (!$underAttack && $player->isUnderAttack() && !$hasPlotToFed) {
				// We're under attack and need to plot course to fed.
				debug('Under Attack');
				$underAttack = true;
				processContainer(plotToFed($player));
			} elseif ($hasPlotToFed) {
				// We have a route to fed to follow
				debug('Follow Course: ' . $player->getPlottedCourse()->getNextOnPath());
				processContainer(moveToSector($player, $player->getPlottedCourse()->getNextOnPath()));
			} elseif ($player->hasPlottedCourse()) {
				// We have a route to follow
				debug('Follow Course: ' . $player->getPlottedCourse()->getNextOnPath());
				processContainer(moveToSector($player, $player->getPlottedCourse()->getNextOnPath()));
			} elseif ($player->getTurns() < NPC_LOW_TURNS || ($player->getTurns() < $player->getMaxTurns() / 2 && $player->getNewbieTurns() < NPC_LOW_NEWBIE_TURNS) || $underAttack) {
				// We're low on turns or have been under attack and need to plot course to fed
				if ($player->getTurns() < NPC_LOW_TURNS) {
					debug('Low Turns:' . $player->getTurns());
				}
				if ($underAttack) {
					debug('Fedding after attack.');
				}
				if ($player->hasNewbieTurns()) { //We have newbie turns, we can just wait here.
					debug('We have newbie turns, let\'s just switch to another NPC.');
					throw new FinalActionException;
				}
				if ($player->hasFederalProtection()) {
					debug('We are in fed, time to switch to another NPC.');
					throw new FinalActionException;
				}
				processContainer(plotToFed($player));
			} elseif ($tradeRoute instanceof \Routes\Route) {
				debug('Trade Route');
				$forwardRoute = $tradeRoute->getForwardRoute();
				$returnRoute = $tradeRoute->getReturnRoute();
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
							$tradeRestriction = $port->getTradeRestriction($player);

							if ($tradeRestriction === false && $port->getGoodAmount($goodID) >= $ship->getCargo($sellRoute->getGoodID())) { //TODO: Sell what we can rather than forcing sell all at once?
								//Sell goods
								debug('Sell Goods');
								processContainer(tradeGoods($goodID, $player, $port));
							} else {
								//Move to next route or fed.
								if (($tradeRoute = changeRoute($allTradeRoutes)) === null) {
									debug('Changing Route Failed');
									processContainer(plotToFed($player));
								} else {
									debug('Route Changed');
									throw new ForwardException;
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
						$tradeRestriction = $port->getTradeRestriction($player);

						if ($tradeRestriction === false && $port->getGoodAmount($goodID) >= $ship->getEmptyHolds()) { //Buy goods
							debug('Buy Goods');
							processContainer(tradeGoods($goodID, $player, $port));
						} else {
							//Move to next route or fed.
							if (($tradeRoute = changeRoute($allTradeRoutes)) === null) {
								debug('Changing Route Failed');
								processContainer(plotToFed($player));
							} else {
								debug('Route Changed');
								throw new ForwardException;
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
			throw new Exception('NPC failed to perform any action');
		} catch (ForwardException $e) {
			$actions++; // we took an action
		} catch (FinalActionException $e) {
			if ($player->getSector()->offersFederalProtection() && !$player->hasFederalProtection()) {
				debug('Disarming so we can get Fed protection');
				$player->getShip()->setCDs(0);
				$player->getShip()->removeAllWeapons();
				$player->getShip()->update();
			}
			// switch to a new NPC if we haven't taken any actions yet
			if ($actions > 0) {
				debug('We have taken actions and now want to change NPC, let\'s exit and let next script choose a new NPC to reset execution time', getrusage());
				exitNPC();
			}
			$changeNPC = true;
		}

		// Save any changes that we made during this action
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

		//Clear up some global vars to avoid contaminating subsequent pages
		global $locksFailed;
		$locksFailed = array();
		$_REQUEST = array();

		//Have a sleep between actions
		sleepNPC();
	}
	debug('Actions Finished.');
	exitNPC();
}

function clearCaches() : void {
	SmrSector::clearCache();
	SmrPlayer::clearCache();
	SmrShip::clearCache();
	SmrForce::clearCache();
	SmrPort::clearCache();
}

function debug(string $message, mixed $debugObject = null) : void {
	echo date('Y-m-d H:i:s - ') . $message . ($debugObject !== null ?EOL.var_export($debugObject, true) : '') . EOL;
	if (NPC_LOG_TO_DATABASE) {
		$session = Smr\Session::getInstance();
		$accountID = $session->getAccountID();
		$var = $session->getCurrentVar();
		$db = Smr\Database::getInstance();
		$db->write('INSERT INTO npc_logs (script_id, npc_id, time, message, debug_info, var) VALUES (' . (defined('SCRIPT_ID') ?SCRIPT_ID:0) . ', ' . $accountID . ',NOW(),' . $db->escapeString($message) . ',' . $db->escapeString(var_export($debugObject, true)) . ',' . $db->escapeString(var_export($var, true)) . ')');

		// On the first call to debug, we need to update the script_id retroactively
		if (!defined('SCRIPT_ID')) {
			define('SCRIPT_ID', $db->getInsertID());
			$db->write('UPDATE npc_logs SET script_id=' . SCRIPT_ID . ' WHERE log_id=' . SCRIPT_ID);
		}
	}
}

function processContainer(Page $container) : never {
	global $forwardedContainer, $previousContainer;
	$session = Smr\Session::getInstance();
	$player = $session->getPlayer();
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
	// The next "page request" must occur at an updated time.
	Smr\Epoch::update();
	$session->setCurrentVar($container);
	acquire_lock($player->getSectorID()); // Lock now to skip var update in do_voodoo
	do_voodoo();
}

function sleepNPC() : void {
	usleep(rand(MIN_SLEEP_TIME, MAX_SLEEP_TIME)); //Sleep for a random time
}

// Releases an NPC when it is done working
function releaseNPC() : void {
	$session = Smr\Session::getInstance();
	if (!$session->hasAccount()) {
		debug('releaseNPC: no NPC to release');
		return;
	}
	$login = $session->getAccount()->getLogin();
	$db = Smr\Database::getInstance();
	$db->write('UPDATE npc_logins SET working=' . $db->escapeBoolean(false) . ' WHERE login=' . $db->escapeString($login));
	if ($db->getChangedRows() > 0) {
		debug('Released NPC: ' . $login);
	} else {
		debug('Failed to release NPC: ' . $login);
	}
}

function exitNPC() : void {
	debug('Exiting NPC script.');
	releaseNPC();
	release_lock();
	exit;
}

function changeNPCLogin() : void {
	// Release previous NPC, if any
	releaseNPC();

	// Lacking a convenient way to get up-to-date turns, order NPCs by how
	// recently they have taken an action.
	debug('Choosing new NPC');
	static $availableNpcs = null;

	$db = Smr\Database::getInstance();
	$session = Smr\Session::getInstance();

	if (is_null($availableNpcs)) {
		// Make sure NPC's have been set up in the database
		$dbResult = $db->read('SELECT 1 FROM npc_logins LIMIT 1');
		if (!$dbResult->hasRecord()) {
			debug('No NPCs have been created yet!');
			exitNPC();
		}

		// Make sure to select NPCs from active games only
		$dbResult = $db->read('SELECT account_id, game_id FROM player JOIN account USING(account_id) JOIN npc_logins USING(login) JOIN game USING(game_id) WHERE active=\'TRUE\' AND working=\'FALSE\' AND start_time < ' . $db->escapeNumber(Smr\Epoch::time()) . ' AND end_time > ' . $db->escapeNumber(Smr\Epoch::time()) . ' ORDER BY last_turn_update ASC');
		foreach ($dbResult->records() as $dbRecord) {
			$availableNpcs[] = [
				'account_id' => $dbRecord->getInt('account_id'),
				'game_id' => $dbRecord->getInt('game_id'),
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
	$session->setAccount($account);
	$session->updateGame($npc['game_id']);

	$db->write('UPDATE npc_logins SET working=' . $db->escapeBoolean(true) . ' WHERE login=' . $db->escapeString($account->getLogin()));
	debug('Chosen NPC: ' . $account->getLogin() . ' (game ' . $session->getGameID() . ')');
}

function tradeGoods(int $goodID, AbstractSmrPlayer $player, SmrPort $port) : Page {
	sleepNPC(); //We have an extra sleep at port to make the NPC more vulnerable.
	$ship = $player->getShip();
	$relations = $player->getRelation($port->getRaceID());

	$transaction = $port->getGoodTransaction($goodID);

	if ($transaction === TRADER_BUYS) {
		$amount = $ship->getEmptyHolds();
	} else {
		$amount = $ship->getCargo($goodID);
	}

	$idealPrice = $port->getIdealPrice($goodID, $transaction, $amount, $relations);
	$offeredPrice = $port->getOfferPrice($idealPrice, $relations, $transaction);

	$_REQUEST = ['action' => $transaction];
	return Page::create('shop_goods_processing.php', '', array('offered_price'=>$offeredPrice, 'ideal_price'=>$idealPrice, 'amount'=>$amount, 'good_id'=>$goodID, 'bargain_price'=>$offeredPrice));
}

function dumpCargo(SmrPlayer $player) : Page {
	$ship = $player->getShip();
	$cargo = $ship->getCargo();
	debug('Ship Cargo', $cargo);
	foreach ($cargo as $goodID => $amount) {
		if ($amount > 0) {
			return Page::create('cargo_dump_processing.php', '', array('good_id'=>$goodID, 'amount'=>$amount));
		}
	}
	throw new Exception('Called dumpCargo without any cargo!');
}

function plotToSector(SmrPlayer $player, int $sectorID) : Page {
	return Page::create('course_plot_processing.php', '', array('from'=>$player->getSectorID(), 'to'=>$sectorID));
}

function plotToFed(SmrPlayer $player) : Page {
	debug('Plotting To Fed');

	// Always drop illegal goods before heading to fed space
	if ($player->getShip()->hasIllegalGoods()) {
		debug('Dumping illegal goods');
		processContainer(dumpCargo($player));
	}

	$fedLocID = $player->getRaceID() + LOCATION_GROUP_RACIAL_BEACONS;
	$container = plotToNearest($player, SmrLocation::getLocation($fedLocID));
	if ($container === false) {
		debug('Plotted to fed whilst in fed, switch NPC and wait for turns');
		throw new FinalActionException;
	}
	return $container;
}

function plotToNearest(AbstractSmrPlayer $player, mixed $realX) : Page|false {
	debug('Plotting To: ', $realX); //TODO: Can we make the debug output a bit nicer?

	if ($player->getSector()->hasX($realX)) { //Check if current sector has what we're looking for before we attempt to plot and get error.
		debug('Already available in sector');
		return false;
	}

	return Page::create('course_plot_nearest_processing.php', '', array('RealX'=>$realX));
}

function moveToSector(SmrPlayer $player, int $targetSector) : Page {
	debug('Moving from #' . $player->getSectorID() . ' to #' . $targetSector);
	return Page::create('sector_move_processing.php', '', array('target_sector'=>$targetSector, 'target_page'=>''));
}

function checkForShipUpgrade(AbstractSmrPlayer $player) : void {
	foreach (SHIP_UPGRADE_PATH[$player->getRaceID()] as $upgradeShipID) {
		if ($player->getShipTypeID() == $upgradeShipID) {
			//We can't upgrade, only downgrade.
			return;
		}
		$cost = $player->getShip()->getCostToUpgrade($upgradeShipID);
		$balance = $player->getCredits() - $cost;
		if ($balance > MINUMUM_RESERVE_CREDITS) {
			debug('Upgrading to ship type: ' . $upgradeShipID);
			$player->setCredits($balance);
			$player->getShip()->setTypeID($upgradeShipID);
			return;
		}
	}
}

function changeRoute(array &$tradeRoutes, Routes\Route $routeToAvoid = null) : ?Routes\Route {
	// Remove any route from the pool of available routes if it contains
	// either of the sectors in the $routeToAvoid (i.e. we died on it,
	// so don't go back!).
	if ($routeToAvoid !== null) {
		$avoidSectorIDs = array_unique([
			$routeToAvoid->getForwardRoute()->getSellSectorId(),
			$routeToAvoid->getForwardRoute()->getBuySectorId(),
			$routeToAvoid->getReturnRoute()->getSellSectorId(),
			$routeToAvoid->getReturnRoute()->getBuySectorId(),
		]);
		foreach ($tradeRoutes as $key => $route) {
			foreach ($avoidSectorIDs as $avoidSectorID) {
				if ($route->containsPort($avoidSectorID)) {
					unset($tradeRoutes[$key]);
					break;
				}
			}
		}
	}

	if (count($tradeRoutes) == 0) {
		return null;
	}

	// Pick a random route
	$routeKey = array_rand($tradeRoutes);
	$tradeRoute = $tradeRoutes[$routeKey];

	// Remove the route we chose so that we don't pick it again later.
	unset($tradeRoutes[$routeKey]);

	debug('Switched route', $tradeRoute);
	return $tradeRoute;
}

function findRoutes(SmrPlayer $player) : array {
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
	foreach (Smr\Race::getAllIDs() as $raceID) {
		$tradeRaces[$raceID] = false;
	}
	$tradeRaces[$player->getRaceID()] = true;
	$tradeRaces[RACE_NEUTRAL] = true;

	$galaxy = $player->getSector()->getGalaxy();

	$maxNumberOfPorts = 2;
	$routesForPort = -1;
	$numberOfRoutes = 100;
	$maxDistance = 15;

	$startSectorID = $galaxy->getStartSector();
	$endSectorID = $galaxy->getEndSector();

	$db = Smr\Database::getInstance();
	$dbResult = $db->read('SELECT routes FROM route_cache WHERE game_id=' . $db->escapeNumber($player->getGameID()) . ' AND max_ports=' . $db->escapeNumber($maxNumberOfPorts) . ' AND goods_allowed=' . $db->escapeObject($tradeGoods) . ' AND races_allowed=' . $db->escapeObject($tradeRaces) . ' AND start_sector_id=' . $db->escapeNumber($startSectorID) . ' AND end_sector_id=' . $db->escapeNumber($endSectorID) . ' AND routes_for_port=' . $db->escapeNumber($routesForPort) . ' AND max_distance=' . $db->escapeNumber($maxDistance));
	if ($dbResult->hasRecord()) {
		$routes = $dbResult->record()->getObject('routes', true);
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
		foreach ($allRoutes[\Routes\RouteGenerator::MONEY_ROUTE] as $multi => $routesByMulti) {
			$routesMerged += $routesByMulti; //Merge arrays
		}

		unset($allSectors);
		SmrPort::clearCache();
		SmrSector::clearCache();

		if (count($routesMerged) == 0) {
			debug('Could not find any routes! Try another NPC.');
			throw new FinalActionException;
		}

		$db->write('INSERT INTO route_cache ' .
				'(game_id, max_ports, goods_allowed, races_allowed, start_sector_id, end_sector_id, routes_for_port, max_distance, routes)' .
				' VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($maxNumberOfPorts) . ', ' . $db->escapeObject($tradeGoods) . ', ' . $db->escapeObject($tradeRaces) . ', ' . $db->escapeNumber($startSectorID) . ', ' . $db->escapeNumber($endSectorID) . ', ' . $db->escapeNumber($routesForPort) . ', ' . $db->escapeNumber($maxDistance) . ', ' . $db->escapeObject($routesMerged, true) . ')');
		debug('Found Routes: #' . count($routesMerged));
		return $routesMerged;
	}
}
