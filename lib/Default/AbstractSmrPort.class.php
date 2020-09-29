<?php declare(strict_types=1);
class AbstractSmrPort {
	use Traits\RaceID;

	protected static $CACHE_PORTS = array();
	protected static $CACHE_CACHED_PORTS = array();
	
	const DAMAGE_NEEDED_FOR_ALIGNMENT_CHANGE = 300; // single player
	const DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE = 325; // all attackers
	const CHANCE_TO_DOWNGRADE = 1;
	const TIME_FEDS_STAY = 1800;
	const MAX_FEDS_BONUS = 4000;
	const BASE_CDS = 725;
	const CDS_PER_LEVEL = 100;
	const CDS_PER_TEN_MIL_CREDITS = 25;
	const BASE_DEFENCES = 500;
	const DEFENCES_PER_LEVEL = 700;
	const DEFENCES_PER_TEN_MIL_CREDITS = 250;
	const MAX_LEVEL = 9;
	const BASE_REFRESH_PER_HOUR = array(
		'1' => 150,
		'2' => 110,
		'3' => 70
	);
	const REFRESH_PER_GOOD = .9;
	const TIME_TO_CREDIT_RAID = 10800; // 3 hours
	const GOODS_TRADED_MONEY_MULTIPLIER = 50;
	const BASE_PAYOUT = 0.9; // fraction of credits for looting
	const RAZE_PAYOUT = 0.75; // fraction of base payout for razing
	
	protected $db;
	
	protected $gameID;
	protected $sectorID;
	protected $shields;
	protected $combatDrones;
	protected $armour;
	protected $reinforceTime;
	protected $attackStarted;
	protected $level;
	protected $credits;
	protected $upgrade;
	protected $experience;

	protected $goodIDs = array('All' => array(), 'Sell' => array(), 'Buy' => array());
	protected $goodAmounts;
	protected $goodAmountsChanged = array();
	protected $goodDistances;
	
	protected $cachedVersion = false;
	protected $cachedTime = TIME;
	protected $cacheIsValid = true;
	
	protected $SQL;
	
	protected $hasChanged = false;
	protected $isNew = false;
	
	public static function refreshCache() {
		foreach (self::$CACHE_PORTS as $gameID => &$gamePorts) {
			foreach ($gamePorts as $sectorID => &$port) {
				$port = self::getPort($gameID, $sectorID, true);
			}
		}
	}
	
	public static function clearCache() {
		self::$CACHE_PORTS = array();
		self::$CACHE_CACHED_PORTS = array();
	}

	public static function getGalaxyPorts($gameID, $galaxyID, $forceUpdate = false) {
		$db = new SmrMySqlDatabase();
		// Use a left join so that we populate the cache for every sector
		$db->query('SELECT port.*, sector_id FROM sector LEFT JOIN port USING(game_id, sector_id) WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND galaxy_id = ' . $db->escapeNumber($galaxyID));
		$galaxyPorts = [];
		while ($db->nextRecord()) {
			$sectorID = $db->getInt('sector_id');
			$port = self::getPort($gameID, $sectorID, $forceUpdate, $db);
			// Only return those ports that exist
			if ($port->exists()) {
				$galaxyPorts[$sectorID] = $port;
			}
		}
		return $galaxyPorts;
	}

	public static function getPort($gameID, $sectorID, $forceUpdate = false, $db = null) {
		if ($forceUpdate || !isset(self::$CACHE_PORTS[$gameID][$sectorID])) {
			self::$CACHE_PORTS[$gameID][$sectorID] = new SmrPort($gameID, $sectorID, $db);
		}
		return self::$CACHE_PORTS[$gameID][$sectorID];
	}

	public static function removePort($gameID, $sectorID) {
		$db = new SmrMySqlDatabase();
		$SQL = 'game_id = ' . $db->escapeNumber($gameID) . '
		        AND sector_id = ' . $db->escapeNumber($sectorID);
		$db->query('DELETE FROM port WHERE ' . $SQL);
		$db->query('DELETE FROM port_has_goods WHERE ' . $SQL);
		$db->query('DELETE FROM player_visited_port WHERE ' . $SQL);
		$db->query('DELETE FROM player_attacks_port WHERE ' . $SQL);
		$db->query('DELETE FROM port_info_cache WHERE ' . $SQL);
		self::$CACHE_PORTS[$gameID][$sectorID] = null;
		unset(self::$CACHE_PORTS[$gameID][$sectorID]);
	}

	public static function createPort($gameID, $sectorID) {
		if (!isset(self::$CACHE_PORTS[$gameID][$sectorID])) {
			$p = new SmrPort($gameID, $sectorID);
			self::$CACHE_PORTS[$gameID][$sectorID] = $p;
		}
		return self::$CACHE_PORTS[$gameID][$sectorID];
	}
	
	public static function savePorts() {
		foreach (self::$CACHE_PORTS as $gamePorts) {
			foreach ($gamePorts as $port) {
				$port->update();
			}
		}
	}

	public static function getBaseExperience($cargo, $distance) {
		return ($cargo / 13) * $distance;
	}
	
	protected function __construct($gameID, $sectorID, $db = null) {
		$this->db = new SmrMySqlDatabase();
		$this->SQL = 'sector_id = ' . $this->db->escapeNumber($sectorID) . ' AND game_id = ' . $this->db->escapeNumber($gameID);

		if (isset($db)) {
			$this->isNew = !$db->hasField('game_id');
		} else {
			$db = $this->db;
			$db->query('SELECT * FROM port WHERE ' . $this->SQL . ' LIMIT 1');
			$this->isNew = !$db->nextRecord();
		}

		$this->gameID = (int)$gameID;
		$this->sectorID = (int)$sectorID;
		if (!$this->isNew) {
			$this->shields = $db->getInt('shields');
			$this->combatDrones = $db->getInt('combat_drones');
			$this->armour = $db->getInt('armour');
			$this->reinforceTime = $db->getInt('reinforce_time');
			$this->attackStarted = $db->getInt('attack_started');
			$this->raceID = $db->getInt('race_id');
			$this->level = $db->getInt('level');
			$this->credits = $db->getInt('credits');
			$this->upgrade = $db->getInt('upgrade');
			$this->experience = $db->getInt('experience');
			
			$this->checkDefenses();
			$this->getGoods();
			$this->checkForUpgrade();
		} else {
			$this->shields = 0;
			$this->combatDrones = 0;
			$this->armour = 0;
			$this->reinforceTime = 0;
			$this->attackStarted = 0;
			$this->raceID = 1;
			$this->level = 0;
			$this->credits = 0;
			$this->upgrade = 0;
			$this->experience = 0;
		}
	}
	
	public function checkDefenses() {
		if (!$this->isUnderAttack()) {
			$defences = self::BASE_DEFENCES + $this->getLevel() * self::DEFENCES_PER_LEVEL;
			$cds = self::BASE_CDS + $this->getLevel() * self::CDS_PER_LEVEL;
			// Upgrade modifier
			$defences += max(0, IRound(self::DEFENCES_PER_LEVEL * $this->getUpgrade() / $this->getUpgradeRequirement()));
			$cds += max(0, IRound(self::CDS_PER_LEVEL * $this->getUpgrade() / $this->getUpgradeRequirement()));
			// Credits modifier
			$defences += max(0, IRound(self::DEFENCES_PER_TEN_MIL_CREDITS * $this->getCredits() / 10000000));
			$cds += max(0, IRound(self::CDS_PER_TEN_MIL_CREDITS * $this->getCredits() / 10000000));
			// Defences restock (check for fed arrival)
			if (TIME < $this->getReinforceTime() + self::TIME_FEDS_STAY) {
				$federalMod = (self::TIME_FEDS_STAY - (TIME - $this->getReinforceTime())) / self::TIME_FEDS_STAY;
				$federalMod = max(0, IRound($federalMod * self::MAX_FEDS_BONUS));
				$defences += $federalMod;
				$cds += IRound($federalMod / 10);
			}
			$this->setShields($defences);
			$this->setArmour($defences);
			$this->setCDs($cds);
			if ($this->getCredits() == 0) {
				$this->setCreditsToDefault();
			}
			$this->db->query('DELETE FROM player_attacks_port WHERE ' . $this->SQL);
		}
	}
	
	/**
	 * Used for the automatic resupplying of all goods over time
	 */
	private function restockGood($goodID, $secondsSinceLastUpdate) {
		if ($secondsSinceLastUpdate <= 0) {
			return;
		}

		$goodClass = Globals::getGood($goodID)['Class'];
		$refreshPerHour = self::BASE_REFRESH_PER_HOUR[$goodClass] * $this->getGame()->getGameSpeed();
		$refreshPerSec = $refreshPerHour / 3600;
		$amountToAdd = IFloor($secondsSinceLastUpdate * $refreshPerSec);

		// We will not save automatic resupplying in the database,
		// because the stock can be correctly recalculated based on the
		// last_update time. We will only do the update for player actions
		// that affect the stock. This avoids many unnecessary db queries.
		$doUpdateDB = false;
		$amount = $this->getGoodAmount($goodID);
		$this->setGoodAmount($goodID, $amount + $amountToAdd, $doUpdateDB);
	}
	
	// Sets the class members that identify port trade goods
	private function getGoods() {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot call getGoods on cached port');
		}
		if (empty($this->goodIDs['All'])) {
			$this->db->query('SELECT * FROM port_has_goods WHERE ' . $this->SQL . ' ORDER BY good_id ASC');
			while ($this->db->nextRecord()) {
				$goodID = $this->db->getInt('good_id');
				$transactionType = $this->db->getField('transaction_type');
				$this->goodAmounts[$goodID] = $this->db->getInt('amount');
				$this->goodIDs[$transactionType][] = $goodID;
				$this->goodIDs['All'][] = $goodID;

				$secondsSinceLastUpdate = TIME - $this->db->getInt('last_update');
				$this->restockGood($goodID, $secondsSinceLastUpdate);
			}
		}
	}

	private function getVisibleGoods($transaction, AbstractSmrPlayer $player = null) {
		$goodIDs = $this->goodIDs[$transaction];
		if ($player == null) {
			return $goodIDs;
		} else {
			return array_filter($goodIDs, function($goodID) use ($player) {
				$good = Globals::getGood($goodID);
				return $player->meetsAlignmentRestriction($good['AlignRestriction']);
			});
		}
	}

	/**
	 * Get IDs of goods that can be sold by $player to the port
	 */
	public function getVisibleGoodsSold(AbstractSmrPlayer $player = null) {
		return $this->getVisibleGoods('Sell', $player);
	}

	/**
	 * Get IDs of goods that can be bought by $player from the port
	 */
	public function getVisibleGoodsBought(AbstractSmrPlayer $player = null) {
		return $this->getVisibleGoods('Buy', $player);
	}
	
	public function getAllGoodIDs() {
		return $this->goodIDs['All'];
	}
	
	/**
	 * Get IDs of goods that can be sold to the port
	 */
	public function getSoldGoodIDs() {
		return $this->goodIDs['Sell'];
	}
	
	/**
	 * Get IDs of goods that can be bought from the port
	 */
	public function getBoughtGoodIDs() {
		return $this->goodIDs['Buy'];
	}
	
	public function getGood($goodID) {
		if ($this->hasGood($goodID)) {
			return Globals::getGood($goodID);
		} else {
			$return = false;
			return $return;
		}
	}
	
	public function getGoodDistance($goodID) {
		if (!isset($this->goodDistances[$goodID])) {
			$x = $this->getGood($goodID);
			if ($x === false) {
				throw new Exception('This port does not have this good!');
			}
			if ($this->hasGood($goodID, 'Buy')) {
				$x['TransactionType'] = 'Sell';
			} else {
				$x['TransactionType'] = 'Buy';
			}
			$di = Plotter::findDistanceToX($x, $this->getSector(), true);
			if (is_object($di)) {
				$di = $di->getRelativeDistance();
			}
			$this->goodDistances[$goodID] = max(1, $di);
		}
		return $this->goodDistances[$goodID];
	}
	
	/**
	 * Returns the transaction type for this good (Buy or Sell).
	 * Note: this is the player's transaction, not the port's.
	 */
	public function getGoodTransaction($goodID) {
		foreach (array('Buy', 'Sell') as $transaction) {
			if ($this->hasGood($goodID, $transaction)) {
				return $transaction;
			}
		}
	}
	
	public function hasGood($goodID, $type = false) {
		if ($type === false) {
			$type = 'All';
		}
		return in_array($goodID, $this->goodIDs[$type]);
	}
	
	private function setGoodAmount($goodID, $amount, $doUpdate = true) {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		// The new amount must be between 0 and the max for this good
		$amount = max(0, min($amount, $this->getGood($goodID)['Max']));
		if ($this->getGoodAmount($goodID) == $amount) {
			return;
		}
		$this->goodAmounts[$goodID] = $amount;

		if ($doUpdate) {
			// This goodID will be changed in the db during `update()`
			$this->goodAmountsChanged[$goodID] = true;
		}
	}
	
	public function getGoodAmount($goodID) {
		return $this->goodAmounts[$goodID];
	}
	
	public function decreaseGood(array $good, $amount, $doRefresh) {
		$this->setGoodAmount($good['ID'], $this->getGoodAmount($good['ID']) - $amount);
		if ($doRefresh === true) {
			//get id of goods to replenish
			$this->refreshGoods($good['Class'], $amount);
		}
	}
	
	public function increaseGoodAmount($goodID, $amount) {
		$this->setGoodAmount($goodID, $this->getGoodAmount($goodID) + $amount);
	}
	
	public function decreaseGoodAmount($goodID, $amount) {
		$this->setGoodAmount($goodID, $this->getGoodAmount($goodID) - $amount);
	}
	
	/**
	 * Adds extra stock to goods in the tier above a good that was traded
	 */
	protected function refreshGoods($classTraded, $amountTraded) {
		$refreshAmount = IRound($amountTraded * self::REFRESH_PER_GOOD);
		//refresh goods that need it
		$refreshClass = $classTraded + 1;
		foreach ($this->getAllGoodIDs() as $goodID) {
			$goodClass = Globals::getGood($goodID)['Class'];
			if ($goodClass == $refreshClass) {
				$this->increaseGoodAmount($goodID, $refreshAmount);
			}
		}
	}

	protected function tradeGoods(array $good, $goodsTraded, $exp) {
		$goodsTradedMoney = $goodsTraded * self::GOODS_TRADED_MONEY_MULTIPLIER;
		$this->increaseUpgrade($goodsTradedMoney);
		$this->increaseCredits($goodsTradedMoney);
		$this->increaseExperience($exp);
		$this->decreaseGood($good, $goodsTraded, true);
	}
	
	public function buyGoods(array $good, $goodsTraded, $idealPrice, $bargainPrice, $exp) {
		$this->tradeGoods($good, $goodsTraded, $exp);
		$this->increaseUpgrade(min(max($idealPrice, $goodsTraded * 1000), $bargainPrice));
		$this->increaseCredits($bargainPrice);
	}
	
	public function sellGoods(array $good, $goodsTraded, $idealPrice, $bargainPrice, $exp) {
		$this->tradeGoods($good, $goodsTraded, $exp);
	}
	
	public function stealGoods(array $good, $goodsTraded) {
		$this->decreaseGood($good, $goodsTraded, false);
	}
	
	public function checkForUpgrade() {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot upgrade a cached port!');
		}
		$upgrades = 0;
		while ($this->upgrade >= $this->getUpgradeRequirement() && $this->level < 9) {
			++$upgrades;
			$this->decreaseUpgrade($this->getUpgradeRequirement());
			$this->decreaseCredits($this->getUpgradeRequirement());
			$this->doUpgrade();
		}
		return $upgrades;
	}
	
	/**
	 * This function should only be used in universe creation to set
	 * ports to a specific level.
	 */
	public function upgradeToLevel($level) {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot upgrade a cached port!');
		}
		while ($this->getLevel() < $level) {
			$this->doUpgrade();
		}
		while ($this->getLevel() > $level) {
			$this->doDowngrade();
		}
	}

	/**
	 * Returns the good class associated with the given level.
	 * If no level specified, will use the current port level.
	 * This is useful for determining what trade goods to add/remove.
	 */
	protected function getGoodClassAtLevel($level = false) {
		if ($level === false) {
			$level = $this->getLevel();
		}
		if ($level <= 2) {
			return 1;
		} elseif ($level <= 6) {
			return 2;
		} else {
			return 3;
		}
	}

	protected function selectAndAddGood($goodClass) {
		$GOODS = Globals::getGoods();
		shuffle($GOODS);
		foreach ($GOODS as $good) {
			if (!$this->hasGood($good['ID']) && $good['Class'] == $goodClass) {
				$transactionType = rand(1, 2) == 1 ? 'Buy' : 'Sell';
				$this->addPortGood($good['ID'], $transactionType);
				return $good;
			}
		}
		throw new Exception('Failed to add a good!');
	}
	
	protected function doUpgrade() {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot upgrade a cached port!');
		}

		$this->increaseLevel(1);
		$goodClass = $this->getGoodClassAtLevel();
		$this->selectAndAddGood($goodClass);

		if ($this->getLevel() == 1) {
			// Add 2 extra goods when upgrading to level 1 (i.e. in Uni Gen)
			$this->selectAndAddGood($goodClass);
			$this->selectAndAddGood($goodClass);
		}
	}
	
	public function getUpgradeRequirement() {
//		return round(exp($this->getLevel()/1.7)+3)*1000000;
		return $this->getLevel() * 1000000;
	}

	/**
	 * Manually set port goods.
	 * Input must be an array of good_id => transaction.
	 * Only modifies goods that need to change.
	 * Returns false on invalid input.
	 */
	public function setPortGoods(array $goods) {
		// Validate the input list of goods to make sure we have the correct
		// number of each good class for this port level.
		$givenClasses = [];
		foreach (array_keys($goods) as $goodID) {
			$givenClasses[] = Globals::getGood($goodID)['Class'];
		}
		$expectedClasses = [1, 1]; // Level 1 has 2 extra Class 1 goods
		foreach (range(1, $this->getLevel()) as $level) {
			$expectedClasses[] = $this->getGoodClassAtLevel($level);
		}
		if ($givenClasses != $expectedClasses) {
			return false;
		}

		// Remove goods not specified or that have the wrong transaction
		foreach ($this->getAllGoodIDs() as $goodID) {
			if (!isset($goods[$goodID]) || !$this->hasGood($goodID, $goods[$goodID])) {
				$this->removePortGood($goodID);
			}
		}
		// Add goods
		foreach ($goods as $goodID => $trans) {
			$this->addPortGood($goodID, $trans);
		}
		return true;
	}

	/**
	 * Add good with given ID to the port, with transaction $type
	 * as either "Buy" or "Sell", meaning the player buys or sells.
	 * If the port already has this transaction, do nothing.
	 *
	 * NOTE: make sure to adjust the port level appropriately if
	 * calling this function directly.
	 */
	public function addPortGood($goodID, $type) {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($this->hasGood($goodID, $type)) {
			return;
		}

		$this->goodIDs['All'][] = $goodID;
		$this->goodIDs[$type][] = $goodID;
		// sort ID arrays, since the good ID might not be the largest
		sort($this->goodIDs['All']);
		sort($this->goodIDs[$type]);

		$this->goodAmounts[$goodID] = Globals::getGood($goodID)['Max'];
		$this->cacheIsValid = false;
		$this->db->query('REPLACE INTO port_has_goods (game_id, sector_id, good_id, transaction_type, amount, last_update) VALUES (' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeNumber($this->getSectorID()) . ',' . $this->db->escapeNumber($goodID) . ',' . $this->db->escapeString($type) . ',' . $this->db->escapeNumber($this->getGoodAmount($goodID)) . ',' . $this->db->escapeNumber(TIME) . ')');
		$this->db->query('DELETE FROM route_cache WHERE game_id=' . $this->db->escapeNumber($this->getGameID()));
	}

	/**
	 * Remove good with given ID from the port.
	 * If the port does not have this good, do nothing.
	 *
	 * NOTE: make sure to adjust the port level appropriately if
	 * calling this function directly.
	 */
	public function removePortGood($goodID) {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if (!$this->hasGood($goodID)) {
			return;
		}
		if (($key = array_search($goodID, $this->goodIDs['All'])) !== false) {
			array_splice($this->goodIDs['All'], $key, 1);
		}
		if (($key = array_search($goodID, $this->goodIDs['Buy'])) !== false) {
			array_splice($this->goodIDs['Buy'], $key, 1);
		} elseif (($key = array_search($goodID, $this->goodIDs['Sell'])) !== false) {
			array_splice($this->goodIDs['Sell'], $key, 1);
		}
		
		$this->cacheIsValid = false;
		$this->db->query('DELETE FROM port_has_goods WHERE ' . $this->SQL . ' AND good_id=' . $this->db->escapeNumber($goodID) . ';');
		$this->db->query('DELETE FROM route_cache WHERE game_id=' . $this->db->escapeNumber($this->getGameID()));
	}

	/**
	 * Returns the number of port level downgrades due to damage taken.
	 */
	public function checkForDowngrade($damage) : int {
		$numDowngrades = 0;
		$numChances = floor($damage / self::DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE);
		for ($i = 0; $i < $numChances; $i++) {
			if (mt_rand(1, 100) <= self::CHANCE_TO_DOWNGRADE && $this->level > 1) {
				++$numDowngrades;
				$this->doDowngrade();
			}
		}
		return $numDowngrades;
	}
	
	protected function selectAndRemoveGood($goodClass) {
		// Pick good to remove from the list of goods the port currently has
		$goodIDs = $this->getAllGoodIDs();
		shuffle($goodIDs);

		foreach ($goodIDs as $goodID) {
			$good = Globals::getGood($goodID);
			if ($good['Class'] == $goodClass) {
				$this->removePortGood($good['ID']);
				return;
			}
		}
		throw new Exception('Failed to remove a good!');
	}

	protected function doDowngrade() {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot downgrade a cached port!');
		}

		$goodClass = $this->getGoodClassAtLevel();
		$this->selectAndRemoveGood($goodClass);

		if ($this->getLevel() == 1) {
			// For level 1 ports, we don't want to have fewer goods
			$newGood = $this->selectAndAddGood($goodClass);
			// Set new good to 0 supply
			// (since other goods are set to 0 when port is destroyed)
			$this->setGoodAmount($newGood['ID'], 0);
		} else {
			// Don't make the port level 0
			$this->decreaseLevel(1);
		}
		$this->setUpgrade(0);
	}
	
	public function attackedBy(AbstractSmrPlayer $trigger, array $attackers) {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot attack a cached port!');
		}

		$trigger->increaseHOF(1, array('Combat', 'Port', 'Number Of Triggers'), HOF_PUBLIC);
		foreach ($attackers as $attacker) {
			$attacker->increaseHOF(1, array('Combat', 'Port', 'Number Of Attacks'), HOF_PUBLIC);
			$this->db->query('REPLACE INTO player_attacks_port (game_id, account_id, sector_id, time, level) VALUES
							(' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($attacker->getAccountID()) . ', ' . $this->db->escapeNumber($this->getSectorID()) . ', ' . $this->db->escapeNumber(TIME) . ', ' . $this->db->escapeNumber($this->getLevel()) . ')');
		}
		if (!$this->isUnderAttack()) {
	
			//5 mins per port level
			$nextReinforce = TIME + $this->getLevel() * 300;
			
			$this->setReinforceTime($nextReinforce);
			$this->updateAttackStarted();
			//add news
			$newsMessage = '<span class="red bold">*MAYDAY* *MAYDAY*</span> A distress beacon has been activated by the port in sector ' . Globals::getSectorBBLink($this->getSectorID()) . '. It is under attack by ';
			if ($trigger->hasAlliance()) {
				$newsMessage .= 'members of ' . $trigger->getAllianceBBLink();
			} else {
				$newsMessage .= $trigger->getBBLink();
			}
			
			$newsMessage .= '. The Federal Government is offering ';
			$bounty = number_format(floor($trigger->getLevelID() * DEFEND_PORT_BOUNTY_PER_LEVEL));

			if ($trigger->hasAlliance()) {
				$newsMessage .= 'bounties of <span class="creds">' . $bounty . '</span> credits for the deaths of any raiding members of ' . $trigger->getAllianceBBLink();
			} else {
				$newsMessage .= 'a bounty of <span class="creds">' . $bounty . '</span> credits for the death of ' . $trigger->getBBLink();
			}
			$newsMessage .= ' prior to the destruction of the port, or until federal forces arrive to defend the port.';
//			$irc_message = '[k00,01]The port in sector [k11]'.$this->sectorID.'[k00] is under attack![/k]';
			$this->db->query('INSERT INTO news (game_id, time, news_message, type,killer_id,killer_alliance,dead_id) VALUES (' . $this->db->escapeNumber($this->getGameID()) . ',' . $this->db->escapeNumber(TIME) . ',' . $this->db->escapeString($newsMessage) . ',\'REGULAR\',' . $this->db->escapeNumber($trigger->getAccountID()) . ',' . $this->db->escapeNumber($trigger->getAllianceID()) . ',' . $this->db->escapeNumber(ACCOUNT_ID_PORT) . ')');
		}
	}
	
	public function getDisplayName() {
		return '<span style="color:yellow;font-variant:small-caps">Port ' . $this->getSectorID() . '</span>';
	}
	
	public function setShields($shields) {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($shields < 0) {
			$shields = 0;
		}
		if ($this->shields == $shields) {
			return;
		}
		$this->shields = $shields;
		$this->hasChanged = true;
	}
	
	public function setArmour($armour) {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($armour < 0) {
			$armour = 0;
		}
		if ($this->armour == $armour) {
			return;
		}
		$this->armour = $armour;
		$this->hasChanged = true;
	}
	
	public function setCDs($combatDrones) {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($combatDrones < 0) {
			$combatDrones = 0;
		}
		if ($this->combatDrones == $combatDrones) {
			return;
		}
		$this->combatDrones = $combatDrones;
		$this->hasChanged = true;
	}

	public function setCreditsToDefault() {
		$this->setCredits(2700000 + $this->getLevel() * 1500000 + pow($this->getLevel(), 2) * 300000);
	}

	public function setCredits($credits) {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($this->credits == $credits) {
			return;
		}
		$this->credits = $credits;
		$this->hasChanged = true;
	}
	
	public function decreaseCredits($credits) {
		if ($credits < 0) {
			throw new Exception('Cannot decrease negative credits.');
		}
		$this->setCredits($this->getCredits() - $credits);
	}
	
	public function increaseCredits($credits) {
		if ($credits < 0) {
			throw new Exception('Cannot increase negative credits.');
		}
		$this->setCredits($this->getCredits() + $credits);
	}
	
	public function setUpgrade($upgrade) {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($this->getLevel() == $this->getMaxLevel()) {
			$upgrade = 0;
		}
		if ($this->upgrade == $upgrade) {
			return;
		}
		$this->upgrade = $upgrade;
		$this->hasChanged = true;
		$this->checkForUpgrade();
	}
	
	public function decreaseUpgrade($upgrade) {
		if ($upgrade < 0) {
			throw new Exception('Cannot decrease negative upgrade.');
		}
		$this->setUpgrade($this->getUpgrade() - $upgrade);
	}
	
	public function increaseUpgrade($upgrade) {
		if ($upgrade < 0) {
			throw new Exception('Cannot increase negative upgrade.');
		}
		$this->setUpgrade($this->getUpgrade() + $upgrade);
	}
	
	public function setLevel($level) {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($this->level == $level) {
			return;
		}
		$this->level = $level;
		$this->hasChanged = true;
	}
	
	public function increaseLevel($level) {
		if ($level < 0) {
			throw new Exception('Cannot increase negative level.');
		}
		$this->setLevel($this->getLevel() + $level);
	}
	
	public function decreaseLevel($level) {
		if ($level < 0) {
			throw new Exception('Cannot increase negative level.');
		}
		$this->setLevel($this->getLevel() - $level);
	}
	
	public function setExperience($experience) {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($this->experience == $experience) {
			return;
		}
		$this->experience = $experience;
		$this->hasChanged = true;
	}
	
	public function increaseExperience($experience) {
		if ($experience < 0) {
			throw new Exception('Cannot increase negative experience.');
		}
		$this->setExperience($this->getExperience() + $experience);
	}
	
	public function getGameID() {
		return $this->gameID;
	}

	public function getGame() {
		return SmrGame::getGame($this->gameID);
	}

	public function getSectorID() {
		return $this->sectorID;
	}
	
	public function getSector() {
		return SmrSector::getSector($this->getGameID(), $this->getSectorID());
	}
	
	public function setRaceID($raceID) {
		if ($this->raceID == $raceID) {
			return;
		}
		$this->raceID = $raceID;
		$this->hasChanged = true;
		$this->cacheIsValid = false;
		// route_cache tells NPC's where they can trade
		$this->db->query('DELETE FROM route_cache WHERE game_id=' . $this->db->escapeNumber($this->getGameID()));
	}
	
	public function getLevel() {
		return $this->level;
	}

	public function getMaxLevel() {
		// Hunter Wars redefines this, so use lazy static binding
		return static::MAX_LEVEL;
	}
	
	public function getShields() {
		return $this->shields;
	}
	
	public function hasShields() {
		return ($this->getShields() > 0);
	}
	
	public function getCDs() {
		return $this->combatDrones;
	}
	
	public function hasCDs() {
		return ($this->getCDs() > 0);
	}
	
	public function getArmour() {
		return $this->armour;
	}
	
	public function hasArmour() {
		return ($this->getArmour() > 0);
	}
	
	public function getExperience() {
		return $this->experience;
	}
	
	public function getCredits() {
		return $this->credits;
	}
	
	public function getUpgrade() {
		return $this->upgrade;
	}

	public function getNumWeapons() {
		return $this->getLevel() + 3;
	}

	public function getWeapons() {
		$weapons = array();
		for ($i = 0; $i < $this->getNumWeapons(); ++$i) {
			$weapons[$i] = SmrWeapon::getWeapon(WEAPON_PORT_TURRET);
		}
		return $weapons;
	}

	public function getUpgradePercent() {
		return min(1, max(0, $this->upgrade / $this->getUpgradeRequirement()));
	}
	
	public function getCreditsPercent() {
		return min(1, max(0, $this->credits / 32000000));
	}
	
	public function getReinforcePercent() {
		if (!$this->isUnderAttack()) {
			return 0;
		}
		return min(1, max(0, ($this->getReinforceTime() - TIME) / ($this->getReinforceTime() - $this->getAttackStarted())));
	}
	
	public function getReinforceTime() {
		return $this->reinforceTime;
	}
	
	public function setReinforceTime($reinforceTime) {
		$this->reinforceTime = $reinforceTime;
	}
	
	public function getAttackStarted() {
		return $this->attackStarted;
	}
	
	private function updateAttackStarted() {
		$this->setAttackStarted(TIME);
	}
	
	private function setAttackStarted($time) {
		if ($this->attackStarted == $time) {
			return;
		}
		$this->attackStarted = TIME;
		$this->hasChanged = true;
	}
	
	public function isUnderAttack() {
		return ($this->getReinforceTime() >= TIME);
	}
	
	public function isDestroyed() {
		return ($this->getArmour() < 1 && $this->isUnderAttack());
	}
	
	public function exists() {
		return $this->isNew === false || $this->hasChanged === true;
	}
	
	public function decreaseShields($number) {
		$this->setShields($this->getShields() - $number);
	}
	
	public function decreaseCDs($number) {
		$this->setCDs($this->getCDs() - $number);
	}
	
	public function decreaseArmour($number) {
		$this->setArmour($this->getArmour() - $number);
	}
	
	public function getIdealPrice($goodID, $transactionType, $numGoods, $relations) : int {
		$relations = min(1000, $relations); // no effect for higher relations
		$good = $this->getGood($goodID);
		$base = $good['BasePrice'] * $numGoods;
		$maxSupply = $good['Max'];
		$supply = $this->getGoodAmount($goodID);
		$dist = $this->getGoodDistance($goodID);
	
		$distFactor = pow($dist, 1.3);
		if ($transactionType == 'Sell') {
			// Trader sells
			$supplyFactor = 1 + ($supply / $maxSupply);
			$relationsFactor = 1.2 + 1.8 * ($relations / 1000); // [0.75-3]
			$scale = 0.088;
		} elseif ($transactionType == 'Buy') {
			// Trader buys
			$supplyFactor = 2 - ($supply / $maxSupply);
			$relationsFactor = 3 - 2 * ($relations / 1000);
			$scale = 0.03;
		} else {
			throw new Exception('Unknown transaction type');
		}
		return IRound($base * $scale * $distFactor * $supplyFactor * $relationsFactor);
	}

	public function getOfferPrice(int $idealPrice, int $relations, string $transactionType) : int {
		$relations = min(1000, $relations); // no effect for higher relations
		$relationsEffect = (2 * $relations + 8000) / 10000; // [0.75-1]

		if ($transactionType == 'Buy') {
			$relationsEffect = 2 - $relationsEffect;
			return max($idealPrice, IFloor($idealPrice * $relationsEffect));
		} else {
			return min($idealPrice, ICeil($idealPrice * $relationsEffect));
		}
	}

	/**
	 * Return the fraction of max exp earned.
	 */
	public function calculateExperiencePercent(int $idealPrice, int $bargainPrice, string $transactionType) : float {
		if ($bargainPrice == $idealPrice || $transactionType == 'Steal') {
			// Stealing always gives full exp
			return 1;
		}

		$offerPriceNoRelations = $this->getOfferPrice($idealPrice, 0, $transactionType);
		$expPercent = 1 - abs(($idealPrice - $bargainPrice) / ($idealPrice - $offerPriceNoRelations));
		return max(0, min(1, $expPercent));
	}
	
	public function getRaidWarningHREF() {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'port_attack_warning.php'));
	}
	
	public function getAttackHREF() {
		$container = create_container('port_attack_processing.php');
		$container['port_id'] = $this->getSectorID();
		return SmrSession::getNewHREF($container);
	}
	
	public function getClaimHREF() {
		$container = create_container('port_claim_processing.php');
		$container['port_id'] = $this->getSectorID();
		return SmrSession::getNewHREF($container);
	}

	public function getRazeHREF($justContainer = false) {
		$container = create_container('port_payout_processing.php');
		$container['PayoutType'] = 'Raze';
		return $justContainer === false ? SmrSession::getNewHREF($container) : $container;
	}

	public function getLootHREF($justContainer = false) {
		if ($this->getCredits() > 0) {
			$container = create_container('port_payout_processing.php');
			$container['PayoutType'] = 'Loot';
		} else {
			$container = create_container('skeleton.php', 'current_sector.php');
			$container['msg'] = 'This port has already been looted.';
		}
		return $justContainer === false ? SmrSession::getNewHREF($container) : $container;
	}

	public function getLootGoodHREF($boughtGoodID) {
		$container = create_container('port_loot_processing.php');
		$container['GoodID'] = $boughtGoodID;
		return SmrSession::getNewHREF($container);
	}
	public function isCachedVersion() {
		return $this->cachedVersion;
	}
	public function getCachedTime() {
		return $this->cachedTime;
	}
	protected function setCachedTime($cachedTime) {
		return $this->cachedTime = $cachedTime;
	}
	public function updateSectorPlayersCache() {
		$accountIDs = array();
		$sectorPlayers = $this->getSector()->getPlayers();
		foreach ($sectorPlayers as $sectorPlayer) {
			$accountIDs[] = $sectorPlayer->getAccountID();
		}
		$this->addCachePorts($accountIDs);
	}
	public function addCachePort($accountID) {
		$this->addCachePorts(array($accountID));
	}
	public function addCachePorts(array $accountIDs) {
		if (count($accountIDs) > 0 && $this->exists()) {
			$cache = $this->db->escapeBinary(gzcompress(serialize($this)));
			$cacheHash = $this->db->escapeString(md5($cache));
			//give them the port info
			$query = 'INSERT IGNORE INTO player_visited_port ' .
						'(account_id, game_id, sector_id, visited, port_info_hash) ' .
						'VALUES ';
			foreach ($accountIDs as $accountID) {
				$query .= '(' . $accountID . ', ' . $this->getGameID() . ', ' . $this->getSectorID() . ', 0, \'\'),';
			}
			$query = substr($query, 0, -1);
			$this->db->query($query);
			
			$this->db->query('INSERT IGNORE INTO port_info_cache
						(game_id, sector_id, port_info_hash, port_info)
						VALUES (' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber($this->getSectorID()) . ', ' . $cacheHash . ', ' . $cache . ')');

			// We can't use the SQL member here because CachePorts don't have it
			$this->db->query('UPDATE player_visited_port SET visited=' . $this->db->escapeNumber($this->getCachedTime()) . ', port_info_hash=' . $cacheHash . ' WHERE visited<=' . $this->db->escapeNumber($this->getCachedTime()) . ' AND account_id IN (' . $this->db->escapeArray($accountIDs) . ') AND sector_id=' . $this->db->escapeNumber($this->getSectorID()) . ' AND game_id=' . $this->db->escapeNumber($this->getGameID()) . ' LIMIT ' . count($accountIDs));

			unset($cache);
			return true;
		}
		return false;
	}
	public static function getCachedPort($gameID, $sectorID, $accountID, $forceUpdate = false) {
		if ($forceUpdate || !isset(self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID])) {
			$db = new SmrMySqlDatabase();
			$db->query('SELECT visited, port_info
						FROM player_visited_port
						JOIN port_info_cache USING (game_id,sector_id,port_info_hash)
						WHERE account_id = ' . $db->escapeNumber($accountID) . '
							AND game_id = ' . $db->escapeNumber($gameID) . '
							AND sector_id = ' . $db->escapeNumber($sectorID) . ' LIMIT 1');
			
			if ($db->nextRecord()) {
				self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID] = unserialize(gzuncompress($db->getField('port_info')));
				self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID]->setCachedTime($db->getInt('visited'));
			} else {
				self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID] = false;
			}
		}
		return self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID];
	}
	
	// This is a magic method used when serializing an SmrPort instance.
	// It designates which members should be included in the serialization.
	public function __sleep() {
		// We omit `goodAmounts` and `goodDistances` so that the hash of the
		// serialized object is the same for all players. This greatly improves
		// cache efficiency.
		return array('gameID', 'sectorID', 'raceID', 'level', 'goodIDs');
	}
	
	public function __wakeup() {
		$this->cachedVersion = true;
		$this->db = new SmrMySqlDatabase();
	}
	
	public function update() {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if (!$this->exists()) {
			return;
		}

		// If any cached members (see `__sleep`) changed, update the cached port
		if (!$this->cacheIsValid) {
			$this->updateSectorPlayersCache();
		}

		// If any fields in the `port` table have changed, update table
		if ($this->hasChanged) {
			if ($this->isNew === false) {
				$this->db->query('UPDATE port SET experience = ' . $this->db->escapeNumber($this->getExperience()) .
								', shields = ' . $this->db->escapeNumber($this->getShields()) .
								', armour = ' . $this->db->escapeNumber($this->getArmour()) .
								', combat_drones = ' . $this->db->escapeNumber($this->getCDs()) .
								', level = ' . $this->db->escapeNumber($this->getLevel()) .
								', credits = ' . $this->db->escapeNumber($this->getCredits()) .
								', upgrade = ' . $this->db->escapeNumber($this->getUpgrade()) .
								', reinforce_time = ' . $this->db->escapeNumber($this->getReinforceTime()) .
								', attack_started = ' . $this->db->escapeNumber($this->getAttackStarted()) .
								', race_id = ' . $this->db->escapeNumber($this->getRaceID()) . '
								WHERE ' . $this->SQL . ' LIMIT 1');
			} else {
				$this->db->query('INSERT INTO port (game_id,sector_id,experience,shields,armour,combat_drones,level,credits,upgrade,reinforce_time,attack_started,race_id)
								values
								(' . $this->db->escapeNumber($this->getGameID()) .
								',' . $this->db->escapeNumber($this->getSectorID()) .
								',' . $this->db->escapeNumber($this->getExperience()) .
								',' . $this->db->escapeNumber($this->getShields()) .
								',' . $this->db->escapeNumber($this->getArmour()) .
								',' . $this->db->escapeNumber($this->getCDs()) .
								',' . $this->db->escapeNumber($this->getLevel()) .
								',' . $this->db->escapeNumber($this->getCredits()) .
								',' . $this->db->escapeNumber($this->getUpgrade()) .
								',' . $this->db->escapeNumber($this->getReinforceTime()) .
								',' . $this->db->escapeNumber($this->getAttackStarted()) .
								',' . $this->db->escapeNumber($this->getRaceID()) . ')');
				$this->isNew = false;
			}
			$this->hasChanged = false;
		}

		// Update the port good amounts if they have been changed
		// (Note: `restockGoods` alone does not trigger this)
		foreach ($this->goodAmountsChanged as $goodID => $doUpdate) {
			if (!$doUpdate) { continue; }
			$amount = $this->getGoodAmount($goodID);
			$this->db->query('UPDATE port_has_goods SET amount = ' . $this->db->escapeNumber($amount) . ', last_update = ' . $this->db->escapeNumber(TIME) . ' WHERE ' . $this->SQL . ' AND good_id = ' . $this->db->escapeNumber($goodID) . ' LIMIT 1');
		}
	}
	
	
	public function &shootPlayer(AbstractSmrPlayer $targetPlayer) {
		return $this->shootPlayers(array($targetPlayer));
	}
	
	public function &shootPlayers(array $targetPlayers) {
		$results = array('Port' => $this, 'TotalDamage' => 0, 'TotalDamagePerTargetPlayer' => array());
		foreach ($targetPlayers as $targetPlayer) {
			$results['TotalDamagePerTargetPlayer'][$targetPlayer->getAccountID()] = 0;
			$results['TotalShotsPerTargetPlayer'][$targetPlayer->getAccountID()] = 0;
		}
		if ($this->isDestroyed()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		$weapons = $this->getWeapons();
		foreach ($weapons as $orderID => $weapon) {
			do {
				$targetPlayer = $targetPlayers[array_rand($targetPlayers)];
			} while ($results['TotalShotsPerTargetPlayer'][$targetPlayer->getAccountID()] > min($results['TotalShotsPerTargetPlayer']));
			$results['Weapons'][$orderID] =& $weapon->shootPlayerAsPort($this, $targetPlayer);
			$results['TotalShotsPerTargetPlayer'][$targetPlayer->getAccountID()]++;
			if ($results['Weapons'][$orderID]['Hit']) {
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
				$results['TotalDamagePerTargetPlayer'][$targetPlayer->getAccountID()] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
			}
		}
		if ($this->hasCDs()) {
			$thisCDs = new SmrCombatDrones($this->getGameID(), $this->getCDs(), true);
			$results['Drones'] =& $thisCDs->shootPlayerAsPort($this, $targetPlayers[array_rand($targetPlayers)]);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
			$results['TotalDamagePerTargetPlayer'][$results['Drones']['TargetPlayer']->getAccountID()] += $results['Drones']['ActualDamage']['TotalDamage'];
		}
		return $results;
	}
	
	public function &doWeaponDamage(array $damage) {
		$alreadyDead = $this->isDestroyed();
		$shieldDamage = 0;
		$cdDamage = 0;
		$armourDamage = 0;
		if (!$alreadyDead) {
			if ($damage['Shield'] || !$this->hasShields()) {
				$shieldDamage = $this->doShieldDamage(min($damage['MaxDamage'], $damage['Shield']));
				$damage['MaxDamage'] -= $shieldDamage;
				if (!$this->hasShields() && ($shieldDamage == 0 || $damage['Rollover'])) {
					$cdDamage = $this->doCDDamage(min($damage['MaxDamage'], $damage['Armour']));
					$damage['Armour'] -= $cdDamage;
					$damage['MaxDamage'] -= $cdDamage;
					if (!$this->hasCDs() && ($cdDamage == 0 || $damage['Rollover'])) {
						$armourDamage = $this->doArmourDamage(min($damage['MaxDamage'], $damage['Armour']));
					}
				}
			} else { //hit drones behind shields
				$cdDamage = $this->doCDDamage(IFloor(min($damage['MaxDamage'], $damage['Armour']) * DRONES_BEHIND_SHIELDS_DAMAGE_PERCENT));
			}
		}

		$return = array(
						'KillingShot' => !$alreadyDead && $this->isDestroyed(),
						'TargetAlreadyDead' => $alreadyDead,
						'Shield' => $shieldDamage,
						'HasShields' => $this->hasShields(),
						'CDs' => $cdDamage,
						'NumCDs' => $cdDamage / CD_ARMOUR,
						'HasCDs' => $this->hasCDs(),
						'Armour' => $armourDamage,
						'TotalDamage' => $shieldDamage + $cdDamage + $armourDamage
		);
		return $return;
	}
	
	protected function doShieldDamage($damage) {
		$actualDamage = min($this->getShields(), $damage);
		$this->decreaseShields($actualDamage);
		return $actualDamage;
	}
	
	protected function doCDDamage($damage) {
		$actualDamage = min($this->getCDs(), IFloor($damage / CD_ARMOUR));
		$this->decreaseCDs($actualDamage);
		return $actualDamage * CD_ARMOUR;
	}
	
	protected function doArmourDamage($damage) {
		$actualDamage = min($this->getArmour(), IFloor($damage));
		$this->decreaseArmour($actualDamage);
		return $actualDamage;
	}

	protected function getAttackersToCredit() {
		//get all players involved for HoF
		$attackers = array();
		$this->db->query('SELECT account_id FROM player_attacks_port WHERE ' . $this->SQL . ' AND time > ' . $this->db->escapeNumber(TIME - self::TIME_TO_CREDIT_RAID));
		while ($this->db->nextRecord()) {
			$attackers[] = SmrPlayer::getPlayer($this->db->getInt('account_id'), $this->getGameID());
		}
		return $attackers;
	}

	protected function creditCurrentAttackersForKill() {
		//get all players involved for HoF
		$attackers = $this->getAttackersToCredit();
		foreach ($attackers as $attacker) {
			$attacker->increaseHOF($this->level, array('Combat', 'Port', 'Levels Raided'), HOF_PUBLIC);
			$attacker->increaseHOF(1, array('Combat', 'Port', 'Total Raided'), HOF_PUBLIC);
		}
	}

	protected function payout(AbstractSmrPlayer $killer, $credits, $payoutType) {
		if ($this->getCredits() == 0) {
			return false;
		}
		$killer->increaseCredits($credits);
		$killer->increaseHOF($credits, array('Combat', 'Port', 'Money', 'Gained'), HOF_PUBLIC);
		$attackers = $this->getAttackersToCredit();
		foreach ($attackers as $attacker) {
			$attacker->increaseHOF(1, array('Combat', 'Port', $payoutType), HOF_PUBLIC);
		}
		$this->setCredits(0);
		return true;
	}

	/**
	 * Get a reduced fraction of the credits stored in the port for razing
	 * after a successful port raid.
	 */
	public function razePort(AbstractSmrPlayer $killer) : int {
		$credits = IFloor($this->getCredits() * self::BASE_PAYOUT * self::RAZE_PAYOUT);
		if ($this->payout($killer, $credits, 'Razed')) {
			$this->doDowngrade();
		}
		return $credits;
	}

	/**
	 * Get a fraction of the credits stored in the port for looting after a
	 * successful port raid.
	 */
	public function lootPort(AbstractSmrPlayer $killer) : int {
		$credits = IFloor($this->getCredits() * self::BASE_PAYOUT);
		$this->payout($killer, $credits, 'Looted');
		return $credits;
	}
	
	public function &killPortByPlayer(AbstractSmrPlayer $killer) {
		$return = array();

		// Port is destroyed, so empty the port of all trade goods
		foreach ($this->getAllGoodIDs() as $goodID) {
			$this->setGoodAmount($goodID, 0);
		}

		$this->creditCurrentAttackersForKill();
		
		// News Entry
		$news = $this->getDisplayName() . ' has been successfully raided by ';
		if ($killer->hasAlliance()) {
			$news .= 'the members of <span class="yellow">' . $killer->getAllianceBBLink() . '</span>';
		} else {
			$news .= $killer->getBBLink();
		}
		$this->db->query('INSERT INTO news (game_id, time, news_message, type,killer_id,killer_alliance,dead_id) VALUES (' . $this->db->escapeNumber($this->getGameID()) . ', ' . $this->db->escapeNumber(TIME) . ', ' . $this->db->escapeString($news) . ', \'REGULAR\',' . $this->db->escapeNumber($killer->getAccountID()) . ',' . $this->db->escapeNumber($killer->getAllianceID()) . ',' . $this->db->escapeNumber(ACCOUNT_ID_PORT) . ')');
		// Killer gets a relations change and a bounty if port is taken
		$return['KillerBounty'] = $killer->getExperience() * $this->getLevel();
		$killer->increaseCurrentBountyAmount('HQ', $return['KillerBounty']);
		$killer->increaseHOF($return['KillerBounty'], array('Combat', 'Port', 'Bounties', 'Gained'), HOF_PUBLIC);
		
		$return['KillerRelations'] = 45;
		$killer->decreaseRelations($return['KillerRelations'], $this->getRaceID());
		$killer->increaseHOF($return['KillerRelations'], array('Combat', 'Port', 'Relation', 'Loss'), HOF_PUBLIC);
		
		return $return;
	}

	public function hasX(/*Object*/ $x) {
		if (is_array($x) && $x['Type'] == 'Good') { // instanceof Good) - No Good class yet, so array is the best we can do
			if (isset($x['ID'])) {
				return $this->hasGood($x['ID'], $x['TransactionType'] ?? false);
			}
		}
		return false;
	}
}
