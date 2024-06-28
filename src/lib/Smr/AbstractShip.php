<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\Combat\Weapon\CombatDrones;
use Smr\Combat\Weapon\Weapon;

/**
 * Properties and methods for a ship instance.
 * Does not include the database layer (see Ship).
 */
class AbstractShip {

	// Player exp gained for each point of damage done
	protected const EXP_PER_DAMAGE_PLAYER = 0.375;
	protected const EXP_PER_DAMAGE_PLANET = 1.0; // note that planet damage is reduced
	protected const EXP_PER_DAMAGE_PORT = 0.15;
	protected const EXP_PER_DAMAGE_FORCE = 0.075;

	protected const STARTER_SHIPS = [
		RACE_NEUTRAL => SHIP_TYPE_GALACTIC_SEMI,
		RACE_ALSKANT => SHIP_TYPE_SMALL_TIMER,
		RACE_CREONTI => SHIP_TYPE_MEDIUM_CARGO_HULK,
		RACE_HUMAN => SHIP_TYPE_LIGHT_FREIGHTER,
		RACE_IKTHORNE => SHIP_TYPE_TINY_DELIGHT,
		RACE_SALVENE => SHIP_TYPE_HATCHLINGS_DUE,
		RACE_THEVIAN => SHIP_TYPE_SWIFT_VENTURE,
		RACE_WQHUMAN => SHIP_TYPE_SLIP_FREIGHTER,
		RACE_NIJARIN => SHIP_TYPE_REDEEMER,
	];

	protected AbstractPlayer $player;

	protected int $gameID;
	protected ShipType $shipType;

	/** @var array<int, Weapon> */
	protected array $weapons = [];
	/** @var array<int, int> */
	protected array $cargo = [];
	/** @var array<int, int> */
	protected array $hardware = [];
	protected bool $isCloaked = false;
	protected ShipIllusion|false $illusionShip = false;

	protected bool $hasChangedWeapons = false;
	protected bool $hasChangedCargo = false;
	/** @var array<int, bool> */
	protected array $hasChangedHardware = [];
	protected bool $hasChangedCloak = false;
	protected bool $hasChangedIllusion = false;

	public function __construct(AbstractPlayer $player) {
		$this->player = $player;
		$this->gameID = $player->getGameID();
		$this->regenerateShipType();
	}

	protected function regenerateShipType(): void {
		$this->shipType = ShipType::get($this->player->getShipTypeID());
	}

	public function checkForExcess(): void {
		$this->checkForExcessHardware();
		$this->checkForExcessWeapons();
		$this->checkForExcessCargo();
	}

	public function checkForExcessWeapons(): void {
		while ($this->hasWeapons() && ($this->getPowerUsed() > $this->getType()->getMaxPower() || $this->getNumWeapons() > $this->getHardpoints())) {
			//erase the first weapon 1 at a time until we are okay
			$this->removeLastWeapon();
		}
	}

	public function checkForExcessCargo(): void {
		if ($this->hasCargo()) {
			$excess = array_sum($this->getCargo()) - $this->getCargoHolds();
			foreach ($this->getCargo() as $goodID => $amount) {
				if ($excess > 0) {
					$decreaseAmount = min($amount, $excess);
					$this->decreaseCargo($goodID, $decreaseAmount);
					$excess -= $decreaseAmount;
				} else {
					// No more excess cargo
					break;
				}
			}
		}
	}

	public function checkForExcessHardware(): void {
		//check hardware to see if anything needs to be removed
		foreach ($this->getHardware() as $hardwareTypeID => $amount) {
			$max = $this->shipType->getMaxHardware($hardwareTypeID);
			$this->setHardware($hardwareTypeID, min($amount, $max));
		}
	}

	/**
	 * Set all hardware to its maximum value for this ship.
	 */
	public function setHardwareToMax(): void {
		foreach ($this->shipType->getAllMaxHardware() as $hardwareTypeID => $max) {
			$this->setHardware($hardwareTypeID, $max);
		}
	}

	public function getPowerUsed(): int {
		$power = 0;
		foreach ($this->weapons as $weapon) {
			$power += $weapon->getPowerLevel();
		}
		return $power;
	}

	public function getRemainingPower(): int {
		return $this->getType()->getMaxPower() - $this->getPowerUsed();
	}

	/**
	 * given power level of new weapon, return whether there is enough power available to install it on this ship
	 */
	public function checkPowerAvailable(int $powerLevel): bool {
		return $this->getRemainingPower() >= $powerLevel;
	}

	public function hasIllegalGoods(): bool {
		return $this->hasCargo(GOODS_SLAVES) || $this->hasCargo(GOODS_WEAPONS) || $this->hasCargo(GOODS_NARCOTICS);
	}

	public function getDisplayAttackRating(): int {
		if ($this->hasActiveIllusion()) {
			return $this->getIllusion()->attackRating;
		}
		return $this->getAttackRating();
	}

	public function getDisplayDefenseRating(): int {
		if ($this->hasActiveIllusion()) {
			return $this->getIllusion()->defenseRating;
		}
		return $this->getDefenseRating();
	}

	public function getDisplayName(): string {
		if ($this->hasActiveIllusion()) {
			return $this->getIllusion()->getName();
		}
		return $this->getName();
	}

	public function getAttackRating(): int {
		return IRound(($this->getTotalShieldDamage() + $this->getTotalArmourDamage() + $this->getCDs() * 2) / 40);
	}

	public function getAttackRatingWithMaxCDs(): int {
		return IRound(($this->getTotalShieldDamage() + $this->getTotalArmourDamage() + $this->getMaxCDs() * 2) / 40);
	}

	public function getDefenseRating(): int {
		return IRound(($this->getShields() + $this->getArmour() + $this->getCDs() * CD_ARMOUR) / 100);
	}

	public function getMaxDefenseRating(): int {
		return IRound(($this->getMaxShields() + $this->getMaxArmour() + $this->getMaxCDs() * CD_ARMOUR) / 100);
	}

	public function getShieldLow(): int { return IFloor($this->getShields() / 100) * 100; }
	public function getShieldHigh(): int { return $this->getShieldLow() + 100; }
	public function getArmourLow(): int { return IFloor($this->getArmour() / 100) * 100; }
	public function getArmourHigh(): int { return $this->getArmourLow() + 100; }
	public function getCDsLow(): int { return IFloor($this->getCDs() / 100) * 100; }
	public function getCDsHigh(): int { return $this->getCDsLow() + 100; }

	public function addWeapon(Weapon $weapon): Weapon|false {
		if ($this->hasOpenWeaponSlots() && $this->checkPowerAvailable($weapon->getPowerLevel())) {
			$this->weapons[] = $weapon;
			$this->hasChangedWeapons = true;
			return $weapon;
		}
		return false;
	}

	public function moveWeaponUp(int $orderID): void {
		if (count($this->weapons) === 0) {
			throw new Exception('This method cannot be used when there are no weapons');
		}
		$replacement = $orderID - 1;
		if ($replacement < 0) {
			// Shift everything up by one and put the selected weapon at the bottom
			$temp = array_shift($this->weapons);
			$this->weapons[] = $temp;
		} else {
			// Swap the selected weapon with the one above it
			$temp = $this->weapons[$replacement];
			$this->weapons[$replacement] = $this->weapons[$orderID];
			$this->weapons[$orderID] = $temp;
		}
		$this->hasChangedWeapons = true;
	}

	public function moveWeaponDown(int $orderID): void {
		$replacement = $orderID + 1;
		if ($replacement >= count($this->weapons)) {
			// Shift everything down by one and put the selected weapon at the top
			array_unshift($this->weapons, array_pop($this->weapons));
		} else {
			// Swap the selected weapon with the one below it
			$temp = $this->weapons[$replacement];
			$this->weapons[$replacement] = $this->weapons[$orderID];
			$this->weapons[$orderID] = $temp;
		}
		$this->hasChangedWeapons = true;
	}

	/**
	 * @param array<int, int> $orderArray
	 */
	public function setWeaponLocations(array $orderArray): void {
		$weapons = $this->weapons;
		foreach ($orderArray as $newOrder => $oldOrder) {
			$this->weapons[$newOrder] = $weapons[$oldOrder];
		}
		$this->hasChangedWeapons = true;
	}

	public function removeLastWeapon(): void {
		$this->removeWeapon($this->getNumWeapons() - 1);
	}

	public function removeWeapon(int $orderID): void {
		// Remove the specified weapon, then reindex the array
		unset($this->weapons[$orderID]);
		$this->weapons = array_values($this->weapons);
		$this->hasChangedWeapons = true;
	}

	public function removeAllWeapons(): void {
		$this->weapons = [];
		$this->hasChangedWeapons = true;
	}

	public function removeAllCargo(): void {
		foreach ($this->cargo as $goodID => $amount) {
			$this->setCargo($goodID, 0);
		}
	}

	public function removeAllHardware(): void {
		foreach (array_keys($this->hardware) as $hardwareTypeID) {
			$this->hasChangedHardware[$hardwareTypeID] = true;
		}
		$this->hardware = [];
		$this->decloak();
		$this->disableIllusion();
	}

	public function getPod(bool $isNewbie = false): void {
		$this->removeAllWeapons();
		$this->removeAllCargo();
		$this->removeAllHardware();

		if ($isNewbie) {
			$this->setShields(75);
			$this->setArmour(150);
			$this->setCargoHolds(40);
			$this->setTypeID(SHIP_TYPE_NEWBIE_MERCHANT_VESSEL);
		} else {
			$this->setShields(50);
			$this->setArmour(50);
			$this->setCargoHolds(5);
			$this->setTypeID(SHIP_TYPE_ESCAPE_POD);
		}
	}

	public function giveStarterShip(): void {
		if ($this->player->hasNewbieStatus()) {
			$shipID = SHIP_TYPE_NEWBIE_MERCHANT_VESSEL;
			$amount_shields = 75;
			$amount_armour = 150;
		} else {
			$shipID = self::STARTER_SHIPS[$this->player->getRaceID()];
			$amount_shields = 50;
			$amount_armour = 50;
		}
		$this->setTypeID($shipID);
		$this->setShields($amount_shields);
		$this->setArmour($amount_armour);
		$this->setCargoHolds(40);
		$this->addWeapon(Weapon::getWeapon(WEAPON_TYPE_LASER));
	}

	public function hasJump(): bool {
		return $this->getHardware(HARDWARE_JUMP) > 0;
	}

	public function hasDCS(): bool {
		return $this->getHardware(HARDWARE_DCS) > 0;
	}

	public function hasScanner(): bool {
		return $this->getHardware(HARDWARE_SCANNER) > 0;
	}

	public function hasCloak(): bool {
		return $this->getHardware(HARDWARE_CLOAK) > 0;
	}

	public function isCloaked(): bool {
		return $this->isCloaked;
	}

	public function decloak(): void {
		if ($this->isCloaked === false) {
			return;
		}
		$this->isCloaked = false;
		$this->hasChangedCloak = true;
	}

	public function enableCloak(): void {
		if ($this->hasCloak() === false) {
			throw new Exception('Ship does not have the supported hardware!');
		}
		if ($this->isCloaked === true) {
			return;
		}
		$this->isCloaked = true;
		$this->hasChangedCloak = true;
	}

	public function hasIllusion(): bool {
		return $this->getHardware(HARDWARE_ILLUSION) > 0;
	}

	/**
	 * @phpstan-assert-if-true !false $this->getIllusion()
	 */
	public function hasActiveIllusion(): bool {
		return $this->getIllusion() !== false;
	}

	/**
	 * @return \Smr\ShipIllusion|false
	 */
	public function getIllusion(): ShipIllusion|false {
		return $this->illusionShip;
	}

	public function setIllusion(int $shipTypeID, int $attack, int $defense): void {
		if ($this->hasIllusion() === false) {
			throw new Exception('Ship does not have the supported hardware!');
		}
		$newIllusion = new ShipIllusion(
			shipTypeID: $shipTypeID,
			attackRating: $attack,
			defenseRating: $defense,
		);
		if ($this->getIllusion() === $newIllusion) {
			return;
		}
		$this->illusionShip = $newIllusion;
		$this->hasChangedIllusion = true;
	}

	public function disableIllusion(): void {
		if ($this->getIllusion() === false) {
			return;
		}
		$this->illusionShip = false;
		$this->hasChangedIllusion = true;
	}

	public function getPlayer(): AbstractPlayer {
		return $this->player;
	}

	public function getAccountID(): int {
		return $this->getPlayer()->getAccountID();
	}

	public function getGameID(): int {
		return $this->gameID;
	}

	public function getGame(): Game {
		return Game::getGame($this->gameID);
	}

	/**
	 * Switch to a new ship, updating player turns accordingly.
	 */
	public function setTypeID(int $shipTypeID): void {
		$oldSpeed = $this->shipType->getSpeed();
		$this->getPlayer()->setShipTypeID($shipTypeID);
		$this->regenerateShipType();
		$this->checkForExcess();

		// Update the player's turns to account for the speed change
		$newSpeed = $this->shipType->getSpeed();
		$oldTurns = $this->getPlayer()->getTurns();
		$this->getPlayer()->setTurns(IRound($oldTurns * $newSpeed / $oldSpeed));
	}

	public function getType(): ShipType {
		return $this->shipType;
	}

	public function getTypeID(): int {
		return $this->shipType->getTypeID();
	}

	public function getClass(): ShipClass {
		return $this->shipType->getClass();
	}

	public function getName(): string {
		return $this->shipType->getName();
	}

	public function getCost(): int {
		return $this->shipType->getCost();
	}

	public function getHardpoints(): int {
		return $this->shipType->getHardpoints();
	}

	/**
	 * Trade-in value of the ship
	 */
	public function getRefundValue(): int {
		return IFloor($this->getCost() * SHIP_REFUND_PERCENT);
	}

	public function getCostToUpgrade(int $otherShipTypeID): int {
		$otherShipType = ShipType::get($otherShipTypeID);
		return $otherShipType->getCost() - $this->getRefundValue();
	}

	public function getCostToUpgradeAndUNO(int $otherShipTypeID): int {
		return $this->getCostToUpgrade($otherShipTypeID) + $this->getCostToUNOAgainstShip($otherShipTypeID);
	}

	protected function getCostToUNOAgainstShip(int $otherShipTypeID): int {
		$otherShipType = ShipType::get($otherShipTypeID);
		$cost = 0;
		$hardwareTypes = [HARDWARE_SHIELDS, HARDWARE_ARMOUR, HARDWARE_CARGO];
		foreach ($hardwareTypes as $hardwareTypeID) {
			$cost += max(0, $otherShipType->getMaxHardware($hardwareTypeID) - $this->getHardware($hardwareTypeID)) * HardwareType::get($hardwareTypeID)->cost;
		}
		return $cost;
	}

	public function getCostToUNO(): int {
		return $this->getCostToUNOAgainstShip($this->getTypeID());
	}

	/**
	 * Returns the ship speed modified by the game speed.
	 */
	public function getRealSpeed(): float {
		return $this->shipType->getSpeed() * $this->getGame()->getGameSpeed();
	}

	/**
	 * @return ($hardwareTypeID is null ? array<int, int> : int)
	 */
	public function getHardware(int $hardwareTypeID = null): array|int {
		if ($hardwareTypeID === null) {
			return $this->hardware;
		}
		return $this->hardware[$hardwareTypeID] ?? 0;
	}

	public function setHardware(int $hardwareTypeID, int $amount): void {
		if ($this->getHardware($hardwareTypeID) === $amount) {
			return;
		}
		$this->hardware[$hardwareTypeID] = $amount;
		$this->hasChangedHardware[$hardwareTypeID] = true;
	}

	public function increaseHardware(int $hardwareTypeID, int $amount): void {
		$this->setHardware($hardwareTypeID, $this->getHardware($hardwareTypeID) + $amount);
	}

	public function hasMaxHardware(int $hardwareTypeID): bool {
		return $this->getHardware($hardwareTypeID) === $this->shipType->getMaxHardware($hardwareTypeID);
	}

	public function getShields(): int {
		return $this->getHardware(HARDWARE_SHIELDS);
	}

	public function setShields(int $amount): void {
		$this->setHardware(HARDWARE_SHIELDS, $amount);
	}

	public function decreaseShields(int $amount): void {
		$this->setShields($this->getShields() - $amount);
	}

	public function increaseShields(int $amount): void {
		$this->setShields($this->getShields() + $amount);
	}

	public function hasShields(): bool {
		return $this->getShields() > 0;
	}

	public function hasMaxShields(): bool {
		return $this->hasMaxHardware(HARDWARE_SHIELDS);
	}

	public function getMaxShields(): int {
		return $this->shipType->getMaxHardware(HARDWARE_SHIELDS);
	}

	public function getArmour(): int {
		return $this->getHardware(HARDWARE_ARMOUR);
	}

	public function setArmour(int $amount): void {
		$this->setHardware(HARDWARE_ARMOUR, $amount);
	}

	public function decreaseArmour(int $amount): void {
		$this->setArmour($this->getArmour() - $amount);
	}

	public function increaseArmour(int $amount): void {
		$this->setArmour($this->getArmour() + $amount);
	}

	public function hasArmour(): bool {
		return $this->getArmour() > 0;
	}

	public function hasMaxArmour(): bool {
		return $this->hasMaxHardware(HARDWARE_ARMOUR);
	}

	public function getMaxArmour(): int {
		return $this->shipType->getMaxHardware(HARDWARE_ARMOUR);
	}

	public function isDead(): bool {
		return !$this->hasArmour() && !$this->hasShields();
	}

	public function hasMaxCDs(): bool {
		return $this->hasMaxHardware(HARDWARE_COMBAT);
	}

	public function hasMaxSDs(): bool {
		return $this->hasMaxHardware(HARDWARE_SCOUT);
	}

	public function hasMaxMines(): bool {
		return $this->hasMaxHardware(HARDWARE_MINE);
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
		return $this->getHardware(HARDWARE_COMBAT);
	}

	public function setCDs(int $amount): void {
		$this->setHardware(HARDWARE_COMBAT, $amount);
	}

	public function decreaseCDs(int $amount): void {
		$this->setCDs($this->getCDs() - $amount);
	}

	public function increaseCDs(int $amount): void {
		$this->setCDs($this->getCDs() + $amount);
	}

	public function getMaxCDs(): int {
		return $this->shipType->getMaxHardware(HARDWARE_COMBAT);
	}

	public function getSDs(): int {
		return $this->getHardware(HARDWARE_SCOUT);
	}

	public function setSDs(int $amount): void {
		$this->setHardware(HARDWARE_SCOUT, $amount);
	}

	public function decreaseSDs(int $amount): void {
		$this->setSDs($this->getSDs() - $amount);
	}

	public function increaseSDs(int $amount): void {
		$this->setSDs($this->getSDs() + $amount);
	}

	public function getMaxSDs(): int {
		return $this->shipType->getMaxHardware(HARDWARE_SCOUT);
	}

	public function getMines(): int {
		return $this->getHardware(HARDWARE_MINE);
	}

	public function setMines(int $amount): void {
		$this->setHardware(HARDWARE_MINE, $amount);
	}

	public function decreaseMines(int $amount): void {
		$this->setMines($this->getMines() - $amount);
	}

	public function increaseMines(int $amount): void {
		$this->setMines($this->getMines() + $amount);
	}

	public function getMaxMines(): int {
		return $this->shipType->getMaxHardware(HARDWARE_MINE);
	}

	public function getCargoHolds(): int {
		return $this->getHardware(HARDWARE_CARGO);
	}

	public function setCargoHolds(int $amount): void {
		$this->setHardware(HARDWARE_CARGO, $amount);
	}

	/**
	 * @return ($goodID is null ? array<int, int> : int)
	 */
	public function getCargo(int $goodID = null): int|array {
		if ($goodID === null) {
			return $this->cargo;
		}
		return $this->cargo[$goodID] ?? 0;
	}

	public function hasCargo(int $goodID = null): bool {
		if ($goodID === null) {
			return $this->getUsedHolds() > 0;
		}
		return $this->getCargo($goodID) > 0;
	}

	public function setCargo(int $goodID, int $amount): void {
		if ($this->getCargo($goodID) === $amount) {
			return;
		}
		$this->cargo[$goodID] = $amount;
		$this->hasChangedCargo = true;
		// Sort cargo by goodID to make sure it shows up in the correct order
		// before the next page is loaded.
		ksort($this->cargo);
	}

	public function decreaseCargo(int $goodID, int $amount): void {
		if ($amount < 0) {
			throw new Exception('Trying to decrease negative cargo.');
		}
		$this->setCargo($goodID, $this->getCargo($goodID) - $amount);
	}

	public function increaseCargo(int $goodID, int $amount): void {
		if ($amount < 0) {
			throw new Exception('Trying to increase negative cargo.');
		}
		$this->setCargo($goodID, $this->getCargo($goodID) + $amount);
	}

	public function getEmptyHolds(): int {
		return $this->getCargoHolds() - $this->getUsedHolds();
	}

	public function getUsedHolds(): int {
		return array_sum($this->getCargo());
	}

	public function hasMaxCargoHolds(): bool {
		return $this->hasMaxHardware(HARDWARE_CARGO);
	}

	public function getMaxCargoHolds(): int {
		return $this->shipType->getMaxHardware(HARDWARE_CARGO);
	}

	public function hasWeapons(): bool {
		return $this->getNumWeapons() > 0;
	}

	/**
	 * @return array<int, Weapon>
	 */
	public function getWeapons(): array {
		return $this->weapons;
	}

	public function canAttack(): bool {
		return $this->hasWeapons() || $this->hasCDs();
	}

	public function getNumWeapons(): int {
		return count($this->getWeapons());
	}

	public function getOpenWeaponSlots(): int {
		return $this->getHardpoints() - $this->getNumWeapons();
	}

	public function hasOpenWeaponSlots(): bool {
		return $this->getOpenWeaponSlots() > 0;
	}

	public function getTotalShieldDamage(): int {
		$shieldDamage = 0;
		foreach ($this->getWeapons() as $weapon) {
			$shieldDamage += $weapon->getShieldDamage();
		}
		return $shieldDamage;
	}

	public function getTotalArmourDamage(): int {
		$armourDamage = 0;
		foreach ($this->getWeapons() as $weapon) {
			$armourDamage += $weapon->getArmourDamage();
		}
		return $armourDamage;
	}

	public function isFederal(): bool {
		return $this->getTypeID() === SHIP_TYPE_FEDERAL_DISCOVERY ||
		       $this->getTypeID() === SHIP_TYPE_FEDERAL_WARRANT ||
		       $this->getTypeID() === SHIP_TYPE_FEDERAL_ULTIMATUM;
	}

	public function isUnderground(): bool {
		return $this->getTypeID() === SHIP_TYPE_THIEF ||
		       $this->getTypeID() === SHIP_TYPE_ASSASSIN ||
		       $this->getTypeID() === SHIP_TYPE_DEATH_CRUISER;
	}

	/**
	 * @param array<AbstractPlayer> $targetPlayers
	 * @return TraderCombatResult
	 */
	public function shootPlayers(array $targetPlayers): array {
		$thisPlayer = $this->getPlayer();
		$results = [
			'Player' => $thisPlayer,
			'TotalDamage' => 0,
			'TotalDamagePerTargetPlayer' => [],
			'Weapons' => [],
		];
		foreach ($targetPlayers as $targetPlayer) {
			$results['TotalDamagePerTargetPlayer'][$targetPlayer->getAccountID()] = 0;
		}
		if ($thisPlayer->isDead()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		foreach ($this->weapons as $orderID => $weapon) {
			$results['Weapons'][$orderID] = $weapon->shootPlayer($thisPlayer, array_rand_value($targetPlayers));
			if ($results['Weapons'][$orderID]['Hit']) {
				if (!isset($results['Weapons'][$orderID]['ActualDamage'])) {
					throw new Exception('Weapon hit without providing ActualDamage!');
				}
				$totalDamage = $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
				$results['TotalDamage'] += $totalDamage;
				$results['TotalDamagePerTargetPlayer'][$results['Weapons'][$orderID]['Target']->getAccountID()] += $totalDamage;
			}
		}
		if ($this->hasCDs()) {
			$thisCDs = new CombatDrones($this->getCDs());
			$results['Drones'] = $thisCDs->shootPlayer($thisPlayer, array_rand_value($targetPlayers));
			$totalDamage = $results['Drones']['ActualDamage']['TotalDamage'];
			$results['TotalDamage'] += $totalDamage;
			$results['TotalDamagePerTargetPlayer'][$results['Drones']['Target']->getAccountID()] += $totalDamage;
		}
		$thisPlayer->increaseExperience(IRound($results['TotalDamage'] * self::EXP_PER_DAMAGE_PLAYER));
		$thisPlayer->increaseHOF($results['TotalDamage'], ['Combat', 'Player', 'Damage Done'], HOF_PUBLIC);
		$thisPlayer->increaseHOF(1, ['Combat', 'Player', 'Shots'], HOF_PUBLIC);
		return $results;
	}

	/**
	 * @return ForceAttackerCombatResult
	 */
	public function shootForces(Force $forces): array {
		$thisPlayer = $this->getPlayer();
		$results = ['Player' => $thisPlayer, 'TotalDamage' => 0, 'Weapons' => []];
		if ($thisPlayer->isDead()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		foreach ($this->weapons as $orderID => $weapon) {
			$results['Weapons'][$orderID] = $weapon->shootForces($thisPlayer, $forces);
			if ($results['Weapons'][$orderID]['Hit']) {
				if (!isset($results['Weapons'][$orderID]['ActualDamage'])) {
					throw new Exception('Weapon hit without providing ActualDamage!');
				}
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['NumMines'], ['Combat', 'Forces', 'Mines', 'Killed'], HOF_PUBLIC);
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['Mines'], ['Combat', 'Forces', 'Mines', 'Damage Done'], HOF_PUBLIC);
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['NumCDs'], ['Combat', 'Forces', 'Combat Drones', 'Killed'], HOF_PUBLIC);
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['CDs'], ['Combat', 'Forces', 'Combat Drones', 'Damage Done'], HOF_PUBLIC);
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['NumSDs'], ['Combat', 'Forces', 'Scout Drones', 'Killed'], HOF_PUBLIC);
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['SDs'], ['Combat', 'Forces', 'Scout Drones', 'Damage Done'], HOF_PUBLIC);
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['NumMines'] + $results['Weapons'][$orderID]['ActualDamage']['NumCDs'] + $results['Weapons'][$orderID]['ActualDamage']['NumSDs'], ['Combat', 'Forces', 'Killed'], HOF_PUBLIC);
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
			}
		}
		if ($this->hasCDs()) {
			$thisCDs = new CombatDrones($this->getCDs());
			$results['Drones'] = $thisCDs->shootForces($thisPlayer, $forces);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['NumMines'], ['Combat', 'Forces', 'Mines', 'Killed'], HOF_PUBLIC);
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['Mines'], ['Combat', 'Forces', 'Mines', 'Damage Done'], HOF_PUBLIC);
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['NumCDs'], ['Combat', 'Forces', 'Combat Drones', 'Killed'], HOF_PUBLIC);
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['CDs'], ['Combat', 'Forces', 'Combat Drones', 'Damage Done'], HOF_PUBLIC);
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['NumSDs'], ['Combat', 'Forces', 'Scout Drones', 'Killed'], HOF_PUBLIC);
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['SDs'], ['Combat', 'Forces', 'Scout Drones', 'Damage Done'], HOF_PUBLIC);
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['NumMines'] + $results['Drones']['ActualDamage']['NumCDs'] + $results['Drones']['ActualDamage']['NumSDs'], ['Combat', 'Forces', 'Killed'], HOF_PUBLIC);
		}
		$thisPlayer->increaseExperience(IRound($results['TotalDamage'] * self::EXP_PER_DAMAGE_FORCE));
		$thisPlayer->increaseHOF($results['TotalDamage'], ['Combat', 'Forces', 'Damage Done'], HOF_PUBLIC);
		$thisPlayer->increaseHOF(1, ['Combat', 'Forces', 'Shots'], HOF_PUBLIC);
		return $results;
	}

	/**
	 * @return PortAttackerCombatResult
	 */
	public function shootPort(Port $port): array {
		$thisPlayer = $this->getPlayer();
		$results = ['Player' => $thisPlayer, 'TotalDamage' => 0, 'Weapons' => []];
		if ($thisPlayer->isDead()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		foreach ($this->weapons as $orderID => $weapon) {
			$results['Weapons'][$orderID] = $weapon->shootPort($thisPlayer, $port);
			if ($results['Weapons'][$orderID]['Hit']) {
				if (!isset($results['Weapons'][$orderID]['ActualDamage'])) {
					throw new Exception('Weapon hit without providing ActualDamage!');
				}
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
			}
		}
		if ($this->hasCDs()) {
			$thisCDs = new CombatDrones($this->getCDs());
			$results['Drones'] = $thisCDs->shootPort($thisPlayer, $port);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
		}
		$thisPlayer->increaseExperience(IRound($results['TotalDamage'] * self::EXP_PER_DAMAGE_PORT));
		$thisPlayer->increaseHOF($results['TotalDamage'], ['Combat', 'Port', 'Damage Done'], HOF_PUBLIC);
		//$thisPlayer->increaseHOF(1,array('Combat','Port','Shots')); //in Port::attackedBy()

		// Change alignment if we reach a damage threshold.
		// Increase if player and port races are at war; decrease otherwise.
		if ($results['TotalDamage'] >= Port::DAMAGE_NEEDED_FOR_ALIGNMENT_CHANGE) {
			$relations = Globals::getRaceRelations($thisPlayer->getGameID(), $thisPlayer->getRaceID());
			if ($relations[$port->getRaceID()] <= RELATIONS_WAR) {
				$thisPlayer->increaseAlignment(ALIGNMENT_GAIN_PORT_DAMAGE);
				$thisPlayer->increaseHOF(ALIGNMENT_GAIN_PORT_DAMAGE, ['Combat', 'Port', 'Alignment', 'Gain'], HOF_PUBLIC);
			} else {
				$thisPlayer->decreaseAlignment(ALIGNMENT_LOSS_PORT_DAMAGE);
				$thisPlayer->increaseHOF(ALIGNMENT_LOSS_PORT_DAMAGE, ['Combat', 'Port', 'Alignment', 'Loss'], HOF_PUBLIC);
			}
		}
		return $results;
	}

	/**
	 * @return PlanetAttackerCombatResult
	 */
	public function shootPlanet(Planet $planet): array {
		$thisPlayer = $this->getPlayer();
		$results = ['Player' => $thisPlayer, 'TotalDamage' => 0, 'Weapons' => []];
		if ($thisPlayer->isDead()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		foreach ($this->weapons as $orderID => $weapon) {
			$results['Weapons'][$orderID] = $weapon->shootPlanet($thisPlayer, $planet);
			if ($results['Weapons'][$orderID]['Hit']) {
				if (!isset($results['Weapons'][$orderID]['ActualDamage'])) {
					throw new Exception('Weapon hit without providing ActualDamage!');
				}
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
			}
		}
		if ($this->hasCDs()) {
			$thisCDs = new CombatDrones($this->getCDs());
			$results['Drones'] = $thisCDs->shootPlanet($thisPlayer, $planet);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
		}
		$thisPlayer->increaseExperience(IRound($results['TotalDamage'] * self::EXP_PER_DAMAGE_PLANET));
		$thisPlayer->increaseHOF($results['TotalDamage'], ['Combat', 'Planet', 'Damage Done'], HOF_PUBLIC);
		//$thisPlayer->increaseHOF(1,array('Combat','Planet','Shots')); //in Planet::attackedBy()
		return $results;
	}

	/**
	 * @param WeaponDamageData $damage
	 * @return TakenDamageData
	 */
	public function takeDamage(array $damage): array {
		$alreadyDead = $this->getPlayer()->isDead();
		$armourDamage = 0;
		$cdDamage = 0;
		$shieldDamage = 0;
		if (!$alreadyDead) {
			// Even if the weapon doesn't do any damage, it was fired at the
			// player, so alert them that they're under attack.
			$this->getPlayer()->setUnderAttack(true);

			$shieldDamage = $this->takeDamageToShields($damage['Shield']);
			if (!$this->hasShields() && ($shieldDamage === 0 || $damage['Rollover'])) {
				$cdMaxDamage = $damage['Armour'] - $shieldDamage;
				$cdDamage = $this->takeDamageToCDs($cdMaxDamage);
				if (!$this->hasCDs() && ($cdDamage === 0 || $damage['Rollover'])) {
					$armourMaxDamage = $damage['Armour'] - $shieldDamage - $cdDamage;
					$armourDamage = $this->takeDamageToArmour($armourMaxDamage);
				}
			}
		}
		return [
			'KillingShot' => !$alreadyDead && $this->isDead(),
			'TargetAlreadyDead' => $alreadyDead,
			'Shield' => $shieldDamage,
			'CDs' => $cdDamage,
			'NumCDs' => $cdDamage / CD_ARMOUR,
			'HasCDs' => $this->hasCDs(),
			'Armour' => $armourDamage,
			'TotalDamage' => $shieldDamage + $cdDamage + $armourDamage,
		];
	}

	/**
	 * @param WeaponDamageData $damage
	 * @return TakenDamageData
	 */
	public function takeDamageFromMines(array $damage): array {
		$alreadyDead = $this->getPlayer()->isDead();
		$armourDamage = 0;
		$cdDamage = 0;
		$shieldDamage = 0;
		if (!$alreadyDead) {
			$shieldDamage = $this->takeDamageToShields($damage['Shield']);
			if (!$this->hasShields() && ($shieldDamage === 0 || $damage['Rollover'])) { //skip CDs if it's mines
				$armourMaxDamage = $damage['Armour'] - $shieldDamage;
				$armourDamage = $this->takeDamageToArmour($armourMaxDamage);
			}
		}
		return [
			'KillingShot' => !$alreadyDead && $this->isDead(),
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
		$actualDamage = min($this->getArmour(), $damage);
		$this->decreaseArmour($actualDamage);
		return $actualDamage;
	}

	/**
	 * Returns the maneuverability rating for this ship.
	 */
	public function getMR(): int {
		return $this->shipType->getBaseManeuverability();
	}

	public function update(): void {
		throw new Exception('Can only call update on Ship objects');
	}

}
