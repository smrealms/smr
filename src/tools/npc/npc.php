<?php declare(strict_types=1);

function overrideForward(Page $container): never {
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
	throw new Smr\Npc\Exceptions\Forward();
}
const OVERRIDE_FORWARD = true;

// global config
require_once(realpath(dirname(__FILE__)) . '/../../bootstrap.php');
// bot config
require_once(CONFIG . 'npc/config.specific.php');

// Enable NPC-specific conditions
Smr\Container\DiContainer::getContainer()->set('NPC_SCRIPT', true);

// Raise exceptions for all types of errors for improved error reporting
// and to attempt to shut down the NPCs cleanly on errors.
set_error_handler('exception_error_handler');

const SHIP_UPGRADE_PATH = [
	RACE_ALSKANT => [
		SHIP_TYPE_TRADE_MASTER,
		SHIP_TYPE_DEEP_SPACER,
		SHIP_TYPE_DEAL_MAKER,
		SHIP_TYPE_TRIP_MAKER,
		SHIP_TYPE_SMALL_TIMER,
	],
	RACE_CREONTI => [
		SHIP_TYPE_DEVASTATOR,
		SHIP_TYPE_JUGGERNAUT,
		SHIP_TYPE_GOLIATH,
		SHIP_TYPE_LEVIATHAN,
		SHIP_TYPE_MEDIUM_CARGO_HULK,
	],
	RACE_HUMAN => [
		SHIP_TYPE_DESTROYER,
		SHIP_TYPE_BORDER_CRUISER,
		SHIP_TYPE_AMBASSADOR,
		SHIP_TYPE_RENAISSANCE,
		SHIP_TYPE_LIGHT_FREIGHTER,
	],
	RACE_IKTHORNE => [
		SHIP_TYPE_MOTHER_SHIP,
		SHIP_TYPE_ADVANCED_CARRIER,
		SHIP_TYPE_FAVOURED_OFFSPRING,
		SHIP_TYPE_PROTO_CARRIER,
		SHIP_TYPE_TINY_DELIGHT,
	],
	RACE_SALVENE => [
		SHIP_TYPE_EATER_OF_SOULS,
		SHIP_TYPE_RAVAGER,
		SHIP_TYPE_PREDATOR,
		SHIP_TYPE_DRUDGE,
		SHIP_TYPE_HATCHLINGS_DUE,
	],
	RACE_THEVIAN => [
		SHIP_TYPE_ASSAULT_CRAFT,
		SHIP_TYPE_CARAPACE,
		SHIP_TYPE_BOUNTY_HUNTER,
		SHIP_TYPE_EXPEDITER,
		SHIP_TYPE_SWIFT_VENTURE,
	],
	RACE_WQHUMAN => [
		SHIP_TYPE_DARK_MIRAGE,
		SHIP_TYPE_BLOCKADE_RUNNER,
		SHIP_TYPE_ROGUE,
		SHIP_TYPE_RESISTANCE,
		SHIP_TYPE_SLIP_FREIGHTER,
	],
	RACE_NIJARIN => [
		SHIP_TYPE_FURY,
		SHIP_TYPE_VINDICATOR,
		SHIP_TYPE_VENGEANCE,
		SHIP_TYPE_RETALIATION,
		SHIP_TYPE_REDEEMER,
	],
];


try {
	NPCStuff();
} catch (Throwable $e) {
	logException($e);
	// Try to shut down cleanly
	exitNPC();
}


function NPCStuff(): void {
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
			$actions = 0;

			// We chose a new NPC, we don't care what we were doing beforehand.
			$previousContainer = null;
		}

		try {
			// Avoid infinite loops by restricting the number of actions
			if ($actions > NPC_MAX_ACTIONS) {
				debug('Reached maximum number of actions: ' . NPC_MAX_ACTIONS);
				throw new Smr\Npc\Exceptions\FinalAction();
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
				checkStartConditions($player);

				// Ensure the NPC doesn't think it's under attack at startup,
				// since this could cause it to get stuck in a loop in Fed.
				$player->setUnderAttack(false);

				// Initialize the trade route for this NPC
				$allTradeRoutes = findRoutes($player);
				$tradeRoute = changeRoute($allTradeRoutes);

				// Upgrade ship if possible, reset hardware to max, etc.
				setupShip($player);

				// Update database (not essential to have a lock here)
				$player->update();
			}

			if ($player->isDead()) {
				debug('Some evil person killed us, let\'s move on now.');
				$player->setDead(false); // see death_processing.php
				$player->setNewbieWarning(false); // undo SmrPlayer::killPlayer setting this to true
				checkStartConditions($player);

				$previousContainer = null; //We died, we don't care what we were doing beforehand.
				setupShip($player); // reship before continuing
				$tradeRoute = changeRoute($allTradeRoutes, $tradeRoute);
			}

			// Do we have a plot that ends in Fed?
			$hasPlotToFed = $player->hasPlottedCourse() && SmrSector::getSector($player->getGameID(), $player->getPlottedCourse()->getEndSectorID())->offersFederalProtection();

			if ($player->isUnderAttack() && !$hasPlotToFed) {
				// We're under attack and need to plot course to fed.
				debug('Under Attack');
				processContainer(plotToFed($player));
			} elseif ($hasPlotToFed) {
				// We have a route to fed to follow
				debug('Follow Course: ' . $player->getPlottedCourse()->getNextOnPath());
				processContainer(moveToSector($player, $player->getPlottedCourse()->getNextOnPath()));
			} elseif ($player->hasPlottedCourse()) {
				// We have a route to follow
				debug('Follow Course: ' . $player->getPlottedCourse()->getNextOnPath());
				processContainer(moveToSector($player, $player->getPlottedCourse()->getNextOnPath()));
			} elseif ($player->getTurns() < NPC_LOW_TURNS) {
				// We're low on turns or have been under attack and need to plot course to fed
				if ($player->hasFederalProtection()) {
					debug('We are in fed, time to switch to another NPC.');
					throw new Smr\Npc\Exceptions\FinalAction();
				}
				if ($player->getTurns() < NPC_LOW_TURNS) {
					debug('Low Turns:' . $player->getTurns());
				}
				processContainer(plotToFed($player));

			} elseif ($tradeRoute instanceof Smr\Routes\MultiplePortRoute) {
				debug('Trade Route');
				if (!$tradeRoute->containsPort($player->getSectorID())) {
					// We're not at a port in this trade route yet, let's plot to it.
					// It doesn't matter where in the route we start, so we choose the return route for convenience.
					$plotSectorID = $tradeRoute->getReturnRoute()->getBuySectorId();
					debug('Plot To Buy: ' . $plotSectorID);
					processContainer(plotToSector($player, $plotSectorID));
				}

				$port = $player->getSector()->getPort();
				$tradeRestriction = $port->getTradeRestriction($player);
				if ($tradeRestriction !== false) {
					// We can't trade at this port, let's try another route
					$tradeRoute = changeRoute($allTradeRoutes, $tradeRoute);
					throw new Smr\Npc\Exceptions\Forward();
				}

				// Find which part of the route we're on and act accordingly
				$ship = $player->getShip();
				foreach ($tradeRoute->getOneWayRoutes() as $route) {
					$goodID = $route->getGoodID();
					if ($route->getSellSectorId() == $player->getSectorID()) {
						if ($ship->hasCargo($goodID) && ($port->getGoodAmount($goodID) >= $ship->getCargo($goodID))) {
							debug('Sell Goods');
							processContainer(tradeGoods($goodID, $player, $port));
						}
					} elseif ($route->getBuySectorId() == $player->getSectorID()) {
						if ($ship->hasCargo($goodID)) {
							// We just bought goods here, plot to sell
							$plotSectorID = $route->getSellSectorId();
							debug('Plot To Sell: ' . $plotSectorID);
							processContainer(plotToSector($player, $plotSectorID));
						} elseif (!$ship->hasCargo() && ($port->getGoodAmount($goodID) >= $ship->getEmptyHolds())) {
							debug('Buy Goods');
							processContainer(tradeGoods($goodID, $player, $port));
						}
					}
				}

				// If we still have cargo, we need to dump it
				if ($ship->hasCargo()) {
					debug('Dump Goods');
					processContainer(dumpCargo($player));
				}

				// We can't do anything with this trade route, let's pick another
				$tradeRoute = changeRoute($allTradeRoutes, $tradeRoute);
				throw new Smr\Npc\Exceptions\Forward();

			} else {
				debug('No valid actions to take');
				processContainer(plotToFed($player));
			}
			/*
			else { //Otherwise let's run around at random.
				$moveTo = array_rand_value($player->getSector()->getLinks());
				debug('Random Wanderings: ' . $moveTo);
				processContainer(moveToSector($player, $moveTo));
			}
			*/
			throw new Exception('NPC failed to perform any action');
		} catch (Smr\Npc\Exceptions\Forward) {
			$actions++; // we took an action
		} catch (Smr\Npc\Exceptions\FinalAction) {
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
		saveAllAndReleaseLock(updateSession: false);

		//Clean up the caches as the data may get changed by other players
		clearCaches();

		//Clear up some global vars to avoid contaminating subsequent pages
		$_REQUEST = [];

		//Have a sleep between actions
		sleepNPC();
	}
	debug('Actions Finished.');
	exitNPC();
}

function clearCaches(): void {
	SmrSector::clearCache();
	SmrPlayer::clearCache();
	SmrShip::clearCache();
	SmrForce::clearCache();
	SmrPort::clearCache();
}

function debug(string $message, mixed $debugObject = null): void {
	echo date('Y-m-d H:i:s - ') . $message . ($debugObject !== null ? EOL . var_export($debugObject, true) : '') . EOL;
	if (NPC_LOG_TO_DATABASE) {
		$session = Smr\Session::getInstance();
		$accountID = $session->getAccountID();
		$var = $session->getCurrentVar();
		$db = Smr\Database::getInstance();
		$logID = $db->insert('npc_logs', [
			'script_id' => $db->escapeNumber(defined('SCRIPT_ID') ? SCRIPT_ID : 0),
			'npc_id' => $db->escapeNumber($accountID),
			'time' => 'NOW()',
			'message' => $db->escapeString($message),
			'debug_info' => $db->escapeString(var_export($debugObject, true)),
			'var' => $db->escapeString(var_export($var, true)),
		]);

		// On the first call to debug, we need to update the script_id retroactively
		if (!defined('SCRIPT_ID')) {
			define('SCRIPT_ID', $logID);
			$db->write('UPDATE npc_logs SET script_id=' . SCRIPT_ID . ' WHERE log_id=' . SCRIPT_ID);
		}
	}
}

/**
 * Determines if a player has enough turns to start taking actions
 */
function checkStartConditions(SmrPlayer $player): void {
	$minTurnsThreshold = rand($player->getMaxTurns() / 2, $player->getMaxTurns());
	if ($player->getTurns() < $minTurnsThreshold && ($player->hasNewbieTurns() || $player->hasFederalProtection())) {
		debug('We don\'t have enough turns to bother starting trading, and we are protected: ' . $player->getTurns());
		throw new Smr\Npc\Exceptions\FinalAction();
	}
}

function processContainer(Page $container): never {
	global $forwardedContainer, $previousContainer;
	$session = Smr\Session::getInstance();
	$player = $session->getPlayer();
	if ($container == $previousContainer && $forwardedContainer['body'] != 'forces_attack.php') {
		debug('We are executing the same container twice?', ['ForwardedContainer' => $forwardedContainer, 'Container' => $container]);
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

	// Lock now to skip var update in do_voodoo
	Smr\SectorLock::getInstance()->acquireForPlayer($player);
	do_voodoo();
}

function sleepNPC(): void {
	usleep(rand(MIN_SLEEP_TIME, MAX_SLEEP_TIME)); //Sleep for a random time
}

// Releases an NPC when it is done working
function releaseNPC(): void {
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

	// Delete sector lock associated with this NPC
	Smr\SectorLock::resetInstance();
}

function exitNPC(): void {
	debug('Exiting NPC script.');
	releaseNPC();
	exit;
}

function changeNPCLogin(): void {
	// Release previous NPC, if any
	releaseNPC();

	// Lacking a convenient way to get up-to-date turns, order NPCs by how
	// recently they have taken an action.
	debug('Choosing new NPC');
	static $availableNpcs = null;

	$db = Smr\Database::getInstance();
	$session = Smr\Session::getInstance();

	if ($availableNpcs === null) {
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
	debug('Chosen NPC: login = ' . $account->getLogin() . ', game = ' . $session->getGameID() . ', player = ' . $session->getPlayer()->getPlayerName());
}

function tradeGoods(int $goodID, AbstractSmrPlayer $player, SmrPort $port): Page {
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
	return Page::create('shop_goods_processing.php', '', ['offered_price' => $offeredPrice, 'ideal_price' => $idealPrice, 'amount' => $amount, 'good_id' => $goodID, 'bargain_price' => $offeredPrice]);
}

function dumpCargo(SmrPlayer $player): Page {
	$ship = $player->getShip();
	$cargo = $ship->getCargo();
	debug('Ship Cargo', $cargo);
	foreach ($cargo as $goodID => $amount) {
		if ($amount > 0) {
			return Page::create('cargo_dump_processing.php', '', ['good_id' => $goodID, 'amount' => $amount]);
		}
	}
	throw new Exception('Called dumpCargo without any cargo!');
}

function plotToSector(SmrPlayer $player, int $sectorID): Page {
	return Page::create('course_plot_processing.php', '', ['from' => $player->getSectorID(), 'to' => $sectorID]);
}

function plotToFed(SmrPlayer $player): Page {
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
		throw new Smr\Npc\Exceptions\FinalAction();
	}
	return $container;
}

function plotToNearest(AbstractSmrPlayer $player, mixed $realX): Page|false {
	debug('Plotting To: ', $realX); //TODO: Can we make the debug output a bit nicer?

	if ($player->getSector()->hasX($realX)) { //Check if current sector has what we're looking for before we attempt to plot and get error.
		debug('Already available in sector');
		return false;
	}

	return Page::create('course_plot_nearest_processing.php', '', ['RealX' => $realX]);
}

function moveToSector(SmrPlayer $player, int $targetSector): Page {
	debug('Moving from #' . $player->getSectorID() . ' to #' . $targetSector);
	return Page::create('sector_move_processing.php', '', ['target_sector' => $targetSector, 'target_page' => '']);
}

function checkForShipUpgrade(AbstractSmrPlayer $player): void {
	foreach (SHIP_UPGRADE_PATH[$player->getRaceID()] as $upgradeShipID) {
		if ($player->getShipTypeID() == $upgradeShipID) {
			//We can't upgrade, only downgrade.
			return;
		}
		$cost = $player->getShip()->getCostToUpgrade($upgradeShipID);
		$balance = $player->getCredits() - $cost;
		if ($balance > MINIMUM_RESERVE_CREDITS) {
			debug('Upgrading to ship type: ' . $upgradeShipID);
			$player->setCredits($balance);
			$player->getShip()->setTypeID($upgradeShipID);
			return;
		}
	}
}

function setupShip(AbstractSmrPlayer $player): void {
	// Upgrade ships if we can
	checkForShipUpgrade($player);

	// Start the NPC with max hardware
	$ship = $player->getShip();
	$ship->setHardwareToMax();

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
	$ship->removeAllWeapons();
	while ($ship->hasOpenWeaponSlots()) {
		$weapon = SmrWeapon::getWeapon(array_shift($weaponIDs));
		$ship->addWeapon($weapon);
	}

	// Enable special hardware
	if ($ship->hasCloak()) {
		$ship->enableCloak();
	}
	if ($ship->hasIllusion()) {
		$illusionShipID = array_rand_value(SHIP_UPGRADE_PATH[$player->getRaceID()]);
		$ship->setIllusion($illusionShipID, rand(8, 25), rand(6, 20));
	}

	// Update database (not essential to have a lock here)
	$player->update();
	$ship->update();
}

function changeRoute(array &$tradeRoutes, Smr\Routes\Route $routeToAvoid = null): ?Smr\Routes\MultiplePortRoute {
	// Remove any route from the pool of available routes if it contains
	// either of the sectors in the $routeToAvoid (i.e. we died on it,
	// so don't go back!).
	if ($routeToAvoid !== null) {
		$avoidSectorIDs = $routeToAvoid->getPortSectorIDs();
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

function findRoutes(SmrPlayer $player): array {
	debug('Finding Routes');

	$tradeGoods = [GOODS_NOTHING => false];
	foreach (Globals::getGoods() as $goodID => $good) {
		if ($player->meetsAlignmentRestriction($good['AlignRestriction'])) {
			$tradeGoods[$goodID] = true;
		} else {
			$tradeGoods[$goodID] = false;
		}
	}

	// Only allow NPCs to trade at ports of their race and neutral ports
	$tradeRaces = [];
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
	}

	debug('Generating Routes');
	$ports = $galaxy->getPorts();

	$distances = Plotter::calculatePortToPortDistances($ports, $maxDistance, $startSectorID, $endSectorID);

	$allRoutes = Smr\Routes\RouteGenerator::generateMultiPortRoutes($maxNumberOfPorts, $ports, $tradeGoods, $tradeRaces, $distances, $routesForPort, $numberOfRoutes);

	unset($distances);

	$routesMerged = [];
	foreach ($allRoutes[Smr\Routes\RouteGenerator::MONEY_ROUTE] as $multi => $routesByMulti) {
		$routesMerged += $routesByMulti; //Merge arrays
	}

	SmrPort::clearCache();
	SmrSector::clearCache();

	if (count($routesMerged) == 0) {
		debug('Could not find any routes! Try another NPC.');
		throw new Smr\Npc\Exceptions\FinalAction();
	}

	$db->insert('route_cache', [
		'game_id' => $db->escapeNumber($player->getGameID()),
		'max_ports' => $db->escapeNumber($maxNumberOfPorts),
		'goods_allowed' => $db->escapeObject($tradeGoods),
		'races_allowed' => $db->escapeObject($tradeRaces),
		'start_sector_id' => $db->escapeNumber($startSectorID),
		'end_sector_id' => $db->escapeNumber($endSectorID),
		'routes_for_port' => $db->escapeNumber($routesForPort),
		'max_distance' => $db->escapeNumber($maxDistance),
		'routes' => $db->escapeObject($routesMerged, true),
	]);
	debug('Found Routes: #' . count($routesMerged));
	return $routesMerged;
}
