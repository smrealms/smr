<?php declare(strict_types=1);

use Smr\AbstractPlayer;
use Smr\Account;
use Smr\Combat\Weapon\Weapon;
use Smr\Container\DiContainer;
use Smr\Database;
use Smr\Epoch;
use Smr\Exceptions\PathNotFound;
use Smr\Force;
use Smr\Galaxy;
use Smr\Location;
use Smr\Npc\Exceptions\FinalAction;
use Smr\Npc\Exceptions\ForwardAction;
use Smr\Npc\Exceptions\TradeRouteDrained;
use Smr\Npc\NpcActor;
use Smr\Page\Page;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Account\ErrorDisplay;
use Smr\Pages\Player\CargoDumpProcessor;
use Smr\Pages\Player\CurrentSector;
use Smr\Pages\Player\PlotCourseConventionalProcessor;
use Smr\Pages\Player\SectorMoveProcessor;
use Smr\Pages\Player\ShopGoodsProcessor;
use Smr\Player;
use Smr\Plotter;
use Smr\Port;
use Smr\Race;
use Smr\Routes\RouteGenerator;
use Smr\Sector;
use Smr\SectorLock;
use Smr\Session;
use Smr\Ship;
use Smr\TradeGood;
use Smr\TransactionType;

function overrideForward(Page $container): never {
	global $forwardedContainer;
	$forwardedContainer = $container;
	if ($container instanceof ErrorDisplay) {
		// We hit a create_error - this shouldn't happen for an NPC often,
		// for now we want to throw an exception for it for testing.
		debug('Hit an error');
		throw new Exception($container->message);
	}
	// We have to throw the exception to get back up the stack,
	// otherwise we quickly hit problems of overflowing the stack.
	throw new ForwardAction();
}
const OVERRIDE_FORWARD = true;

// global config
require_once(realpath(__DIR__) . '/../../bootstrap.php');

// Enable NPC-specific conditions
DiContainer::getContainer()->set('NPC_SCRIPT', true);

// Raise exceptions for all types of errors for improved error reporting
// and to attempt to shut down the NPCs cleanly on errors.
set_error_handler('exception_error_handler');

const SHIP_UPGRADE_PATH = [
	RACE_NEUTRAL => [
		SHIP_TYPE_CELESTIAL_TRADER,
		SHIP_TYPE_MERCHANT_VESSEL,
		SHIP_TYPE_FREIGHTER,
		SHIP_TYPE_PLANETARY_FREIGHTER,
		SHIP_TYPE_PLANETARY_SUPER_FREIGHTER,
	],
	RACE_ALSKANT => [
		SHIP_TYPE_SMALL_TIMER,
		SHIP_TYPE_TRIP_MAKER,
		SHIP_TYPE_DEAL_MAKER,
		SHIP_TYPE_DEEP_SPACER,
		SHIP_TYPE_TRADE_MASTER,
	],
	RACE_CREONTI => [
		SHIP_TYPE_MEDIUM_CARGO_HULK,
		SHIP_TYPE_LEVIATHAN,
		SHIP_TYPE_GOLIATH,
		SHIP_TYPE_JUGGERNAUT,
		SHIP_TYPE_DEVASTATOR,
	],
	RACE_HUMAN => [
		SHIP_TYPE_LIGHT_FREIGHTER,
		SHIP_TYPE_RENAISSANCE,
		SHIP_TYPE_AMBASSADOR,
		SHIP_TYPE_BORDER_CRUISER,
		SHIP_TYPE_DESTROYER,
	],
	RACE_IKTHORNE => [
		SHIP_TYPE_TINY_DELIGHT,
		SHIP_TYPE_PROTO_CARRIER,
		SHIP_TYPE_FAVOURED_OFFSPRING,
		SHIP_TYPE_ADVANCED_CARRIER,
		SHIP_TYPE_MOTHER_SHIP,
	],
	RACE_SALVENE => [
		SHIP_TYPE_HATCHLINGS_DUE,
		SHIP_TYPE_DRUDGE,
		SHIP_TYPE_PREDATOR,
		SHIP_TYPE_RAVAGER,
		SHIP_TYPE_EATER_OF_SOULS,
	],
	RACE_THEVIAN => [
		SHIP_TYPE_SWIFT_VENTURE,
		SHIP_TYPE_EXPEDITER,
		SHIP_TYPE_BOUNTY_HUNTER,
		SHIP_TYPE_CARAPACE,
		SHIP_TYPE_ASSAULT_CRAFT,
	],
	RACE_WQHUMAN => [
		SHIP_TYPE_SLIP_FREIGHTER,
		SHIP_TYPE_RESISTANCE,
		SHIP_TYPE_ROGUE,
		SHIP_TYPE_BLOCKADE_RUNNER,
		SHIP_TYPE_DARK_MIRAGE,
	],
	RACE_NIJARIN => [
		SHIP_TYPE_REDEEMER,
		SHIP_TYPE_RETALIATION,
		SHIP_TYPE_VENGEANCE,
		SHIP_TYPE_VINDICATOR,
		SHIP_TYPE_FURY,
	],
];

const SHIP_UPGRADE_PATH_GOOD = [
	SHIP_TYPE_GALACTIC_SEMI,
	SHIP_TYPE_LIGHT_COURIER_VESSEL,
	SHIP_TYPE_ADVANCED_COURIER_VESSEL,
	SHIP_TYPE_FEDERAL_DISCOVERY,
	SHIP_TYPE_FEDERAL_WARRANT,
	SHIP_TYPE_FEDERAL_ULTIMATUM,
];

const SHIP_UPGRADE_PATH_EVIL = [
	SHIP_TYPE_GALACTIC_SEMI,
	SHIP_TYPE_CELESTIAL_MERCENARY,
	SHIP_TYPE_STELLAR_FREIGHTER,
	SHIP_TYPE_THIEF,
	SHIP_TYPE_ASSASSIN,
	SHIP_TYPE_DEATH_CRUISER,
];

try {
	while (npcDriver() === false) {
		// No actions taken, try another NPC
	}
} catch (Throwable $e) {
	logException($e);
}
// Try to shut down cleanly
exitNPC();

/**
 * @return bool If the NPC performed any actions
 */
function npcDriver(): bool {
	global $previousContainer;

	$session = Session::getInstance();
	$session->setCurrentVar(new Page()); // initialize fake var

	// Load the first available NPC
	changeNPCLogin();

	// We chose a new NPC, we don't care what we were doing beforehand.
	$previousContainer = null;

	try {
		$actor = new NpcActor($session->getGameID(), $session->getAccountID());
	} catch (FinalAction) {
		// Startup conditions not satisfied, try another NPC
		return false;
	}

	// Loop over actions for this NPC
	while (true) {
		try {
			$container = $actor->getNextAction();
			processContainer($container);
		} catch (ForwardAction) {
			// we took an action
		} catch (FinalAction) {
			$actor->shutdown();
			// switch to a new NPC if we haven't taken any actions yet
			return $actor->getNumActions() > 0;
		} finally {
			// Save any changes that we made during this action
			saveAllAndReleaseLock(updateSession: false);

			//Clean up the caches as the data may get changed by other players
			clearCaches();
		}

		//Have a sleep between actions
		sleepNPC();
	}
}

function clearCaches(): void {
	Sector::clearCache();
	Player::clearCache();
	Ship::clearCache();
	Force::clearCache();
	Port::clearCache();
}

function debug(string $message, mixed $debugObject = null): void {
	echo date('Y-m-d H:i:s - ') . $message . ($debugObject !== null ? EOL . var_export($debugObject, true) : '') . EOL;
	if (NPC_LOG_TO_DATABASE) {
		$session = Session::getInstance();
		$accountID = $session->getAccountID();
		$var = $session->getCurrentVar();
		$db = Database::getInstance();
		$logID = $db->insertAutoIncrement('npc_logs', [
			'script_id' => defined('SCRIPT_ID') ? SCRIPT_ID : 0,
			'npc_id' => $accountID,
			'time' => date('Y-m-d H:i:s'),
			'message' => $message,
			'debug_info' => var_export($debugObject, true),
			'var' => var_export($var, true),
		]);

		// On the first call to debug, we need to update the script_id retroactively
		if (!defined('SCRIPT_ID')) {
			define('SCRIPT_ID', $logID);
			$db->update(
				'npc_logs',
				['script_id' => SCRIPT_ID],
				['log_id' => SCRIPT_ID],
			);
		}
	}
}

/**
 * Determines if a player has enough turns to start taking actions
 */
function checkStartConditions(AbstractPlayer $player): void {
	$minTurnsThreshold = rand($player->getMaxTurns() / 2, $player->getMaxTurns());
	if ($player->getTurns() < $minTurnsThreshold && !$player->canFight()) {
		debug('We don\'t have enough turns to bother starting trading, and we are protected: ' . $player->getTurns());
		throw new FinalAction();
	}
}

function processContainer(PlayerPageProcessor $container): never {
	global $forwardedContainer, $previousContainer;
	$session = Session::getInstance();
	$player = $session->getPlayer();
	if ($container === $previousContainer && $forwardedContainer->file !== 'forces_attack.php') {
		debug('We are executing the same container twice?', ['ForwardedContainer' => $forwardedContainer, 'Container' => $container]);
		if (!$player->canFight()) {
			// Only throw the exception if we have protection, otherwise let's hope that the NPC will be able to find its way to safety rather than dying in the open.
			throw new Exception('We are executing the same container twice?');
		}
	}
	$previousContainer = $container;
	debug('Executing container', $container);
	// The next "page request" must occur at an updated time.
	Epoch::update();
	$session->setCurrentVar($container);

	// Acquire a lock in the sector where we chose our action
	$lock = SectorLock::getInstance();
	$lock->acquireForPlayer($player);
	clearCaches(); // do not retain anything from before lock acquisition
	if ($session->getPlayer(true)->getSectorID() !== $lock->getSectorID()) {
		// NPC sector was modified externally (e.g. back to HQ in a pod) while
		// deciding what to do, so skip this action and select a new action.
		throw new ForwardAction();
	}

	$container->build($player);
}

function sleepNPC(): void {
	usleep(rand(NPC_MIN_SLEEP_TIME, NPC_MAX_SLEEP_TIME)); //Sleep for a random time
}

// Releases an NPC when it is done working
function releaseNPC(): void {
	$session = Session::getInstance();
	if (!$session->hasAccount()) {
		debug('releaseNPC: no NPC to release');
		return;
	}
	$login = $session->getAccount()->getLogin();
	$db = Database::getInstance();
	$changedRows = $db->update(
		'npc_logins',
		['working' => $db->escapeBoolean(false)],
		['login' => $login],
	);
	if ($changedRows > 0) {
		debug('Released NPC: ' . $login);
	} else {
		debug('Failed to release NPC: ' . $login);
	}

	// Delete sector lock associated with this NPC
	SectorLock::resetInstance();
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

	$db = Database::getInstance();
	$session = Session::getInstance();

	if ($availableNpcs === null) {
		// Make sure NPC's have been set up in the database
		$dbResult = $db->read('SELECT 1 FROM npc_logins LIMIT 1');
		if (!$dbResult->hasRecord()) {
			debug('No NPCs have been created yet!');
			exitNPC();
		}

		// Make sure to select NPCs from active games only
		$dbResult = $db->read('SELECT account_id, game_id FROM player JOIN account USING(account_id) JOIN npc_logins USING(login) JOIN game USING(game_id) WHERE active=\'TRUE\' AND working=\'FALSE\' AND start_time < :now AND end_time > :now ORDER BY last_turn_update ASC', [
			'now' => $db->escapeNumber(Epoch::time()),
		]);
		$availableNpcs = [];
		foreach ($dbResult->records() as $dbRecord) {
			$availableNpcs[] = [
				'account_id' => $dbRecord->getInt('account_id'),
				'game_id' => $dbRecord->getInt('game_id'),
			];
		}
	}

	if (count($availableNpcs) === 0) {
		debug('No free NPCs');
		exitNPC();
	}

	// Pop an NPC off the top of the stack to activate
	$npc = array_shift($availableNpcs);

	// Update session info for this chosen NPC
	$account = Account::getAccount($npc['account_id']);
	$session->setAccount($account);
	$session->updateGame($npc['game_id']);

	$db->update(
		'npc_logins',
		['working' => $db->escapeBoolean(true)],
		['login' => $account->getLogin()],
	);
	debug('Chosen NPC: login = ' . $account->getLogin() . ', game = ' . $session->getGameID() . ', player = ' . $session->getPlayer()->getPlayerName());
}

function tradeGoods(int $goodID, AbstractPlayer $player, Port $port): PlayerPageProcessor {
	sleepNPC(); //We have an extra sleep at port to make the NPC more vulnerable.
	$ship = $player->getShip();
	$relations = $player->getRelation($port->getRaceID());

	$transaction = $port->getGoodTransaction($goodID);

	if ($transaction === TransactionType::Buy) {
		debug('Buy Goods');
		$amount = $ship->getEmptyHolds();
	} else {
		debug('Sell Goods');
		$amount = $ship->getCargo($goodID);
	}

	if ($port->getGoodAmount($goodID) < $amount) {
		throw new TradeRouteDrained();
	}

	$idealPrice = $port->getIdealPrice($goodID, $transaction, $amount, $relations);
	$offeredPrice = $port->getOfferPrice($idealPrice, $relations, $transaction);

	return new ShopGoodsProcessor(
		goodID: $goodID,
		amount: $amount,
		bargainNumber: 1,
		bargainPrice: $offeredPrice, // take the offered price
	);
}

function dumpCargo(AbstractPlayer $player): PlayerPageProcessor {
	$ship = $player->getShip();
	$cargo = $ship->getCargo();
	debug('Ship Cargo', $cargo);
	foreach ($cargo as $goodID => $amount) {
		if ($amount > 0) {
			return new CargoDumpProcessor($goodID, $amount);
		}
	}
	throw new Exception('Called dumpCargo without any cargo!');
}

function plotToSector(AbstractPlayer $player, int $sectorID): PlayerPageProcessor {
	return new PlotCourseConventionalProcessor(from: $player->getSectorID(), to: $sectorID);
}

function plotToFed(AbstractPlayer $player): never {
	debug('Plotting To Fed');

	// Always drop illegal goods before heading to fed space
	if ($player->getShip()->hasIllegalGoods()) {
		debug('Dumping illegal goods');
		processContainer(dumpCargo($player));
	}

	$fedLocID = $player->getRaceID() + LOCATION_GROUP_RACIAL_BEACONS;
	try {
		$needToMove = plotToNearest($player, Location::getLocation($player->getGameID(), $fedLocID));
	} catch (PathNotFound) {
		debug('Racial Beacon not found, trying any safe fed');
		$needToMove = plotToNearest($player, 'SafeFed');
	}
	if ($needToMove === false) {
		debug('Plotted to fed whilst in fed, switch NPC and wait for turns');
		throw new FinalAction();
	}
	throw new ForwardAction();
}

/**
 * Sets the player's plotted course to the nearest $realX location.
 *
 * @raises \Smr\Exceptions\PathNotFound
 * @return bool True if location is in another sector, false if in current sector.
 */
function plotToNearest(AbstractPlayer $player, mixed $realX): bool {
	debug('Plotting To: ', $realX); //TODO: Can we make the debug output a bit nicer?

	if ($player->getSector()->hasX($realX, $player)) {
		debug('Already available in sector');
		return false;
	}

	$path = Plotter::findDistanceToX(
		x: $realX,
		sector: $player->getSector(),
		useFirst: true,
		needsToHaveBeenExploredBy: null, // NPCs have full vision
		player: $player,
	);
	$player->setPlottedCourse($path);
	return true;
}

function moveToSector(AbstractPlayer $player, int $targetSector): PlayerPageProcessor {
	debug('Moving from #' . $player->getSectorID() . ' to #' . $targetSector);
	return new SectorMoveProcessor($targetSector, new CurrentSector());
}

/**
 * @param list<list<int>> $upgradeGroups
 */
function getCurrentShipTier(AbstractPlayer $player, array $upgradeGroups): int {
	// Determine current ship tier
	foreach ($upgradeGroups as $upgradeGroup) {
		foreach ($upgradeGroup as $tier => $upgradeShipID) {
			if ($player->getShipTypeID() === $upgradeShipID) {
				return $tier;
			}
		}
	}
	// Ship is not in a valid upgrade path; this will upgrade to tier 0
	return -1;
}

function checkForShipUpgrade(AbstractPlayer $player): void {
	// Select the next tier ship in a random upgrade group
	$upgradeGroups = [
		SHIP_UPGRADE_PATH[$player->getRaceID()],
		SHIP_UPGRADE_PATH[RACE_NEUTRAL],
	];
	// 50% chance to pick from evil/good ships
	if ($player->hasGoodAlignment() && flip_coin()) {
		$upgradeGroups[] = SHIP_UPGRADE_PATH_GOOD;
	}
	if ($player->hasEvilAlignment() && flip_coin()) {
		$upgradeGroups[] = SHIP_UPGRADE_PATH_EVIL;
	}
	$currentTier = getCurrentShipTier($player, $upgradeGroups);
	$upgradeGroup = array_rand_value($upgradeGroups);
	$upgradeTier = $currentTier + 1;
	if (!array_key_exists($upgradeTier, $upgradeGroup)) {
		// Already at highest tier, no upgrade
		return;
	}
	$upgradeShipID = $upgradeGroup[$upgradeTier];

	// Base chance to upgrade is percent of cost of ship NPC can afford,
	// which decreases for higher ship tier (but returns to the base chance
	// over a number of weeks).
	$cost = $player->getShip()->getCostToUpgrade($upgradeShipID);
	$weekNum = (Epoch::time() - $player->getGame()->getStartTime()) / 604800;
	$delayFactor = 1 + max(0, 1.5 * $upgradeTier - $weekNum);
	$baseUpgradeFrac = $player->getCredits() / max($cost, 1); // avoid <=0 denom
	$maxUpgradeFrac = 1 - 0.1 * $upgradeTier; // -10% max chance per tier
	$upgradeFrac = min($maxUpgradeFrac, $baseUpgradeFrac) / $delayFactor;
	$upgradePercent = IRound(100 * $upgradeFrac);
	if (flip_coin($upgradePercent)) {
		debug('Upgrading to ship type: ' . $upgradeShipID);
		$balance = $player->getCredits() - $cost;
		$player->setCredits(max(NPC_MINIMUM_RESERVE_CREDITS, $balance));
		$player->getShip()->setTypeID($upgradeShipID);
	}
}

function setupShip(AbstractPlayer $player): void {
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
	while ($ship->hasOpenWeaponSlots() && count($weaponIDs) > 0) {
		$weapon = Weapon::getWeapon(array_shift($weaponIDs));
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

/**
 * @return array<Smr\Routes\MultiplePortRoute>
 */
function findRoutes(AbstractPlayer $player): array {
	debug('Finding Routes');

	$tradeGoods = [GOODS_NOTHING => false];
	foreach (TradeGood::getAll() as $goodID => $good) {
		if ($player->meetsAlignmentRestriction($good->alignRestriction)) {
			$tradeGoods[$goodID] = true;
		} else {
			$tradeGoods[$goodID] = false;
		}
	}

	// Only allow NPCs to trade at ports of their race and neutral ports
	$tradeRaces = [];
	foreach (Race::getAllIDs() as $raceID) {
		$tradeRaces[$raceID] = false;
	}
	$tradeRaces[$player->getRaceID()] = true;
	$tradeRaces[RACE_NEUTRAL] = true;

	// Trade in all Racial/Neutral galaxies up until the first Planet galaxy
	$galaxies = [];
	foreach ($player->getGame()->getGalaxies() as $galaxy) {
		if ($galaxy->getGalaxyType() === Galaxy::TYPE_PLANET) {
			break;
		}
		$galaxies[] = $galaxy;
	}
	// Fallback to current galaxy in case this has selected no galaxies
	if (count($galaxies) === 0) {
		$galaxies[] = $player->getSector()->getGalaxy();
	}

	// Determine the trade area (start of first galaxy to end of last)
	$startSectorID = reset($galaxies)->getStartSector();
	$endSectorID = end($galaxies)->getEndSector();

	$maxNumberOfPorts = 2;
	$routesForPort = -1;
	$numberOfRoutes = 150;
	$maxDistance = 15;

	$db = Database::getInstance();
	$dbResult = $db->read('SELECT routes FROM route_cache WHERE game_id = :game_id AND max_ports = :max_ports AND goods_allowed = :goods_allowed AND races_allowed = :races_allowed AND start_sector_id = :start_sector_id AND end_sector_id = :end_sector_id AND routes_for_port = :routes_for_port AND max_distance = :max_distance', [
		'game_id' => $db->escapeNumber($player->getGameID()),
		'max_ports' => $db->escapeNumber($maxNumberOfPorts),
		'goods_allowed' => $db->escapeObject($tradeGoods),
		'races_allowed' => $db->escapeObject($tradeRaces),
		'start_sector_id' => $db->escapeNumber($startSectorID),
		'end_sector_id' => $db->escapeNumber($endSectorID),
		'routes_for_port' => $db->escapeNumber($routesForPort),
		'max_distance' => $db->escapeNumber($maxDistance),
	]);
	if ($dbResult->hasRecord()) {
		$routes = $dbResult->record()->getObject('routes', true);
		debug('Using Cached Routes: #' . count($routes));
		return $routes;
	}

	debug('Generating Routes');
	$ports = [];
	foreach ($galaxies as $galaxy) {
		$ports += $galaxy->getPorts(); // Merge arrays
	}

	$distances = Plotter::calculatePortToPortDistances($ports, $tradeRaces, $maxDistance, $startSectorID, $endSectorID);

	$allRoutes = RouteGenerator::generateMultiPortRoutes($maxNumberOfPorts, $ports, $tradeGoods, $tradeRaces, $distances, $routesForPort, $numberOfRoutes);

	unset($distances);

	$routesMerged = [];
	foreach ($allRoutes[RouteGenerator::MONEY_ROUTE] as $routesByMulti) {
		$routesMerged += $routesByMulti; //Merge arrays
	}

	Port::clearCache();
	Sector::clearCache();

	if (count($routesMerged) === 0) {
		debug('Could not find any routes! Try another NPC.');
		throw new FinalAction();
	}

	$db->insert('route_cache', [
		'game_id' => $player->getGameID(),
		'max_ports' => $maxNumberOfPorts,
		'goods_allowed' => $db->escapeObject($tradeGoods),
		'races_allowed' => $db->escapeObject($tradeRaces),
		'start_sector_id' => $startSectorID,
		'end_sector_id' => $endSectorID,
		'routes_for_port' => $routesForPort,
		'max_distance' => $maxDistance,
		'routes' => $db->escapeObject($routesMerged, true),
	]);
	debug('Found Routes: #' . count($routesMerged));
	return $routesMerged;
}
