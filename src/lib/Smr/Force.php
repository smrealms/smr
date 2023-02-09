<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\Combat\Weapon\CombatDrones;
use Smr\Combat\Weapon\Mines;
use Smr\Combat\Weapon\ScoutDrones;
use Smr\Pages\Player\AttackForcesProcessor;
use Smr\Pages\Player\ForcesDrop;
use Smr\Pages\Player\ForcesDropProcessor;
use Smr\Pages\Player\ForcesRefreshAllProcessor;
use Smr\Pages\Player\ForcesRefreshProcessor;

class Force {

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

	protected readonly string $SQL;

	protected int $combatDrones = 0;
	protected int $scoutDrones = 0;
	protected int $mines = 0;
	protected int $expire = 0;

	protected bool $isNew = true;
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
			self::tidyUpForces(Galaxy::getGalaxyContaining($gameID, $sectorID));
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
			self::tidyUpForces(Galaxy::getGalaxyContaining($gameID, $sectorID));
			$p = new self($gameID, $sectorID, $ownerID, $dbRecord);
			self::$CACHE_FORCES[$gameID][$sectorID][$ownerID] = $p;
		}
		return self::$CACHE_FORCES[$gameID][$sectorID][$ownerID];
	}

	public static function tidyUpForces(Galaxy $galaxyToTidy): void {
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
		$db = Database::getInstance();
		$this->SQL = 'game_id = ' . $db->escapeNumber($gameID) . '
		              AND sector_id = ' . $db->escapeNumber($sectorID) . '
		              AND owner_id = ' . $db->escapeNumber($ownerID);

		if ($dbRecord === null) {
			$dbResult = $db->read('SELECT * FROM sector_has_forces WHERE ' . $this->SQL);
			if ($dbResult->hasRecord()) {
				$dbRecord = $dbResult->record();
			}
		}

		if ($dbRecord !== null) {
			$this->isNew = false;
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

	public function getBumpTurnCost(AbstractShip $ship): int {
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

	public function getAttackTurnCost(AbstractShip $ship): int {
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

	public function getSector(): Sector {
		return Sector::getSector($this->getGameID(), $this->getSectorID());
	}

	public function getSectorID(): int {
		return $this->sectorID;
	}

	public function ping(string $pingMessage, AbstractPlayer $playerPinging, bool $skipCheck = false): void {
		if (!$this->hasSDs() && !$skipCheck) {
			return;
		}
		$owner = $this->getOwner();
		if (!$playerPinging->sameAlliance($owner)) {
			$playerPinging->sendMessage($owner->getAccountID(), MSG_SCOUT, $pingMessage, false);
		}
	}

	public function getGalaxy(): Galaxy {
		return Galaxy::getGalaxyContaining($this->getGameID(), $this->getSectorID());
	}

	public function getOwner(): AbstractPlayer {
		return Player::getPlayer($this->getOwnerID(), $this->getGameID());
	}

	public function update(): void {
		$db = Database::getInstance();
		if (!$this->isNew) {
			if (!$this->exists()) {
				$db->write('DELETE FROM sector_has_forces WHERE ' . $this->SQL);
				$this->isNew = true;
			} elseif ($this->hasChanged) {
				$db->write('UPDATE sector_has_forces SET combat_drones = ' . $db->escapeNumber($this->combatDrones) . ', scout_drones = ' . $db->escapeNumber($this->scoutDrones) . ', mines = ' . $db->escapeNumber($this->mines) . ', expire_time = ' . $db->escapeNumber($this->expire) . ' WHERE ' . $this->SQL);
			}
		} elseif ($this->exists()) {
			$db->insert('sector_has_forces', [
				'game_id' => $db->escapeNumber($this->gameID),
				'sector_id' => $db->escapeNumber($this->sectorID),
				'owner_id' => $db->escapeNumber($this->ownerID),
				'combat_drones' => $db->escapeNumber($this->combatDrones),
				'scout_drones' => $db->escapeNumber($this->scoutDrones),
				'mines' => $db->escapeNumber($this->mines),
				'expire_time' => $db->escapeNumber($this->expire),
			]);
			$this->isNew = false;
		}
		// This instance is now in sync with the database
		$this->hasChanged = false;
	}

	/**
	 * Update the table fields associated with using Refresh All
	 */
	public function updateRefreshAll(AbstractPlayer $player, int $refreshTime): void {
		$db = Database::getInstance();
		$db->write('UPDATE sector_has_forces SET refresh_at=' . $db->escapeNumber($refreshTime) . ', refresher=' . $db->escapeNumber($player->getAccountID()) . ' WHERE ' . $this->SQL);
	}

	public function getExamineDropForcesHREF(): string {
		$container = new ForcesDrop($this->getOwnerID());
		return $container->href();
	}

	public function getAttackForcesHREF(): string {
		$container = new AttackForcesProcessor($this->getOwnerID());
		return $container->href();
	}

	public function getRefreshHREF(): string {
		$container = new ForcesRefreshProcessor($this->getOwnerID());
		return $container->href();
	}

	public function getDropSDHREF(): string {
		$container = new ForcesDropProcessor($this->getOwnerID(), dropSDs: 1);
		return $container->href();
	}

	public function getTakeSDHREF(): string {
		$container = new ForcesDropProcessor($this->getOwnerID(), takeSDs: 1);
		return $container->href();
	}

	public function getDropCDHREF(): string {
		$container = new ForcesDropProcessor($this->getOwnerID(), dropCDs: 1);
		return $container->href();
	}

	public function getTakeCDHREF(): string {
		$container = new ForcesDropProcessor($this->getOwnerID(), takeCDs: 1);
		return $container->href();
	}

	public function getDropMineHREF(): string {
		$container = new ForcesDropProcessor($this->getOwnerID(), dropMines: 1);
		return $container->href();
	}

	public function getTakeMineHREF(): string {
		$container = new ForcesDropProcessor($this->getOwnerID(), takeMines: 1);
		return $container->href();
	}

	public static function getRefreshAllHREF(): string {
		$container = new ForcesRefreshAllProcessor();
		return $container->href();
	}

	/**
	 * @param array<AbstractPlayer> $targetPlayers
	 * @return array{TotalDamage: int, DeadBeforeShot: bool, ForcesDestroyed?: bool, Mines?: array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}, Drones?: array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}, Scouts?: array{Weapon: \Smr\Combat\Weapon\AbstractWeapon, TargetPlayer: \Smr\AbstractPlayer, Hit: bool, WeaponDamage: WeaponDamageData, ActualDamage: TakenDamageData, KillResults?: array{DeadExp: int, LostCredits: int}}}
	 */
	public function shootPlayers(array $targetPlayers, bool $minesAreAttacker): array {
		$results = ['TotalDamage' => 0];
		if (!$this->exists()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;

		if ($this->hasMines()) {
			$thisMines = new Mines($this->getMines());
			$results['Results']['Mines'] = $thisMines->shootPlayerAsForce($this, array_rand_value($targetPlayers), $minesAreAttacker);
			$this->setMines($thisMines->getAmount()); // kamikaze
			$results['TotalDamage'] += $results['Results']['Mines']['ActualDamage']['TotalDamage'];
		}

		if ($this->hasCDs()) {
			$thisCDs = new CombatDrones($this->getCDs());
			$results['Results']['Drones'] = $thisCDs->shootPlayerAsForce($this, array_rand_value($targetPlayers));
			$results['TotalDamage'] += $results['Results']['Drones']['ActualDamage']['TotalDamage'];
		}

		if (!$minesAreAttacker) {
			if ($this->hasSDs()) {
				$thisSDs = new ScoutDrones($this->getSDs());
				$results['Results']['Scouts'] = $thisSDs->shootPlayerAsForce($this, array_rand_value($targetPlayers));
				$this->setSDs($thisSDs->getAmount()); // kamikaze
				$results['TotalDamage'] += $results['Results']['Scouts']['ActualDamage']['TotalDamage'];
			}
		}

		$results['ForcesDestroyed'] = !$this->exists();
		return $results;
	}

	/**
	 * @param WeaponDamageData $damage
	 * @return ForceTakenDamageData
	 */
	public function takeDamage(array $damage): array {
		$alreadyDead = !$this->exists();
		$numMines = 0;
		$numCDs = 0;
		$numSDs = 0;
		$minesDamage = 0;
		$cdDamage = 0;
		$sdDamage = 0;
		if (!$alreadyDead) {
			$numMines = $this->takeDamageToMines($damage['Armour']);
			$minesDamage = $numMines * MINE_ARMOUR;
			if (!$this->hasMines() && ($minesDamage == 0 || $damage['Rollover'])) {
				$cdMaxDamage = $damage['Armour'] - $minesDamage;
				$numCDs = $this->takeDamageToCDs($cdMaxDamage);
				$cdDamage = $numCDs * CD_ARMOUR;
				if (!$this->hasCDs() && ($cdDamage == 0 || $damage['Rollover'])) {
					$sdMaxDamage = $damage['Armour'] - $minesDamage - $cdDamage;
					$numSDs = $this->takeDamageToSDs($sdMaxDamage);
					$sdDamage = $numSDs * SD_ARMOUR;
				}
			}
		}
		return [
						'KillingShot' => !$alreadyDead && !$this->exists(),
						'TargetAlreadyDead' => $alreadyDead,
						'Mines' => $minesDamage,
						'NumMines' => $numMines,
						'HasMines' => $this->hasMines(),
						'CDs' => $cdDamage,
						'NumCDs' => $numCDs,
						'HasCDs' => $this->hasCDs(),
						'SDs' => $sdDamage,
						'NumSDs' => $numSDs,
						'HasSDs' => $this->hasSDs(),
						'TotalDamage' => $minesDamage + $cdDamage + $sdDamage,
		];
	}

	/**
	 * Returns the number of mines destroyed
	 */
	protected function takeDamageToMines(int $damage): int {
		$numMines = min($this->getMines(), IFloor($damage / MINE_ARMOUR));
		$this->takeMines($numMines);
		return $numMines;
	}

	/**
	 * Returns the number of CDs destroyed
	 */
	protected function takeDamageToCDs(int $damage): int {
		$numCDs = min($this->getCDs(), IFloor($damage / CD_ARMOUR));
		$this->takeCDs($numCDs);
		return $numCDs;
	}

	/**
	 * Returns the number of SDs destroyed
	 */
	protected function takeDamageToSDs(int $damage): int {
		$numSDs = min($this->getSDs(), IFloor($damage / SD_ARMOUR));
		$this->takeSDs($numSDs);
		return $numSDs;
	}

	/**
	 * @return array{}
	 */
	public function killForcesByPlayer(AbstractPlayer $killer): array {
		return [];
	}

}
