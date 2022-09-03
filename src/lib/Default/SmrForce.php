<?php declare(strict_types=1);

use Smr\Database;
use Smr\DatabaseRecord;
use Smr\Epoch;

class SmrForce {

	/** @var array<int, array<int, array<int, self>>> */
	protected static array $CACHE_FORCES = [];
	/** @var array<int, array<int, array<int, self>>> */
	protected static array $CACHE_SECTOR_FORCES = [];
	/** @var array<int, array<int, bool>> */
	protected static array $TIDIED_UP = [];

	public const LOWEST_MAX_EXPIRE_SCOUTS_ONLY = 432000; // 5 days
	protected const TIME_PER_SCOUT_ONLY = 86400; // 1 = 1 day
	protected const TIME_PERCENT_PER_SCOUT = 0.02; // 1/50th
	protected const TIME_PERCENT_PER_COMBAT = 0.02; // 1/50th
	protected const TIME_PERCENT_PER_MINE = 0.02; // 1/50th
	public const REFRESH_ALL_TIME_PER_STACK = 1; // 1 second

	public const MAX_MINES = 50;
	public const MAX_CDS = 50;
	public const MAX_SDS = 5;

	protected Database $db;
	protected readonly string $SQL;

	protected int $combatDrones = 0;
	protected int $scoutDrones = 0;
	protected int $mines = 0;
	protected int $expire = 0;

	protected bool $isNew;
	protected bool $hasChanged = false;

	public function __sleep() {
		return ['ownerID', 'sectorID', 'gameID'];
	}

	public static function clearCache(): void {
		self::$CACHE_FORCES = [];
		self::$CACHE_SECTOR_FORCES = [];
	}

	public static function saveForces(): void {
		foreach (self::$CACHE_FORCES as $gameForces) {
			foreach ($gameForces as $gameSectorForces) {
				foreach ($gameSectorForces as $forces) {
					$forces->update();
				}
			}
		}
	}

	/**
	 * @return array<int, array<int, self>>
	 */
	public static function getGalaxyForces(int $gameID, int $galaxyID, bool $forceUpdate = false): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT sector_has_forces.* FROM sector_has_forces LEFT JOIN sector USING(game_id, sector_id) WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND galaxy_id = ' . $db->escapeNumber($galaxyID));
		$galaxyForces = [];
		foreach ($dbResult->records() as $dbRecord) {
			$sectorID = $dbRecord->getInt('sector_id');
			$ownerID = $dbRecord->getInt('owner_id');
			$force = self::getForce($gameID, $sectorID, $ownerID, $forceUpdate, $dbRecord);
			self::$CACHE_SECTOR_FORCES[$gameID][$sectorID][$ownerID] = $force;
			$galaxyForces[$sectorID][$ownerID] = $force;
		}
		return $galaxyForces;
	}

	/**
	 * @return array<int, self>
	 */
	public static function getSectorForces(int $gameID, int $sectorID, bool $forceUpdate = false): array {
		if ($forceUpdate || !isset(self::$CACHE_SECTOR_FORCES[$gameID][$sectorID])) {
			self::tidyUpForces(SmrGalaxy::getGalaxyContaining($gameID, $sectorID));
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM sector_has_forces WHERE sector_id = ' . $db->escapeNumber($sectorID) . ' AND game_id=' . $db->escapeNumber($gameID) . ' ORDER BY expire_time ASC');
			$forces = [];
			foreach ($dbResult->records() as $dbRecord) {
				$ownerID = $dbRecord->getInt('owner_id');
				$forces[$ownerID] = self::getForce($gameID, $sectorID, $ownerID, $forceUpdate, $dbRecord);
			}
			self::$CACHE_SECTOR_FORCES[$gameID][$sectorID] = $forces;
		}
		return self::$CACHE_SECTOR_FORCES[$gameID][$sectorID];
	}

	public static function getForce(int $gameID, int $sectorID, int $ownerID, bool $forceUpdate = false, DatabaseRecord $dbRecord = null): self {
		if ($forceUpdate || !isset(self::$CACHE_FORCES[$gameID][$sectorID][$ownerID])) {
			self::tidyUpForces(SmrGalaxy::getGalaxyContaining($gameID, $sectorID));
			$p = new self($gameID, $sectorID, $ownerID, $dbRecord);
			self::$CACHE_FORCES[$gameID][$sectorID][$ownerID] = $p;
		}
		return self::$CACHE_FORCES[$gameID][$sectorID][$ownerID];
	}

	public static function tidyUpForces(SmrGalaxy $galaxyToTidy): void {
		if (!isset(self::$TIDIED_UP[$galaxyToTidy->getGameID()][$galaxyToTidy->getGalaxyID()])) {
			self::$TIDIED_UP[$galaxyToTidy->getGameID()][$galaxyToTidy->getGalaxyID()] = true;
			$db = Database::getInstance();
			$db->write('UPDATE sector_has_forces
						SET refresher=0,
							expire_time = (refresh_at + if(combat_drones+mines=0,
															LEAST(' . $db->escapeNumber(self::LOWEST_MAX_EXPIRE_SCOUTS_ONLY) . ', scout_drones*' . $db->escapeNumber(self::TIME_PER_SCOUT_ONLY) . '),
															LEAST(' . $db->escapeNumber($galaxyToTidy->getMaxForceTime()) . ', (combat_drones*' . $db->escapeNumber(self::TIME_PERCENT_PER_COMBAT) . '+scout_drones*' . $db->escapeNumber(self::TIME_PERCENT_PER_SCOUT) . '+mines*' . $db->escapeNumber(self::TIME_PERCENT_PER_MINE) . ')*' . $db->escapeNumber($galaxyToTidy->getMaxForceTime()) . ')
														))
						WHERE game_id = ' . $db->escapeNumber($galaxyToTidy->getGameID()) . ' AND sector_id >= ' . $db->escapeNumber($galaxyToTidy->getStartSector()) . ' AND sector_id <= ' . $db->escapeNumber($galaxyToTidy->getEndSector()) . ' AND refresher != 0 AND refresh_at <= ' . $db->escapeNumber(Epoch::time()));
			$db->write('DELETE FROM sector_has_forces WHERE expire_time < ' . $db->escapeNumber(Epoch::time()));
		}
	}

	protected function __construct(
		protected readonly int $gameID,
		protected readonly int $sectorID,
		protected readonly int $ownerID,
		DatabaseRecord $dbRecord = null
	) {
		$this->db = Database::getInstance();
		$this->SQL = 'game_id = ' . $this->db->escapeNumber($gameID) . '
		              AND sector_id = ' . $this->db->escapeNumber($sectorID) . '
		              AND owner_id = ' . $this->db->escapeNumber($ownerID);

		if ($dbRecord === null) {
			$dbResult = $this->db->read('SELECT * FROM sector_has_forces WHERE ' . $this->SQL);
			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
			}
		}
		$this->isNew = $dbRecord === null;

		if (!$this->isNew) {
			$this->combatDrones = $dbRecord->getInt('combat_drones');
			$this->scoutDrones = $dbRecord->getInt('scout_drones');
			$this->mines = $dbRecord->getInt('mines');
			$this->expire = $dbRecord->getInt('expire_time');
		}
	}

	public function exists(): bool {
		return ($this->hasCDs() || $this->hasSDs() || $this->hasMines()) && !$this->hasExpired();
	}

	public function hasMaxCDs(): bool {
		return $this->getCDs() >= self::MAX_CDS;
	}

	public function hasMaxSDs(): bool {
		return $this->getSDs() >= self::MAX_SDS;
	}

	public function hasMaxMines(): bool {
		return $this->getMines() >= self::MAX_MINES;
	}

	public function hasCDs(): bool {
		return $this->getCDs() > 0;
	}

	public function hasSDs(): bool {
		return $this->getSDs() > 0;
	}

	public function hasMines(): bool {
		return $this->getMines() > 0;
	}

	public function getCDs(): int {
		return $this->combatDrones;
	}

	public function getSDs(): int {
		return $this->scoutDrones;
	}

	public function getMines(): int {
		return $this->mines;
	}

	public function addMines(int $amount): void {
		if ($amount < 0) {
			throw new Exception('Cannot add negative mines.');
		}
		$this->setMines($this->getMines() + $amount);
	}

	public function addCDs(int $amount): void {
		if ($amount < 0) {
			throw new Exception('Cannot add negative CDs.');
		}
		$this->setCDs($this->getCDs() + $amount);
	}

	public function addSDs(int $amount): void {
		if ($amount < 0) {
			throw new Exception('Cannot add negative SDs.');
		}
		$this->setSDs($this->getSDs() + $amount);
	}

	public function takeMines(int $amount): void {
		if ($amount < 0) {
			throw new Exception('Cannot take negative mines.');
		}
		$this->setMines($this->getMines() - $amount);
	}

	public function takeCDs(int $amount): void {
		if ($amount < 0) {
			throw new Exception('Cannot take negative CDs.');
		}
		$this->setCDs($this->getCDs() - $amount);
	}

	public function takeSDs(int $amount): void {
		if ($amount < 0) {
			throw new Exception('Cannot take negative SDs.');
		}
		$this->setSDs($this->getSDs() - $amount);
	}

	public function setMines(int $amount): void {
		if ($amount < 0) {
			throw new Exception('Cannot set negative mines.');
		}
		if ($amount == $this->getMines()) {
			return;
		}
		$this->hasChanged = true;
		$this->mines = $amount;
	}

	public function setCDs(int $amount): void {
		if ($amount < 0) {
			throw new Exception('Cannot set negative CDs.');
		}
		if ($amount == $this->getCDs()) {
			return;
		}
		$this->hasChanged = true;
		$this->combatDrones = $amount;
	}

	public function setSDs(int $amount): void {
		if ($amount < 0) {
			throw new Exception('Cannot set negative SDs.');
		}
		if ($amount == $this->getSDs()) {
			return;
		}
		$this->hasChanged = true;
		$this->scoutDrones = $amount;
	}

	public function hasExpired(): bool {
		return $this->expire < Epoch::time();
	}

	public function getExpire(): int {
		return $this->expire;
	}

	public function setExpire(int $time): void {
		if ($time < 0) {
			throw new Exception('Cannot set negative expiry.');
		}
		if ($time == $this->getExpire()) {
			return;
		}
		if ($time > Epoch::time() + $this->getMaxExpireTime()) {
			$time = Epoch::time() + $this->getMaxExpireTime();
		}
		$this->hasChanged = true;
		$this->expire = $time;
		if (!$this->isNew) {
			$this->update();
		}
	}

	public function updateExpire(): void {
		// Changed (26/10/05) - scout drones count * 2
		if ($this->getCDs() == 0 && $this->getMines() == 0 && $this->getSDs() > 0) {
			$time = self::TIME_PER_SCOUT_ONLY * $this->getSDs();
		} else {
			$time = ($this->getCDs() * self::TIME_PERCENT_PER_COMBAT + $this->getSDs() * self::TIME_PERCENT_PER_SCOUT + $this->getMines() * self::TIME_PERCENT_PER_MINE) * $this->getMaxGalaxyExpireTime();
		}
		$this->setExpire(Epoch::time() + IFloor($time));
	}

	public function getMaxExpireTime(): int {
		if ($this->hasCDs() || $this->hasMines()) {
			return $this->getMaxGalaxyExpireTime();
		}
		if ($this->hasSDs()) {
			return max(self::LOWEST_MAX_EXPIRE_SCOUTS_ONLY, $this->getMaxGalaxyExpireTime());
		}
		return 0;
	}

	public function getMaxGalaxyExpireTime(): int {
		return $this->getGalaxy()->getMaxForceTime();
	}

	public function getBumpTurnCost(AbstractSmrShip $ship): int {
		$mines = $this->getMines();
		if ($mines <= 1) {
			return 0;
		}
		if ($mines < 10) {
			$turns = 1;
		} elseif ($mines < 25) {
			$turns = 2;
		} else {
			$turns = 3;
		}
		if ($ship->isFederal() || $ship->hasDCS()) {
			$turns -= 1;
		}
		return $turns;
	}

	public function getAttackTurnCost(AbstractSmrShip $ship): int {
		if ($ship->isFederal() || $ship->hasDCS()) {
			return 2;
		}
		return 3;
	}

	public function getOwnerID(): int {
		return $this->ownerID;
	}

	public function getGameID(): int {
		return $this->gameID;
	}

	public function getSector(): SmrSector {
		return SmrSector::getSector($this->getGameID(), $this->getSectorID());
	}

	public function getSectorID(): int {
		return $this->sectorID;
	}

	public function ping(string $pingMessage, AbstractSmrPlayer $playerPinging, bool $skipCheck = false): void {
		if (!$this->hasSDs() && !$skipCheck) {
			return;
		}
		$owner = $this->getOwner();
		if (!$playerPinging->sameAlliance($owner)) {
			$playerPinging->sendMessage($owner->getAccountID(), MSG_SCOUT, $pingMessage, false);
		}
	}

	public function getGalaxy(): SmrGalaxy {
		return SmrGalaxy::getGalaxyContaining($this->getGameID(), $this->getSectorID());
	}

	public function getOwner(): AbstractSmrPlayer {
		return SmrPlayer::getPlayer($this->getOwnerID(), $this->getGameID());
	}

	public function update(): void {
		if (!$this->isNew) {
			if (!$this->exists()) {
				$this->db->write('DELETE FROM sector_has_forces WHERE ' . $this->SQL);
				$this->isNew = true;
			} elseif ($this->hasChanged) {
				$this->db->write('UPDATE sector_has_forces SET combat_drones = ' . $this->db->escapeNumber($this->combatDrones) . ', scout_drones = ' . $this->db->escapeNumber($this->scoutDrones) . ', mines = ' . $this->db->escapeNumber($this->mines) . ', expire_time = ' . $this->db->escapeNumber($this->expire) . ' WHERE ' . $this->SQL);
			}
		} elseif ($this->exists()) {
			$this->db->insert('sector_has_forces', [
				'game_id' => $this->db->escapeNumber($this->gameID),
				'sector_id' => $this->db->escapeNumber($this->sectorID),
				'owner_id' => $this->db->escapeNumber($this->ownerID),
				'combat_drones' => $this->db->escapeNumber($this->combatDrones),
				'scout_drones' => $this->db->escapeNumber($this->scoutDrones),
				'mines' => $this->db->escapeNumber($this->mines),
				'expire_time' => $this->db->escapeNumber($this->expire),
			]);
			$this->isNew = false;
		}
		// This instance is now in sync with the database
		$this->hasChanged = false;
	}

	/**
	 * Update the table fields associated with using Refresh All
	 */
	public function updateRefreshAll(AbstractSmrPlayer $player, int $refreshTime): void {
		$this->db->write('UPDATE sector_has_forces SET refresh_at=' . $this->db->escapeNumber($refreshTime) . ', refresher=' . $this->db->escapeNumber($player->getAccountID()) . ' WHERE ' . $this->SQL);
	}

	public function getExamineDropForcesHREF(): string {
		$container = Page::create('forces_drop.php');
		$container['owner_id'] = $this->getOwnerID();
		return $container->href();
	}

	public function getAttackForcesHREF(): string {
		$container = Page::create('forces_attack_processing.php');
		$container['bump'] = false;
		$container['owner_id'] = $this->getOwnerID();
		return $container->href();
	}

	public function getRefreshHREF(): string {
		$container = Page::create('forces_refresh_processing.php');
		$container['owner_id'] = $this->getOwnerID();
		return $container->href();
	}

	protected function getDropContainer(): Page {
		$container = Page::create('forces_drop_processing.php');
		$container['owner_id'] = $this->getOwnerID();
		return $container;
	}

	public function getDropSDHREF(): string {
		$container = $this->getDropContainer();
		$container['drop_scout_drones'] = 1;
		return $container->href();
	}

	public function getTakeSDHREF(): string {
		$container = $this->getDropContainer();
		$container['take_scout_drones'] = 1;
		return $container->href();
	}

	public function getDropCDHREF(): string {
		$container = $this->getDropContainer();
		$container['drop_combat_drones'] = 1;
		return $container->href();
	}

	public function getTakeCDHREF(): string {
		$container = $this->getDropContainer();
		$container['take_combat_drones'] = 1;
		return $container->href();
	}

	public function getDropMineHREF(): string {
		$container = $this->getDropContainer();
		$container['drop_mines'] = 1;
		return $container->href();
	}

	public function getTakeMineHREF(): string {
		$container = $this->getDropContainer();
		$container['take_mines'] = 1;
		return $container->href();
	}

	public static function getRefreshAllHREF(): string {
		$container = Page::create('forces_mass_refresh.php');
		return $container->href();
	}

	/**
	 * @param array<AbstractSmrPlayer> $targetPlayers
	 * @return array<string, mixed>
	 */
	public function shootPlayers(array $targetPlayers, bool $minesAreAttacker): array {
		$results = ['TotalDamage' => 0];
		if (!$this->exists()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;

		if ($this->hasMines()) {
			$thisMines = new SmrMines($this->getMines());
			$results['Results']['Mines'] = $thisMines->shootPlayerAsForce($this, array_rand_value($targetPlayers), $minesAreAttacker);
			$this->setMines($thisMines->getAmount()); // kamikaze
			$results['TotalDamage'] += $results['Results']['Mines']['ActualDamage']['TotalDamage'];
		}

		if ($this->hasCDs()) {
			$thisCDs = new SmrCombatDrones($this->getCDs());
			$results['Results']['Drones'] = $thisCDs->shootPlayerAsForce($this, array_rand_value($targetPlayers));
			$results['TotalDamage'] += $results['Results']['Drones']['ActualDamage']['TotalDamage'];
		}

		if (!$minesAreAttacker) {
			if ($this->hasSDs()) {
				$thisSDs = new SmrScoutDrones($this->getSDs());
				$results['Results']['Scouts'] = $thisSDs->shootPlayerAsForce($this, array_rand_value($targetPlayers));
				$this->setSDs($thisSDs->getAmount()); // kamikaze
				$results['TotalDamage'] += $results['Results']['Scouts']['ActualDamage']['TotalDamage'];
			}
		}

		$results['ForcesDestroyed'] = !$this->exists();
		return $results;
	}

	/**
	 * @param array<string, int|bool> $damage
	 * @return array<string, int|bool>
	 */
	public function takeDamage(array $damage): array {
		$alreadyDead = !$this->exists();
		$minesDamage = 0;
		$cdDamage = 0;
		$sdDamage = 0;
		if (!$alreadyDead) {
			$minesDamage = $this->takeDamageToMines($damage['Armour']);
			if (!$this->hasMines() && ($minesDamage == 0 || $damage['Rollover'])) {
				$cdMaxDamage = $damage['Armour'] - $minesDamage;
				$cdDamage = $this->takeDamageToCDs($cdMaxDamage);
				if (!$this->hasCDs() && ($cdDamage == 0 || $damage['Rollover'])) {
					$sdMaxDamage = $damage['Armour'] - $minesDamage - $cdDamage;
					$sdDamage = $this->takeDamageToSDs($sdMaxDamage);
				}
			}
		}
		$return = [
						'KillingShot' => !$alreadyDead && !$this->exists(),
						'TargetAlreadyDead' => $alreadyDead,
						'Mines' => $minesDamage,
						'NumMines' => $minesDamage / MINE_ARMOUR,
						'HasMines' => $this->hasMines(),
						'CDs' => $cdDamage,
						'NumCDs' => $cdDamage / CD_ARMOUR,
						'HasCDs' => $this->hasCDs(),
						'SDs' => $sdDamage,
						'NumSDs' => $sdDamage / SD_ARMOUR,
						'HasSDs' => $this->hasSDs(),
						'TotalDamage' => $minesDamage + $cdDamage + $sdDamage,
		];
		return $return;
	}

	protected function takeDamageToMines(int $damage): int {
		$actualDamage = min($this->getMines(), IFloor($damage / MINE_ARMOUR));
		$this->takeMines($actualDamage);
		return $actualDamage * MINE_ARMOUR;
	}

	protected function takeDamageToCDs(int $damage): int {
		$actualDamage = min($this->getCDs(), IFloor($damage / CD_ARMOUR));
		$this->takeCDs($actualDamage);
		return $actualDamage * CD_ARMOUR;
	}

	protected function takeDamageToSDs(int $damage): int {
		$actualDamage = min($this->getSDs(), IFloor($damage / SD_ARMOUR));
		$this->takeSDs($actualDamage);
		return $actualDamage * SD_ARMOUR;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function killForcesByPlayer(AbstractSmrPlayer $killer): array {
		return [];
	}

}
