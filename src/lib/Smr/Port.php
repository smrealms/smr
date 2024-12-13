<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\Combat\Weapon\CombatDrones;
use Smr\Combat\Weapon\Weapon;
use Smr\Exceptions\CachedPortNotFound;
use Smr\Exceptions\PathNotFound;
use Smr\Pages\Player\AttackPortConfirm;
use Smr\Pages\Player\AttackPortLootProcessor;
use Smr\Pages\Player\AttackPortPayoutProcessor;
use Smr\Pages\Player\AttackPortProcessor;
use Smr\Pages\Player\CurrentSector;
use Smr\Traits\RaceID;

class Port {

	use RaceID;

	/** @var array<int, array<int, self>> */
	protected static array $CACHE_PORTS = [];
	/** @var array<int, array<int, array<int, self|false>>> */
	protected static array $CACHE_CACHED_PORTS = [];

	public const DAMAGE_NEEDED_FOR_ALIGNMENT_CHANGE = 300; // single player
	protected const DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE = 325; // all attackers
	protected const CHANCE_TO_DOWNGRADE = 1;
	protected const TIME_FEDS_STAY = 1800;
	protected const MAX_FEDS_BONUS = 4000;
	protected const BASE_CDS = 725;
	protected const CDS_PER_LEVEL = 100;
	protected const CDS_PER_TEN_MIL_CREDITS = 25;
	protected const BASE_DEFENCES = 500;
	protected const DEFENCES_PER_LEVEL = 700;
	protected const DEFENCES_PER_TEN_MIL_CREDITS = 250;
	protected const BASE_REFRESH_PER_HOUR = [
		'1' => 150,
		'2' => 110,
		'3' => 70,
	];
	protected const REFRESH_PER_GOOD = .9;
	protected const TIME_TO_CREDIT_RAID = 10800; // 3 hours
	protected const GOODS_TRADED_MONEY_MULTIPLIER = 50;
	protected const BASE_PAYOUT = 0.85; // fraction of credits for looting
	public const RAZE_PAYOUT = 0.75; // fraction of base payout for razing
	public const CLAIM_PAYOUT = 0.5; // fraction of base payout for claiming
	public const KILLER_RELATIONS_LOSS = 45; // relations lost by killer in PR

	public const SQL = 'sector_id = :sector_id AND game_id = :game_id';
	/** @var array{sector_id: int, game_id: int} */
	public readonly array $SQLID;

	protected int $shields;
	protected int $combatDrones;
	protected int $armour;
	protected int $reinforceTime;
	protected int $attackStarted;
	protected int $level;
	protected int $credits;
	protected int $upgrade;
	protected int $experience;

	/** @var array<int, int> */
	protected array $goodAmounts;
	/** @var array<int, bool> */
	protected array $goodAmountsChanged = [];
	/** @var array<int, TransactionType> */
	protected array $goodTransactions;
	/** @var array<int, bool> */
	protected array $goodTransactionsChanged = [];
	/** @var array<int, int> */
	protected array $goodDistances;

	protected bool $cachedVersion = false;
	protected int $cachedTime;
	protected bool $cacheIsValid = true;

	protected bool $hasChanged = false;
	protected bool $isNew = false;

	public static function clearCache(): void {
		self::$CACHE_PORTS = [];
		self::$CACHE_CACHED_PORTS = [];
	}

	/**
	 * @return array<int, self>
	 */
	public static function getGalaxyPorts(int $gameID, int $galaxyID, bool $forceUpdate = false): array {
		$db = Database::getInstance();
		// Use a left join so that we populate the cache for every sector
		$dbResult = $db->read('SELECT port.* FROM port LEFT JOIN sector USING(game_id, sector_id) WHERE game_id = :game_id AND galaxy_id = :galaxy_id', [
			'game_id' => $db->escapeNumber($gameID),
			'galaxy_id' => $db->escapeNumber($galaxyID),
		]);
		$galaxyPorts = [];
		foreach ($dbResult->records() as $dbRecord) {
			$sectorID = $dbRecord->getInt('sector_id');
			$port = self::getPort($gameID, $sectorID, $forceUpdate, $dbRecord);
			// Only return those ports that exist
			if ($port->exists()) {
				$galaxyPorts[$sectorID] = $port;
			}
		}
		return $galaxyPorts;
	}

	public static function getPort(int $gameID, int $sectorID, bool $forceUpdate = false, ?DatabaseRecord $dbRecord = null): self {
		if ($forceUpdate || !isset(self::$CACHE_PORTS[$gameID][$sectorID])) {
			self::$CACHE_PORTS[$gameID][$sectorID] = new self($gameID, $sectorID, $dbRecord);
		}
		return self::$CACHE_PORTS[$gameID][$sectorID];
	}

	public static function removePort(int $gameID, int $sectorID): void {
		$db = Database::getInstance();
		$SQLID = [
			'game_id' => $db->escapeNumber($gameID),
			'sector_id' => $db->escapeNumber($sectorID),
		];
		$db->delete('port', $SQLID);
		$db->delete('port_has_goods', $SQLID);
		$db->delete('player_visited_port', $SQLID);
		$db->delete('player_attacks_port', $SQLID);
		$db->delete('port_info_cache', $SQLID);
		unset(self::$CACHE_PORTS[$gameID][$sectorID]);
	}

	public static function createPort(int $gameID, int $sectorID): self {
		if (!isset(self::$CACHE_PORTS[$gameID][$sectorID])) {
			$p = new self($gameID, $sectorID);
			self::$CACHE_PORTS[$gameID][$sectorID] = $p;
		}
		return self::$CACHE_PORTS[$gameID][$sectorID];
	}

	public static function savePorts(): void {
		foreach (self::$CACHE_PORTS as $gamePorts) {
			foreach ($gamePorts as $port) {
				$port->update();
			}
		}
	}

	public static function getBaseExperience(int $cargo, int $distance): float {
		return ($cargo / 13) * $distance;
	}

	protected function __construct(
		protected readonly int $gameID,
		protected readonly int $sectorID,
		?DatabaseRecord $dbRecord = null,
	) {
		$this->cachedTime = Epoch::time();
		$db = Database::getInstance();
		$this->SQLID = [
			'sector_id' => $db->escapeNumber($sectorID),
			'game_id' => $db->escapeNumber($gameID),
		];

		if ($dbRecord === null) {
			$dbResult = $db->read('SELECT * FROM port WHERE ' . self::SQL, $this->SQLID);
			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
			}
		}

		if ($dbRecord !== null) {
			$this->shields = $dbRecord->getInt('shields');
			$this->combatDrones = $dbRecord->getInt('combat_drones');
			$this->armour = $dbRecord->getInt('armour');
			$this->reinforceTime = $dbRecord->getInt('reinforce_time');
			$this->attackStarted = $dbRecord->getInt('attack_started');
			$this->raceID = $dbRecord->getInt('race_id');
			$this->level = $dbRecord->getInt('level');
			$this->credits = $dbRecord->getInt('credits');
			$this->upgrade = $dbRecord->getInt('upgrade');
			$this->experience = $dbRecord->getInt('experience');

			$this->checkDefenses();
			$this->getGoods();
			$this->checkForUpgrade();
		} else {
			$this->isNew = true;
			$this->shields = 0;
			$this->combatDrones = 0;
			$this->armour = 0;
			$this->reinforceTime = 0;
			$this->attackStarted = 0;
			$this->raceID = RACE_NEUTRAL;
			$this->level = 0;
			$this->credits = 0;
			$this->upgrade = 0;
			$this->experience = 0;

			$this->goodAmounts = [];
			$this->goodTransactions = [];
		}
	}

	public function checkDefenses(): void {
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
			if (Epoch::time() < $this->getReinforceTime() + self::TIME_FEDS_STAY) {
				$federalMod = (self::TIME_FEDS_STAY - (Epoch::time() - $this->getReinforceTime())) / self::TIME_FEDS_STAY;
				$federalMod = max(0, IRound($federalMod * self::MAX_FEDS_BONUS));
				$defences += $federalMod;
				$cds += IRound($federalMod / 10);
			}
			$this->setShields($defences);
			$this->setArmour($defences);
			$this->setCDs($cds);
			if ($this->getCredits() === 0) {
				$this->setCreditsToDefault();
			}
			$db = Database::getInstance();
			$db->delete('player_attacks_port', $this->SQLID);
		}
	}

	/**
	 * Used for the automatic resupplying of all goods over time
	 */
	private function restockGood(int $goodID, int $secondsSinceLastUpdate): void {
		if ($secondsSinceLastUpdate <= 0) {
			return;
		}

		$goodClass = TradeGood::get($goodID)->class;
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
	private function getGoods(): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot call getGoods on cached port');
		}
		if (!isset($this->goodAmounts)) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM port_has_goods WHERE ' . self::SQL . ' ORDER BY good_id ASC', $this->SQLID);
			foreach ($dbResult->records() as $dbRecord) {
				$goodID = $dbRecord->getInt('good_id');
				$this->goodTransactions[$goodID] = $dbRecord->getStringEnum('transaction_type', TransactionType::class);
				$this->goodAmounts[$goodID] = $dbRecord->getInt('amount');

				$secondsSinceLastUpdate = Epoch::time() - $dbRecord->getInt('last_update');
				$this->restockGood($goodID, $secondsSinceLastUpdate);
			}
		}
	}

	/**
	 * @param array<int> $goodIDs
	 * @return array<int, TradeGood>
	 */
	private function getVisibleGoods(array $goodIDs, ?AbstractPlayer $player = null): array {
		$visibleGoods = [];
		foreach ($goodIDs as $goodID) {
			$good = TradeGood::get($goodID);
			if ($player === null || $player->meetsAlignmentRestriction($good->alignRestriction)) {
				$visibleGoods[$goodID] = $good;
			}
		}
		return $visibleGoods;
	}

	/**
	 * Get goods that can be sold by $player to the port
	 *
	 * @return array<int, TradeGood>
	 */
	public function getVisibleGoodsSold(?AbstractPlayer $player = null): array {
		return $this->getVisibleGoods($this->getSellGoodIDs(), $player);
	}

	/**
	 * Get goods that can be bought by $player from the port
	 *
	 * @return array<int, TradeGood>
	 */
	public function getVisibleGoodsBought(?AbstractPlayer $player = null): array {
		return $this->getVisibleGoods($this->getBuyGoodIDs(), $player);
	}

	/**
	 * @return array<int>
	 */
	public function getAllGoodIDs(): array {
		return array_keys($this->goodTransactions);
	}

	/**
	 * Get IDs of goods that can be sold to the port by the trader
	 *
	 * @return array<int>
	 */
	public function getSellGoodIDs(): array {
		return array_keys($this->goodTransactions, TransactionType::Sell, true);
	}

	/**
	 * Get IDs of goods that can be bought from the port by the trader
	 *
	 * @return array<int>
	 */
	public function getBuyGoodIDs(): array {
		return array_keys($this->goodTransactions, TransactionType::Buy, true);
	}

	public function getGoodDistance(int $goodID): int {
		if (!isset($this->goodDistances[$goodID])) {
			// Calculate distance to the opposite of the offered transaction
			$x = new TradeGoodTransaction(
				goodID: $goodID,
				transactionType: $this->getGoodTransaction($goodID)->opposite(),
			);
			try {
				$distance = Plotter::findDistanceToX($x, $this->getSector(), true)->getDistance();
			} catch (PathNotFound) {
				$distance = 0;
			}
			$this->goodDistances[$goodID] = $distance;
		}
		return $this->goodDistances[$goodID];
	}

	/**
	 * Returns the transaction type for this good (Buy or Sell).
	 * Note: this is the player's transaction, not the port's.
	 */
	public function getGoodTransaction(int $goodID): TransactionType {
		foreach (TransactionType::cases() as $transaction) {
			if ($this->hasGood($goodID, $transaction)) {
				return $transaction;
			}
		}
		throw new Exception('Port does not trade goodID ' . $goodID);
	}

	/**
	 * @return array<int, TransactionType>
	 */
	public function getGoodTransactions(): array {
		return $this->goodTransactions;
	}

	public function hasGood(int $goodID, ?TransactionType $type = null): bool {
		$hasGood = isset($this->goodTransactions[$goodID]);
		if ($type === null || $hasGood === false) {
			return $hasGood;
		}
		return $this->goodTransactions[$goodID] === $type;
	}

	private function setGoodAmount(int $goodID, int $amount, bool $doUpdate = true): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		// The new amount must be between 0 and the max for this good
		$amount = max(0, min($amount, TradeGood::get($goodID)->maxPortAmount));
		if ($this->getGoodAmount($goodID) === $amount) {
			return;
		}
		$this->goodAmounts[$goodID] = $amount;

		if ($doUpdate) {
			// This goodID will be changed in the db during `update()`
			$this->goodAmountsChanged[$goodID] = true;
		}
	}

	public function getGoodAmount(int $goodID): int {
		return $this->goodAmounts[$goodID];
	}

	public function decreaseGood(TradeGood $good, int $amount, bool $doRefresh): void {
		$this->setGoodAmount($good->id, $this->getGoodAmount($good->id) - $amount);
		if ($doRefresh === true) {
			//get id of goods to replenish
			$this->refreshGoods($good->class, $amount);
		}
	}

	public function increaseGoodAmount(int $goodID, int $amount): void {
		$this->setGoodAmount($goodID, $this->getGoodAmount($goodID) + $amount);
	}

	public function decreaseGoodAmount(int $goodID, int $amount): void {
		$this->setGoodAmount($goodID, $this->getGoodAmount($goodID) - $amount);
	}

	/**
	 * Adds extra stock to goods in the tier above a good that was traded
	 */
	protected function refreshGoods(int $classTraded, int $amountTraded): void {
		$refreshAmount = IRound($amountTraded * self::REFRESH_PER_GOOD);
		//refresh goods that need it
		$refreshClass = $classTraded + 1;
		foreach ($this->getAllGoodIDs() as $goodID) {
			$goodClass = TradeGood::get($goodID)->class;
			if ($goodClass === $refreshClass) {
				$this->increaseGoodAmount($goodID, $refreshAmount);
			}
		}
	}

	protected function tradeGoods(TradeGood $good, int $goodsTraded, int $exp): void {
		$goodsTradedMoney = $goodsTraded * self::GOODS_TRADED_MONEY_MULTIPLIER;
		$this->increaseUpgrade($goodsTradedMoney);
		$this->increaseCredits($goodsTradedMoney);
		$this->increaseExperience($exp);
		$this->decreaseGood($good, $goodsTraded, true);
	}

	public function buyGoods(TradeGood $good, int $goodsTraded, int $idealPrice, int $bargainPrice, int $exp): void {
		$this->tradeGoods($good, $goodsTraded, $exp);
		// Limit upgrade/credits to prevent massive increases in a single trade
		$cappedBargainPrice = min(max($idealPrice, $goodsTraded * 1000), $bargainPrice);
		$this->increaseUpgrade($cappedBargainPrice);
		$this->increaseCredits($cappedBargainPrice);
	}

	public function sellGoods(TradeGood $good, int $goodsTraded, int $exp): void {
		$this->tradeGoods($good, $goodsTraded, $exp);
	}

	public function stealGoods(TradeGood $good, int $goodsTraded): void {
		$this->decreaseGood($good, $goodsTraded, false);
	}

	public function checkForUpgrade(): int {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot upgrade a cached port!');
		}
		$upgrades = 0;
		while ($this->upgrade >= $this->getUpgradeRequirement() && $this->level < $this->getMaxLevel()) {
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
	public function upgradeToLevel(int $level): void {
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
	protected function getGoodClassAtLevel(?int $level = null): int {
		if ($level === null) {
			$level = $this->getLevel();
		}
		return match ($level) {
			1, 2 => 1,
			3, 4, 5, 6 => 2,
			7, 8, 9 => 3,
			default => throw new Exception('No good class for level ' . $level),
		};
	}

	protected function selectAndAddGood(int $goodClass): TradeGood {
		$goods = TradeGood::getAll();
		shuffle($goods);
		foreach ($goods as $good) {
			if (!$this->hasGood($good->id) && $good->class === $goodClass) {
				$transactionType = array_rand_value(TransactionType::cases());
				$this->addPortGood($good->id, $transactionType);
				return $good;
			}
		}
		throw new Exception('Failed to add a good!');
	}

	protected function doUpgrade(): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot upgrade a cached port!');
		}

		$this->increaseLevel(1);
		$goodClass = $this->getGoodClassAtLevel();
		$this->selectAndAddGood($goodClass);

		if ($this->getLevel() === 1) {
			// Add 2 extra goods when upgrading to level 1 (i.e. in Uni Gen)
			$this->selectAndAddGood($goodClass);
			$this->selectAndAddGood($goodClass);
		}
	}

	public function getUpgradeRequirement(): int {
		//return round(exp($this->getLevel()/1.7)+3)*1000000;
		return $this->getLevel() * 1000000;
	}

	/**
	 * Manually set port goods.
	 * Only modifies goods that need to change.
	 * Returns false on invalid input.
	 *
	 * @param array<int, TransactionType> $goodTransactions
	 */
	public function setPortGoods(array $goodTransactions): bool {
		// Validate the input list of goods to make sure we have the correct
		// number of each good class for this port level.
		$givenClasses = [];
		foreach (array_keys($goodTransactions) as $goodID) {
			$givenClasses[] = TradeGood::get($goodID)->class;
		}
		$expectedClasses = [1, 1]; // Level 1 has 2 extra Class 1 goods
		foreach (range(1, $this->getLevel()) as $level) {
			$expectedClasses[] = $this->getGoodClassAtLevel($level);
		}
		if ($givenClasses !== $expectedClasses) {
			return false;
		}

		// Remove goods not specified or that have the wrong transaction
		foreach ($this->getAllGoodIDs() as $goodID) {
			if (!isset($goodTransactions[$goodID]) || !$this->hasGood($goodID, $goodTransactions[$goodID])) {
				$this->removePortGood($goodID);
			}
		}
		// Add goods
		foreach ($goodTransactions as $goodID => $trans) {
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
	public function addPortGood(int $goodID, TransactionType $type): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($this->hasGood($goodID, $type)) {
			return;
		}

		$this->goodTransactions[$goodID] = $type;
		// sort ID arrays, since the good ID might not be the largest
		ksort($this->goodTransactions);

		$this->goodAmounts[$goodID] = TradeGood::get($goodID)->maxPortAmount;

		// Flag for update
		$this->cacheIsValid = false;
		$this->goodTransactionsChanged[$goodID] = true; // true => added
	}

	/**
	 * Remove good with given ID from the port.
	 * If the port does not have this good, do nothing.
	 *
	 * NOTE: make sure to adjust the port level appropriately if
	 * calling this function directly.
	 */
	public function removePortGood(int $goodID): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if (!$this->hasGood($goodID)) {
			return;
		}

		unset($this->goodAmounts[$goodID]);
		unset($this->goodAmountsChanged[$goodID]);
		unset($this->goodTransactions[$goodID]);
		unset($this->goodDistances[$goodID]);

		// Flag for update
		$this->cacheIsValid = false;
		$this->goodTransactionsChanged[$goodID] = false; // false => removed
	}

	/**
	 * Returns the number of port level downgrades due to damage taken.
	 */
	public function checkForDowngrade(int $damage): int {
		$numDowngrades = 0;
		$numChances = floor($damage / self::DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE);
		for ($i = 0; $i < $numChances; $i++) {
			if (rand(1, 100) <= self::CHANCE_TO_DOWNGRADE && $this->level > 1) {
				++$numDowngrades;
				$this->doDowngrade();
			}
		}
		return $numDowngrades;
	}

	protected function selectAndRemoveGood(int $goodClass): void {
		// Pick good to remove from the list of goods the port currently has
		$goodIDs = $this->getAllGoodIDs();
		shuffle($goodIDs);

		foreach ($goodIDs as $goodID) {
			$good = TradeGood::get($goodID);
			if ($good->class === $goodClass) {
				$this->removePortGood($good->id);
				return;
			}
		}
		throw new Exception('Failed to remove a good!');
	}

	protected function doDowngrade(): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot downgrade a cached port!');
		}

		$goodClass = $this->getGoodClassAtLevel();
		$this->selectAndRemoveGood($goodClass);

		if ($this->getLevel() === 1) {
			// For level 1 ports, we don't want to have fewer goods
			$newGood = $this->selectAndAddGood($goodClass);
			// Set new good to 0 supply
			// (since other goods are set to 0 when port is destroyed)
			$this->setGoodAmount($newGood->id, 0);
		} else {
			// Don't make the port level 0
			$this->decreaseLevel(1);
		}
		$this->setUpgrade(0);
	}

	/**
	 * @param array<AbstractPlayer> $attackers
	 */
	public function attackedBy(AbstractPlayer $trigger, array $attackers): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot attack a cached port!');
		}

		$trigger->increaseHOF(1, ['Combat', 'Port', 'Number Of Triggers'], HOF_PUBLIC);
		$db = Database::getInstance();
		foreach ($attackers as $attacker) {
			$attacker->increaseHOF(1, ['Combat', 'Port', 'Number Of Attacks'], HOF_PUBLIC);
			$db->replace('player_attacks_port', [
				'game_id' => $this->getGameID(),
				'account_id' => $attacker->getAccountID(),
				'sector_id' => $this->getSectorID(),
				'time' => Epoch::time(),
				'level' => $this->getLevel(),
			]);
		}
		if (!$this->isUnderAttack()) {

			//5 mins per port level
			$nextReinforce = Epoch::time() + $this->getLevel() * 300;

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

			$db->insert('news', [
				'game_id' => $this->getGameID(),
				'time' => Epoch::time(),
				'news_message' => $newsMessage,
				'killer_id' => $trigger->getAccountID(),
				'killer_alliance' => $trigger->getAllianceID(),
				'dead_id' => ACCOUNT_ID_PORT,
			]);
		}
	}

	public function getDisplayName(): string {
		return '<span style="color:yellow;font-variant:small-caps">Port ' . $this->getSectorID() . '</span>';
	}

	public function setShields(int $shields): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($shields < 0) {
			$shields = 0;
		}
		if ($this->shields === $shields) {
			return;
		}
		$this->shields = $shields;
		$this->hasChanged = true;
	}

	public function setArmour(int $armour): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($armour < 0) {
			$armour = 0;
		}
		if ($this->armour === $armour) {
			return;
		}
		$this->armour = $armour;
		$this->hasChanged = true;
	}

	public function setCDs(int $combatDrones): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($combatDrones < 0) {
			$combatDrones = 0;
		}
		if ($this->combatDrones === $combatDrones) {
			return;
		}
		$this->combatDrones = $combatDrones;
		$this->hasChanged = true;
	}

	public function setCreditsToDefault(): void {
		$this->setCredits(2700000 + $this->getLevel() * 1500000 + pow($this->getLevel(), 2) * 300000);
	}

	public function setCredits(int $credits): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($this->credits === $credits) {
			return;
		}
		$this->credits = $credits;
		$this->hasChanged = true;
	}

	public function decreaseCredits(int $credits): void {
		if ($credits < 0) {
			throw new Exception('Cannot decrease negative credits.');
		}
		$this->setCredits($this->getCredits() - $credits);
	}

	public function increaseCredits(int $credits): void {
		if ($credits < 0) {
			throw new Exception('Cannot increase negative credits.');
		}
		$this->setCredits($this->getCredits() + $credits);
	}

	public function setUpgrade(int $upgrade): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($this->getLevel() === $this->getMaxLevel()) {
			$upgrade = 0;
		}
		if ($this->upgrade === $upgrade) {
			return;
		}
		$this->upgrade = $upgrade;
		$this->hasChanged = true;
		$this->checkForUpgrade();
	}

	public function decreaseUpgrade(int $upgrade): void {
		if ($upgrade < 0) {
			throw new Exception('Cannot decrease negative upgrade.');
		}
		$this->setUpgrade($this->getUpgrade() - $upgrade);
	}

	public function increaseUpgrade(int $upgrade): void {
		if ($upgrade < 0) {
			throw new Exception('Cannot increase negative upgrade.');
		}
		$this->setUpgrade($this->getUpgrade() + $upgrade);
	}

	public function setLevel(int $level): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($this->level === $level) {
			return;
		}
		$this->level = $level;
		$this->hasChanged = true;
	}

	public function increaseLevel(int $level): void {
		if ($level < 0) {
			throw new Exception('Cannot increase negative level.');
		}
		$this->setLevel($this->getLevel() + $level);
	}

	public function decreaseLevel(int $level): void {
		if ($level < 0) {
			throw new Exception('Cannot increase negative level.');
		}
		$this->setLevel($this->getLevel() - $level);
	}

	public function setExperience(int $experience): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if ($this->experience === $experience) {
			return;
		}
		$this->experience = $experience;
		$this->hasChanged = true;
	}

	public function increaseExperience(int $experience): void {
		if ($experience < 0) {
			throw new Exception('Cannot increase negative experience.');
		}
		$this->setExperience($this->getExperience() + $experience);
	}

	public function getGameID(): int {
		return $this->gameID;
	}

	public function getGame(): Game {
		return Game::getGame($this->gameID);
	}

	public function getSectorID(): int {
		return $this->sectorID;
	}

	public function getSector(): Sector {
		return Sector::getSector($this->getGameID(), $this->getSectorID());
	}

	public function setRaceID(int $raceID): void {
		if ($this->raceID === $raceID) {
			return;
		}
		$this->raceID = $raceID;
		$this->hasChanged = true;
		$this->cacheIsValid = false;
	}

	public function getLevel(): int {
		return $this->level;
	}

	public static function getMaxLevelByGame(int $gameID): int {
		$game = Game::getGame($gameID);
		if ($game->isGameType(Game::GAME_TYPE_HUNTER_WARS)) {
			$maxLevel = 6;
		} else {
			$maxLevel = 9;
		}
		return $maxLevel;
	}

	public function getMaxLevel(): int {
		return self::getMaxLevelByGame($this->gameID);
	}

	public function getShields(): int {
		return $this->shields;
	}

	public function hasShields(): bool {
		return ($this->getShields() > 0);
	}

	public function getCDs(): int {
		return $this->combatDrones;
	}

	public function hasCDs(): bool {
		return ($this->getCDs() > 0);
	}

	public function getArmour(): int {
		return $this->armour;
	}

	public function hasArmour(): bool {
		return ($this->getArmour() > 0);
	}

	public function getExperience(): int {
		return $this->experience;
	}

	public function getCredits(): int {
		return $this->credits;
	}

	public function getUpgrade(): int {
		return $this->upgrade;
	}

	public function getNumWeapons(): int {
		return $this->getLevel() + 3;
	}

	/**
	 * @return array<Weapon>
	 */
	public function getWeapons(): array {
		$portTurret = Weapon::getWeapon(WEAPON_PORT_TURRET);
		return array_fill(0, $this->getNumWeapons(), $portTurret);
	}

	public function getUpgradePercent(): float {
		return min(1, max(0, $this->upgrade / $this->getUpgradeRequirement()));
	}

	public function getCreditsPercent(): float {
		return min(1, max(0, $this->credits / 32000000));
	}

	public function getReinforcePercent(): float {
		if (!$this->isUnderAttack()) {
			return 0;
		}
		return min(1, max(0, ($this->getReinforceTime() - Epoch::time()) / ($this->getReinforceTime() - $this->getAttackStarted())));
	}

	public function getReinforceTime(): int {
		return $this->reinforceTime;
	}

	public function setReinforceTime(int $reinforceTime): void {
		if ($this->reinforceTime === $reinforceTime) {
			return;
		}
		$this->reinforceTime = $reinforceTime;
		$this->hasChanged = true;
	}

	public function getAttackStarted(): int {
		return $this->attackStarted;
	}

	private function updateAttackStarted(): void {
		$this->setAttackStarted(Epoch::time());
	}

	private function setAttackStarted(int $time): void {
		if ($this->attackStarted === $time) {
			return;
		}
		$this->attackStarted = $time;
		$this->hasChanged = true;
	}

	public function isUnderAttack(): bool {
		return ($this->getReinforceTime() >= Epoch::time());
	}

	public function isBusted(): bool {
		return $this->getArmour() < 1;
	}

	public function exists(): bool {
		return $this->isNew === false || $this->hasChanged === true;
	}

	public function decreaseShields(int $number): void {
		$this->setShields($this->getShields() - $number);
	}

	public function decreaseCDs(int $number): void {
		$this->setCDs($this->getCDs() - $number);
	}

	public function decreaseArmour(int $number): void {
		$this->setArmour($this->getArmour() - $number);
	}

	public function getTradeRestriction(AbstractPlayer $player): string|false {
		if (!$this->exists()) {
			return 'There is no port in this sector!';
		}
		if ($this->getSectorID() !== $player->getSectorID()) {
			return 'That port is not in this sector!';
		}
		if ($player->getRelation($this->getRaceID()) <= RELATIONS_WAR) {
			return 'We will not trade with our enemies!';
		}
		if ($this->isUnderAttack()) {
			return 'We are still repairing damage caused during the last raid.';
		}
		return false;
	}

	public function getIdealPrice(int $goodID, TransactionType $transactionType, int $numGoods, int $relations): int {
		$supply = $this->getGoodAmount($goodID);
		$dist = $this->getGoodDistance($goodID);
		return self::idealPrice($goodID, $transactionType, $numGoods, $relations, $supply, $dist);
	}

	/**
	 * Generic ideal price calculation, given all parameters as input.
	 */
	public static function idealPrice(int $goodID, TransactionType $transactionType, int $numGoods, int $relations, int $supply, int $dist): int {
		$relations = min(1000, $relations); // no effect for higher relations
		$good = TradeGood::get($goodID);
		$base = $good->basePrice * $numGoods;
		$maxSupply = $good->maxPortAmount;

		// Handle distance 0 case (good has no matching trade anywhere)
		$dist = max(1, $dist);

		$distFactor = pow($dist, 1.3);
		if ($transactionType === TransactionType::Sell) {
			$supplyFactor = 1 + ($supply / $maxSupply);
			$relationsFactor = 1.2 + 1.8 * ($relations / 1000); // [0.75-3]
			$scale = 0.088;
		} else { // $transactionType === TransactionType::Buy
			$supplyFactor = 2 - ($supply / $maxSupply);
			$relationsFactor = 3 - 2 * ($relations / 1000);
			$scale = 0.03;
		}
		return IRound($base * $scale * $distFactor * $supplyFactor * $relationsFactor);
	}

	public function getOfferPrice(int $idealPrice, int $relations, TransactionType $transactionType): int {
		$relations = min(1000, $relations); // no effect for higher relations
		$relationsEffect = (2 * $relations + 8000) / 10000; // [0.75-1]

		return match ($transactionType) {
			TransactionType::Buy => max($idealPrice, IFloor($idealPrice * (2 - $relationsEffect))),
			TransactionType::Sell => min($idealPrice, ICeil($idealPrice * $relationsEffect)),
		};
	}

	/**
	 * Return the fraction of max exp earned.
	 */
	public function calculateExperiencePercent(int $idealPrice, int $bargainPrice, TransactionType $transactionType): float {
		if ($bargainPrice === $idealPrice) {
			return 1;
		}

		$offerPriceNoRelations = $this->getOfferPrice($idealPrice, 0, $transactionType);

		// Avoid division by 0 in the case where the ideal price is so small
		// that relations have no impact on the offered price.
		$denom = max(1, abs($idealPrice - $offerPriceNoRelations));

		$expPercent = 1 - abs(($idealPrice - $bargainPrice) / $denom);
		return max(0, min(1, $expPercent));
	}

	public function getRaidWarningHREF(): string {
		return (new AttackPortConfirm())->href();
	}

	public function getAttackHREF(): string {
		return (new AttackPortProcessor())->href();
	}

	public function getPayoutHREF(PortPayoutType $payoutType): string {
		if ($this->getCredits() > 0) {
			$container = new AttackPortPayoutProcessor($payoutType);
		} else {
			$container = new CurrentSector(message: 'The fate of this port has already been decided.');
		}
		return $container->href();
	}

	public function getLootGoodHREF(int $boughtGoodID): string {
		$container = new AttackPortLootProcessor($boughtGoodID);
		return $container->href();
	}

	public function isCachedVersion(): bool {
		return $this->cachedVersion;
	}

	public function getCachedTime(): int {
		return $this->cachedTime;
	}

	protected function setCachedTime(int $cachedTime): void {
		$this->cachedTime = $cachedTime;
	}

	public function updateSectorPlayersCache(): void {
		$accountIDs = [];
		$sectorPlayers = $this->getSector()->getPlayers();
		foreach ($sectorPlayers as $sectorPlayer) {
			$accountIDs[] = $sectorPlayer->getAccountID();
		}
		$this->addCachePorts($accountIDs);
	}

	public function addCachePort(int $accountID): void {
		$this->addCachePorts([$accountID]);
	}

	/**
	 * @param array<int> $accountIDs
	 */
	public function addCachePorts(array $accountIDs): bool {
		if (count($accountIDs) > 0 && $this->exists()) {
			$db = Database::getInstance();
			$cache = $db->escapeObject($this, true);
			$cacheHash = $db->escapeString(md5($cache));

			// Insert dummy rows that don't exist yet for these primary keys
			$query = 'INSERT IGNORE INTO player_visited_port
					(account_id, game_id, sector_id, visited, port_info_hash) VALUES ';
			$params = [
				'game_id' => $db->escapeNumber($this->getGameID()),
				'sector_id' => $db->escapeNumber($this->getSectorID()),
				'visited' => 0, // to be updated below
				'port_info_hash' => '', // to be updated below
			];
			$paramSql = [];
			foreach ($accountIDs as $accountID) {
				$params['account_id' . $accountID] = $accountID;
				$paramSql[] = '(:account_id' . $accountID . ', :game_id, :sector_id, :visited, :port_info_hash)';
			}
			$query .= implode(',', $paramSql);
			$db->write($query, $params);

			$db->write('INSERT IGNORE INTO port_info_cache
						(game_id, sector_id, port_info_hash, port_info)
						VALUES (:game_id, :sector_id, :hash, :cache)', [
				'sector_id' => $db->escapeNumber($this->getSectorID()),
				'game_id' => $db->escapeNumber($this->getGameID()),
				'hash' => $cacheHash,
				'cache' => $cache,
			]);

			// We can't use the SQL member here because CachePorts don't have it
			$db->write('UPDATE player_visited_port SET visited = :cached_time, port_info_hash = :port_info_hash WHERE visited <= :cached_time AND account_id IN (:account_ids) AND sector_id = :sector_id AND game_id = :game_id LIMIT :limit', [
				'cached_time' => $db->escapeNumber($this->getCachedTime()),
				'port_info_hash' => $cacheHash,
				'sector_id' => $db->escapeNumber($this->getSectorID()),
				'game_id' => $db->escapeNumber($this->getGameID()),
				'account_ids' => $db->escapeArray($accountIDs),
				'limit' => count($accountIDs),
			]);

			unset($cache);
			return true;
		}
		return false;
	}

	/**
	 * @throws \Smr\Exceptions\CachedPortNotFound If the cached port is not found in the database.
	 */
	public static function getCachedPort(int $gameID, int $sectorID, int $accountID, bool $forceUpdate = false): self {
		if ($forceUpdate || !isset(self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID])) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT visited, port_info
						FROM player_visited_port
						JOIN port_info_cache USING (game_id,sector_id,port_info_hash)
						WHERE account_id = :account_id
							AND game_id = :game_id
							AND sector_id = :sector_id LIMIT 1', [
				'account_id' => $db->escapeNumber($accountID),
				'game_id' => $db->escapeNumber($gameID),
				'sector_id' => $db->escapeNumber($sectorID),
			]);

			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
				self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID] = $dbRecord->getObject('port_info', true);
				self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID]->setCachedTime($dbRecord->getInt('visited'));
			} else {
				self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID] = false;
			}
		}
		$port = self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID];
		if ($port === false) {
			throw new CachedPortNotFound();
		}
		return $port;
	}

	// This is a magic method used when serializing an Port instance.
	// It designates which members should be included in the serialization.
	public function __sleep() {
		// We omit `goodAmounts` and `goodDistances` so that the hash of the
		// serialized object is the same for all players. This greatly improves
		// cache efficiency.
		return ['gameID', 'sectorID', 'raceID', 'level', 'goodTransactions'];
	}

	public function __wakeup() {
		$this->cachedVersion = true;
	}

	public function update(): void {
		if ($this->isCachedVersion()) {
			throw new Exception('Cannot update a cached port!');
		}
		if (!$this->exists()) {
			return;
		}

		// If any cached members (see `__sleep`) changed, update the cached port
		$db = Database::getInstance();
		if (!$this->cacheIsValid) {
			$this->updateSectorPlayersCache();
			// route_cache tells NPC's where they can trade
			$db->delete('route_cache', [
				'game_id' => $this->getGameID(),
			]);
		}

		// If any fields in the `port` table have changed, update table
		if ($this->hasChanged) {
			$params = [
				'experience' => $this->getExperience(),
				'shields' => $this->getShields(),
				'armour' => $this->getArmour(),
				'combat_drones' => $this->getCDs(),
				'level' => $this->getLevel(),
				'credits' => $this->getCredits(),
				'upgrade' => $this->getUpgrade(),
				'reinforce_time' => $this->getReinforceTime(),
				'attack_started' => $this->getAttackStarted(),
				'race_id' => $this->getRaceID(),
			];
			if ($this->isNew === false) {
				$db->update('port', $params, $this->SQLID);
			} else {
				$db->insert('port', [...$params, ...$this->SQLID]);
				$this->isNew = false;
			}
			$this->hasChanged = false;
		}

		// Update the port good amounts if they have been changed
		// (Note: `restockGoods` alone does not trigger this)
		foreach ($this->goodAmountsChanged as $goodID => $doUpdate) {
			if (!$doUpdate) {
				continue;
			}
			$amount = $this->getGoodAmount($goodID);
			$db->update(
				'port_has_goods',
				[
					'amount' => $amount,
					'last_update' => Epoch::time(),
				],
				[
					...$this->SQLID,
					'good_id' => $goodID,
				],
			);
			unset($this->goodAmountsChanged[$goodID]);
		}

		// Handle any goods that were added or removed
		foreach ($this->goodTransactionsChanged as $goodID => $status) {
			if ($status === true) {
				// add the good
				$db->replace('port_has_goods', [
					...$this->SQLID,
					'good_id' => $goodID,
					'transaction_type' => $this->getGoodTransaction($goodID)->value,
					'amount' => $this->getGoodAmount($goodID),
					'last_update' => Epoch::time(),
				]);
			} else {
				// remove the good
				$db->delete('port_has_goods', [
					...$this->SQLID,
					'good_id' => $goodID,
				]);
			}
			unset($this->goodTransactionsChanged[$goodID]);
		}

	}

	/**
	 * @param non-empty-array<int, AbstractPlayer> $targetPlayers
	 * @return PortCombatResults
	 */
	public function shootPlayers(array $targetPlayers): array {
		$results = ['Port' => $this, 'TotalDamage' => 0, 'TotalDamagePerTargetPlayer' => [], 'TotalShotsPerTargetPlayer' => []];
		foreach ($targetPlayers as $targetPlayer) {
			$results['TotalDamagePerTargetPlayer'][$targetPlayer->getAccountID()] = 0;
			$results['TotalShotsPerTargetPlayer'][$targetPlayer->getAccountID()] = 0;
		}
		if ($this->isBusted()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		$weapons = $this->getWeapons();
		foreach ($weapons as $orderID => $weapon) {
			do {
				$targetPlayer = array_rand_value($targetPlayers);
			} while ($results['TotalShotsPerTargetPlayer'][$targetPlayer->getAccountID()] > min($results['TotalShotsPerTargetPlayer']));
			$results['Weapons'][$orderID] = $weapon->shootPlayerAsPort($this, $targetPlayer);
			$results['TotalShotsPerTargetPlayer'][$targetPlayer->getAccountID()]++;
			if ($results['Weapons'][$orderID]['Hit']) {
				if (!isset($results['Weapons'][$orderID]['ActualDamage'])) {
					throw new Exception('Weapon hit without providing ActualDamage!');
				}
				$totalDamage = $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
				$results['TotalDamage'] += $totalDamage;
				$results['TotalDamagePerTargetPlayer'][$targetPlayer->getAccountID()] += $totalDamage;
			}
		}
		if ($this->hasCDs()) {
			$thisCDs = new CombatDrones($this->getCDs(), true);
			$results['Drones'] = $thisCDs->shootPlayerAsPort($this, array_rand_value($targetPlayers));
			$totalDamage = $results['Drones']['ActualDamage']['TotalDamage'];
			$results['TotalDamage'] += $totalDamage;
			$results['TotalDamagePerTargetPlayer'][$results['Drones']['Target']->getAccountID()] += $totalDamage;
		}
		return $results;
	}

	/**
	 * @param WeaponDamageData $damage
	 * @return TakenDamageData
	 */
	public function takeDamage(array $damage): array {
		$alreadyDead = $this->isBusted();
		$shieldDamage = 0;
		$cdDamage = 0;
		$armourDamage = 0;
		if (!$alreadyDead) {
			$shieldDamage = $this->takeDamageToShields($damage['Shield']);
			if ($shieldDamage === 0 || $damage['Rollover']) {
				$cdMaxDamage = $damage['Armour'] - $shieldDamage;
				if ($shieldDamage === 0 && $this->hasShields()) {
					$cdMaxDamage = IFloor($cdMaxDamage * DRONES_BEHIND_SHIELDS_DAMAGE_PERCENT);
				}
				$cdDamage = $this->takeDamageToCDs($cdMaxDamage);
				if (!$this->hasShields() && ($cdDamage === 0 || $damage['Rollover'])) {
					$armourMaxDamage = $damage['Armour'] - $shieldDamage - $cdDamage;
					$armourDamage = $this->takeDamageToArmour($armourMaxDamage);
				}
			}
		}

		return [
			'KillingShot' => !$alreadyDead && $this->isBusted(),
			'TargetAlreadyDead' => $alreadyDead,
			'Shield' => $shieldDamage,
			'CDs' => $cdDamage,
			'NumCDs' => $cdDamage / CD_ARMOUR,
			'HasCDs' => $this->hasCDs(),
			'Armour' => $armourDamage,
			'TotalDamage' => $shieldDamage + $cdDamage + $armourDamage,
		];
	}

	protected function takeDamageToShields(int $damage): int {
		$actualDamage = min($this->getShields(), $damage);
		$this->decreaseShields($actualDamage);
		return $actualDamage;
	}

	protected function takeDamageToCDs(int $damage): int {
		$actualDamage = min($this->getCDs(), IFloor($damage / CD_ARMOUR));
		$this->decreaseCDs($actualDamage);
		return $actualDamage * CD_ARMOUR;
	}

	protected function takeDamageToArmour(int $damage): int {
		$actualDamage = min($this->getArmour(), IFloor($damage));
		$this->decreaseArmour($actualDamage);
		return $actualDamage;
	}

	/**
	 * @return array<Player>
	 */
	public function getAttackersToCredit(): array {
		//get all players involved for HoF
		$attackers = [];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT player.* FROM player_attacks_port JOIN player USING (game_id, account_id) WHERE game_id = :game_id AND player_attacks_port.sector_id = :sector_id AND time > :credit_time', [
			...$this->SQLID,
			'credit_time' => $db->escapeNumber(Epoch::time() - self::TIME_TO_CREDIT_RAID),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$attackers[] = Player::getPlayer($dbRecord->getInt('account_id'), $this->getGameID(), false, $dbRecord);
		}
		return $attackers;
	}

	/**
	 * Identifies if the given $player is a credited attacker of this port.
	 */
	public function isCreditedAttacker(AbstractPlayer $player): bool {
		foreach (self::getAttackersToCredit() as $attacker) {
			if ($player->equals($attacker)) {
				return true;
			}
		}
		return false;
	}

	protected function creditCurrentAttackersForKill(): void {
		//get all players involved for HoF
		$attackers = $this->getAttackersToCredit();
		foreach ($attackers as $attacker) {
			$attacker->increaseHOF($this->level, ['Combat', 'Port', 'Levels Raided'], HOF_PUBLIC);
			$attacker->increaseHOF(1, ['Combat', 'Port', 'Total Raided'], HOF_PUBLIC);
		}
	}

	protected function payout(AbstractPlayer $killer, int $credits, string $payoutType): bool {
		if ($this->getCredits() === 0) {
			return false;
		}
		$killer->increaseCredits($credits);
		$killer->increaseHOF($credits, ['Combat', 'Port', 'Money', 'Gained'], HOF_PUBLIC);
		$attackers = $this->getAttackersToCredit();
		foreach ($attackers as $attacker) {
			$attacker->increaseHOF(1, ['Combat', 'Port', $payoutType], HOF_PUBLIC);
		}
		$this->setCredits(0);
		return true;
	}

	/**
	 * Get a reduced fraction of the credits stored in the port for razing
	 * after a successful port raid.
	 */
	public function razePort(AbstractPlayer $killer): int {
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
	public function lootPort(AbstractPlayer $killer): int {
		$credits = IFloor($this->getCredits() * self::BASE_PAYOUT);
		$this->payout($killer, $credits, 'Looted');
		return $credits;
	}

	/**
	 * Claim port for your race after a successful port raid.
	 */
	public function claimPort(AbstractPlayer $killer): int {
		$credits = IFloor($this->getCredits() * self::BASE_PAYOUT * self::CLAIM_PAYOUT);
		if ($this->payout($killer, $credits, 'Claimed')) {
			$this->setRaceID($killer->getRaceID());
		}
		return $credits;
	}

	/**
	 * Permanently destroy the port after a successful port raid.
	 */
	public function destroyPort(AbstractPlayer $killer): int {
		$credits = 0;
		if ($this->payout($killer, $credits, 'Destroyed')) {
			// News Entry
			$news = $this->getDisplayName() . ' was destroyed by ' . $killer->getBBLink();
			if ($killer->hasCustomShipName()) {
				$named_ship = strip_tags($killer->getCustomShipName(), '<font><span><img>');
				$news .= ' flying <span class="yellow">' . $named_ship . '</span>';
			}
			$news .= ' in Sector&nbsp;' . Globals::getSectorBBLink($this->getSectorID());
			$db = Database::getInstance();
			$db->insert('news', [
				'game_id' => $this->getGameID(),
				'time' => Epoch::time(),
				'news_message' => $news,
				'killer_id' => $killer->getAccountID(),
				'killer_alliance' => $killer->getAllianceID(),
				'dead_id' => ACCOUNT_ID_PORT,
			]);

			// This wasn't a nice thing to do
			$killer->decreaseAlignment(ALIGNMENT_LOSS_PORT_DESTROY);

			self::removePort($this->getGameID(), $this->getSectorID());
		}
		return $credits;
	}

	/**
	 * Can ports be destroyed after a successful port raid?
	 */
	public function canBeDestroyed(): bool {
		return $this->getGame()->canDestroyPorts() && ($this->getLevel() === 1);
	}

	/**
	 * @return array{}
	 */
	public function killPortByPlayer(AbstractPlayer $killer): array {
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
		$db = Database::getInstance();
		$db->insert('news', [
			'game_id' => $this->getGameID(),
			'time' => Epoch::time(),
			'news_message' => $news,
			'killer_id' => $killer->getAccountID(),
			'killer_alliance' => $killer->getAllianceID(),
			'dead_id' => ACCOUNT_ID_PORT,
		]);

		// Killer gets a relations change and a bounty if port is taken
		$killerBounty = $killer->getExperience() * $this->getLevel();
		$killer->getActiveBounty(BountyType::HQ)->increaseCredits($killerBounty);
		$killer->increaseHOF($killerBounty, ['Combat', 'Port', 'Bounties', 'Gained'], HOF_PUBLIC);

		$killer->decreaseRelations(self::KILLER_RELATIONS_LOSS, $this->getRaceID());
		$killer->increaseHOF(self::KILLER_RELATIONS_LOSS, ['Combat', 'Port', 'Relation', 'Loss'], HOF_PUBLIC);

		return [];
	}

}
