<?php declare(strict_types=1);

abstract class AbstractSmrShip {
	protected static array $CACHE_BASE_SHIPS = [];

	const SHIP_CLASS_HUNTER = 1;
	const SHIP_CLASS_RAIDER = 3;
	const SHIP_CLASS_SCOUT = 4;

	// Player exp gained for each point of damage done
	const EXP_PER_DAMAGE_PLAYER = 0.375;
	const EXP_PER_DAMAGE_PLANET = 1.0; // note that planet damage is reduced
	const EXP_PER_DAMAGE_PORT   = 0.15;
	const EXP_PER_DAMAGE_FORCE  = 0.075;

	const STARTER_SHIPS = [
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

	protected AbstractSmrPlayer $player;

	protected int $gameID;
	protected array $baseShip;

	protected array $hardware;
	protected array $oldHardware;

	protected array $cargo;

	protected array $weapons;

	protected array|false $illusionShip;

	protected bool $hasChangedWeapons = false;
	protected bool $hasChangedCargo = false;
	protected array $hasChangedHardware = [];

	public static function getBaseShip(int $shipTypeID, bool $forceUpdate = false) : array {
		if ($forceUpdate || !isset(self::$CACHE_BASE_SHIPS[$shipTypeID])) {
			// determine ship
			$db = MySqlDatabase::getInstance();
			$db->query('SELECT * FROM ship_type WHERE ship_type_id = ' . $db->escapeNumber($shipTypeID) . ' LIMIT 1');
			if ($db->nextRecord()) {
				self::$CACHE_BASE_SHIPS[$shipTypeID] = self::buildBaseShip($db);
			} else {
				self::$CACHE_BASE_SHIPS[$shipTypeID] = false;
			}
		}
		return self::$CACHE_BASE_SHIPS[$shipTypeID];
	}

	protected static function buildBaseShip(MySqlDatabase $db) : array {
		$ship = array();
		$ship['Type'] = 'Ship';
		$ship['Name'] = $db->getField('ship_name');
		$ship['ShipTypeID'] = $db->getInt('ship_type_id');
		$ship['ShipClassID'] = $db->getInt('ship_class_id');
		$ship['RaceID'] = $db->getInt('race_id');
		$ship['Hardpoint'] = $db->getInt('hardpoint');
		$ship['Speed'] = $db->getInt('speed');
		$ship['Cost'] = $db->getInt('cost');
		$ship['AlignRestriction'] = $db->getInt('buyer_restriction');
		$ship['Level'] = $db->getInt('lvl_needed');

		$maxPower = 0;
		switch ($ship['Hardpoint']) {
			default:
				$maxPower += 1 * $ship['Hardpoint'] - 10;
			case 10:
				$maxPower += 2;
			case 9:
				$maxPower += 2;
			case 8:
				$maxPower += 2;
			case 7:
				$maxPower += 2;
			case 6:
				$maxPower += 3;
			case 5:
				$maxPower += 3;
			case 4:
				$maxPower += 3;
			case 3:
				$maxPower += 4;
			case 2:
				$maxPower += 4;
			case 1:
				$maxPower += 5;
			case 0:
				$maxPower += 0;
		}
		$ship['MaxPower'] = $maxPower;


		// get supported hardware from db
		$db2 = MySqlDatabase::getInstance();
		$db2->query('SELECT hardware_type_id, max_amount FROM ship_type_support_hardware ' .
			'WHERE ship_type_id = ' . $db2->escapeNumber($ship['ShipTypeID']) . ' ORDER BY hardware_type_id');

		while ($db2->nextRecord()) {
			// adding hardware to array
			$ship['MaxHardware'][$db2->getInt('hardware_type_id')] = $db2->getInt('max_amount');
		}

		$ship['BaseMR'] = IRound(
								700 -
								(
									(
										$ship['MaxHardware'][HARDWARE_SHIELDS]
										+$ship['MaxHardware'][HARDWARE_ARMOUR]
										+$ship['MaxHardware'][HARDWARE_COMBAT] * 3
									) / 25
									+(
										$ship['MaxHardware'][HARDWARE_CARGO] / 100
										-$ship['Speed'] * 5
										+$ship['Hardpoint'] * 5
										+$ship['MaxHardware'][HARDWARE_COMBAT] / 5
									)
								)
							);
		return $ship;
	}

	public static function getAllBaseShips() : array {
		// determine ship
		$db = MySqlDatabase::getInstance();
		$db->query('SELECT * FROM ship_type ORDER BY ship_type_id ASC');
		while ($db->nextRecord()) {
			if (!isset(self::$CACHE_BASE_SHIPS[$db->getInt('ship_type_id')])) {
				self::$CACHE_BASE_SHIPS[$db->getInt('ship_type_id')] = self::buildBaseShip($db);
			}
		}
		return self::$CACHE_BASE_SHIPS;
	}

	protected function __construct(AbstractSmrPlayer $player) {
		$this->player = $player;
		$this->gameID = $player->getGameID();
		$this->regenerateBaseShip();
	}

	protected function regenerateBaseShip() : void {
		$this->baseShip = AbstractSmrShip::getBaseShip($this->player->getShipTypeID());
	}

	public function checkForExcess() : void {
		$this->checkForExcessHardware();
		$this->checkForExcessWeapons();
		$this->checkForExcessCargo();
	}

	public function checkForExcessWeapons() : void {
		while ($this->hasWeapons() && ($this->getPowerUsed() > $this->getMaxPower() || $this->getNumWeapons() > $this->getHardpoints())) {
			//erase the first weapon 1 at a time until we are okay
			$this->removeLastWeapon();
		}
	}

	public function checkForExcessCargo() : void {
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

	public function checkForExcessHardware() : void {
		//check hardware to see if anything needs to be removed
		if (is_array($hardware = $this->getHardware())) {
			foreach ($hardware as $hardwareTypeID => $amount) {
				if ($amount > ($max = $this->getMaxHardware($hardwareTypeID))) {
					$this->setHardware($hardwareTypeID, $max);
				}
			}
		}
	}

	/**
	 * Set all hardware to its maximum value for this ship.
	 */
	public function setHardwareToMax() : void {
		foreach ($this->getMaxHardware() as $key => $max) {
			$this->setHardware($key, $max);
		}
		$this->removeUnderAttack();
	}

	public function getPowerUsed() : int {
		$power = 0;
		foreach ($this->weapons as $weapon) {
			$power += $weapon->getPowerLevel();
		}
		return $power;
	}

	public function getRemainingPower() : int {
		return $this->getMaxPower() - $this->getPowerUsed();
	}

	/**
	 * given power level of new weapon, return whether there is enough power available to install it on this ship
	 */
	public function checkPowerAvailable(int $powerLevel) : bool {
		return $this->getRemainingPower() >= $powerLevel;
	}

	public function getMaxPower() : int {
		return $this->baseShip['MaxPower'];
	}

	public function hasIllegalGoods() : bool {
		return $this->hasCargo(GOODS_SLAVES) || $this->hasCargo(GOODS_WEAPONS) || $this->hasCargo(GOODS_NARCOTICS);
	}

	public function getDisplayAttackRating() : int {
		if ($this->hasActiveIllusion()) {
			return $this->getIllusionAttack();
		} else {
			return $this->getAttackRating();
		}
	}

	public function getDisplayDefenseRating() : int {
		if ($this->hasActiveIllusion()) {
			return $this->getIllusionDefense();
		} else {
			return $this->getDefenseRating();
		}
	}

	public function getDisplayName() : string {
		if ($this->hasActiveIllusion()) {
			return $this->getIllusionShipName();
		} else {
			return $this->getName();
		}
	}

	public function getAttackRating() : int {
		return IRound(($this->getTotalShieldDamage() + $this->getTotalArmourDamage() + $this->getCDs() * 2) / 40);
	}

	public function getAttackRatingWithMaxCDs() : int {
		return IRound(($this->getTotalShieldDamage() + $this->getTotalArmourDamage() + $this->getMaxCDs() * .7) / 40);
	}

	public function getDefenseRating() : int {
		return IRound((($this->getShields() + $this->getArmour()) / 100) + (($this->getCDs() * 3) / 100));
	}

	public function getMaxDefenseRating() : int {
		return IRound((($this->getMaxShields() + $this->getMaxArmour()) / 100) + (($this->getMaxCDs() * 3) / 100));
	}

	public function getShieldLow() : int { return IFloor($this->getShields() / 100) * 100; }
	public function getShieldHigh() : int { return $this->getShieldLow() + 100; }
	public function getArmourLow() : int { return IFloor($this->getArmour() / 100) * 100; }
	public function getArmourHigh() : int { return $this->getArmourLow() + 100; }
	public function getCDsLow() : int { return IFloor($this->getCDs() / 100) * 100; }
	public function getCDsHigh() : int { return $this->getCDsLow() + 100; }



	public function addWeapon(SmrWeapon $weapon) : SmrWeapon|false {
		if ($this->hasOpenWeaponSlots() && $this->checkPowerAvailable($weapon->getPowerLevel())) {
			array_push($this->weapons, $weapon);
			$this->hasChangedWeapons = true;
			return $weapon;
		}
		return false;
	}

	public function moveWeaponUp(int $orderID) : void {
		$replacement = $orderID - 1;
		if ($replacement < 0) {
			// Shift everything up by one and put the selected weapon at the bottom
			array_push($this->weapons, array_shift($this->weapons));
		} else {
			// Swap the selected weapon with the one above it
			$temp = $this->weapons[$replacement];
			$this->weapons[$replacement] = $this->weapons[$orderID];
			$this->weapons[$orderID] = $temp;
		}
		$this->hasChangedWeapons = true;
	}

	public function moveWeaponDown(int $orderID) : void {
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

	public function setWeaponLocations(array $orderArray) : void {
		$weapons = $this->weapons;
		foreach ($orderArray as $newOrder => $oldOrder) {
			$this->weapons[$newOrder] = $weapons[$oldOrder];
		}
		$this->hasChangedWeapons = true;
	}

	public function removeLastWeapon() : void {
		$this->removeWeapon($this->getNumWeapons() - 1);
	}

	public function removeWeapon(int $orderID) : void {
		// Remove the specified weapon, then reindex the array
		unset($this->weapons[$orderID]);
		$this->weapons = array_values($this->weapons);
		$this->hasChangedWeapons = true;
	}

	public function removeAllWeapons() : void {
		$this->weapons = array();
		$this->hasChangedWeapons = true;
	}

	public function removeAllCargo() : void {
		foreach ($this->cargo as $goodID => $amount) {
			$this->setCargo($goodID, 0);
		}
	}

	public function removeAllHardware() : void {
		foreach (array_keys($this->hardware) as $hardwareTypeID) {
			$this->setHardware($hardwareTypeID, 0);
		}
		$this->decloak();
		$this->disableIllusion();
	}

	public function getPod(bool $isNewbie = false) : void {
		$this->removeAllWeapons();
		$this->removeAllCargo();
		$this->removeAllHardware();

		if ($isNewbie) {
			$this->setShields(75, true);
			$this->setArmour(150, true);
			$this->setCargoHolds(40);
			$this->setShipTypeID(SHIP_TYPE_NEWBIE_MERCHANT_VESSEL);
		} else {
			$this->setShields(50, true);
			$this->setArmour(50, true);
			$this->setCargoHolds(5);
			$this->setShipTypeID(SHIP_TYPE_ESCAPE_POD);
		}

		$this->removeUnderAttack();
	}

	public function giveStarterShip() : void {
		if ($this->player->hasNewbieStatus()) {
			$shipID = SHIP_TYPE_NEWBIE_MERCHANT_VESSEL;
			$amount_shields = 75;
			$amount_armour = 150;
		} else {
			$shipID = self::STARTER_SHIPS[$this->player->getRaceID()];
			$amount_shields = 50;
			$amount_armour = 50;
		}
		$this->setShipTypeID($shipID);
		$this->setShields($amount_shields, true);
		$this->setArmour($amount_armour, true);
		$this->setCargoHolds(40);
		$this->addWeapon(SmrWeapon::getWeapon(WEAPON_TYPE_LASER));
	}

	public function hasCloak() : bool {
		return $this->getHardware(HARDWARE_CLOAK) > 0;
	}

	public function canHaveCloak() : bool {
		return $this->getMaxHardware(HARDWARE_CLOAK) > 0;
	}


	public function hasActiveIllusion() : bool {
		if (!$this->hasIllusion()) {
			return false;
		}
		return $this->getIllusionShip() !== false;
	}

	public function hasIllusion() : bool {
		return $this->getHardware(HARDWARE_ILLUSION) > 0;
	}

	public function canHaveIllusion() : bool {
		return $this->getMaxHardware(HARDWARE_ILLUSION) > 0;
	}

	public function hasJump() : bool {
		return $this->getHardware(HARDWARE_JUMP) > 0;
	}

	public function canHaveJump() : bool {
		return $this->getMaxHardware(HARDWARE_JUMP) > 0;
	}

	public function hasDCS() : bool {
		return $this->getHardware(HARDWARE_DCS) > 0;
	}

	public function canHaveDCS() : bool {
		return $this->getMaxHardware(HARDWARE_DCS) > 0;
	}

	public function hasScanner() : bool {
		return $this->getHardware(HARDWARE_SCANNER) > 0;
	}

	public function canHaveScanner() : bool {
		return $this->getMaxHardware(HARDWARE_SCANNER) > 0;
	}

	abstract public function decloak() : void;

	abstract public function enableCloak() : void;

	abstract public function setIllusion(int $ship_id, int $attack, int $defense) : void;

	abstract public function disableIllusion() : void;

	public function getIllusionShipID() : int {
		$this->getIllusionShip();
		return $this->illusionShip['ID'];
	}

	public function getIllusionShipName() : string {
		$this->getIllusionShip();
		return $this->illusionShip['Name'];
	}

	abstract public function getIllusionShip() : array|false;

	public function getIllusionAttack() : int {
		$this->getIllusionShip();
		return $this->illusionShip['Attack'];
	}

	public function getIllusionDefense() : int {
		$this->getIllusionShip();
		return $this->illusionShip['Defense'];
	}

	public function getPlayer() : AbstractSmrPlayer {
		return $this->player;
	}

	public function getGameID() : int {
		return $this->gameID;
	}

	public function getGame() : SmrGame {
		return SmrGame::getGame($this->gameID);
	}

	public function getShipTypeID() : int {
		return $this->baseShip['ShipTypeID'];
	}

	public function getShipClassID() : int {
		return $this->baseShip['ShipClassID'];
	}

	/**
	 * Switch to a new ship, updating player turns accordingly.
	 */
	public function setShipTypeID(int $shipTypeID) : void {
		$oldSpeed = $this->getSpeed();
		$this->getPlayer()->setShipTypeID($shipTypeID);
		$this->regenerateBaseShip();
		$newSpeed = $this->getSpeed();

		// Update the player's turns to account for the speed change
		$oldTurns = $this->getPlayer()->getTurns();
		$this->getPlayer()->setTurns(IRound($oldTurns * $newSpeed / $oldSpeed));
	}

	public function getName() : string {
		return $this->baseShip['Name'];
	}

	public function getCost() : int {
		return $this->baseShip['Cost'];
	}

	public function getCostToUpgrade(int $upgradeShipID) : int {
		$upgadeBaseShip = AbstractSmrShip::getBaseShip($upgradeShipID);
		return $upgadeBaseShip['Cost'] - IFloor($this->getCost() * SHIP_REFUND_PERCENT);
	}

	public function getCostToUpgradeAndUNO(int $upgradeShipID) : int {
		return $this->getCostToUpgrade($upgradeShipID) + $this->getCostToUNOAgainstShip($upgradeShipID);
	}

	protected function getCostToUNOAgainstShip(int $shipID) : int {
		$baseShip = AbstractSmrShip::getBaseShip($shipID);
		$cost = 0;
		$hardwareTypes = array(HARDWARE_SHIELDS, HARDWARE_ARMOUR, HARDWARE_CARGO);
		foreach ($hardwareTypes as $hardwareTypeID) {
			$cost += max(0, $baseShip['MaxHardware'][$hardwareTypeID] - $this->getHardware($hardwareTypeID)) * Globals::getHardwareCost($hardwareTypeID);
		}
		return $cost;
	}

	public function getCostToUNO() : int {
		return $this->getCostToUNOAgainstShip($this->getShipTypeID());
	}

	/**
	 * Returns the base ship speed (unmodified by the game speed).
	 */
	public function getSpeed() : int {
		return $this->baseShip['Speed'];
	}

	/**
	 * Returns the ship speed modified by the game speed.
	 */
	public function getRealSpeed() : float {
		return $this->getSpeed() * $this->getGame()->getGameSpeed();
	}

	public function getHardware(int $hardwareTypeID = null) : array|int {
		if ($hardwareTypeID === null) {
			return $this->hardware;
		}
		return $this->hardware[$hardwareTypeID] ?? 0;
	}

	public function setHardware(int $hardwareTypeID, int $amount) : void {
		if ($this->getHardware($hardwareTypeID) === $amount) {
			return;
		}
		$this->hardware[$hardwareTypeID] = $amount;
		$this->hasChangedHardware[$hardwareTypeID] = true;
	}

	public function increaseHardware(int $hardwareTypeID, int $amount) : void {
		$this->setHardware($hardwareTypeID, $this->getHardware($hardwareTypeID) + $amount);
	}

	public function getOldHardware(int $hardwareTypeID = null) : array|int {
		if ($hardwareTypeID === null) {
			return $this->oldHardware;
		}
		return $this->oldHardware[$hardwareTypeID] ?? 0;
	}

	public function setOldHardware(int $hardwareTypeID, int $amount) : void {
		if ($this->getOldHardware($hardwareTypeID) == $amount) {
			return;
		}
		$this->oldHardware[$hardwareTypeID] = $amount;
		$this->hasChangedHardware[$hardwareTypeID] = true;
	}

	public function hasMaxHardware(int $hardwareTypeID) : bool {
		return $this->getHardware($hardwareTypeID) == $this->getMaxHardware($hardwareTypeID);
	}

	public function getMaxHardware(int $hardwareTypeID = null) : array|int {
		if ($hardwareTypeID === null) {
			return $this->baseShip['MaxHardware'];
		}
		return $this->baseShip['MaxHardware'][$hardwareTypeID];
	}

	public function getShields() : int {
		return $this->getHardware(HARDWARE_SHIELDS);
	}

	public function setShields(int $amount, bool $updateOldAmount = false) : void {
		if ($updateOldAmount && !$this->hasLostShields()) {
			$this->setOldHardware(HARDWARE_SHIELDS, $amount);
		}
		$this->setHardware(HARDWARE_SHIELDS, $amount);
	}

	public function decreaseShields(int $amount) : void {
		$this->setShields($this->getShields() - $amount);
	}

	public function increaseShields(int $amount) : void {
		$this->setShields($this->getShields() + $amount);
	}

	public function getOldShields() : int {
		return $this->getOldHardware(HARDWARE_SHIELDS);
	}

	public function setOldShields(int $amount) : void {
		$this->setOldHardware(HARDWARE_SHIELDS, $amount);
	}

	public function hasShields() : bool {
		return $this->getShields() > 0;
	}

	public function hasLostShields() : bool {
		return $this->getShields() < $this->getOldShields();
	}

	public function hasMaxShields() : bool {
		return $this->getShields() == $this->getMaxShields();
	}

	public function getMaxShields() : int {
		return $this->getMaxHardware(HARDWARE_SHIELDS);
	}

	public function getArmour() : int {
		return $this->getHardware(HARDWARE_ARMOUR);
	}

	public function setArmour(int $amount, bool $updateOldAmount = false) : void {
		if ($updateOldAmount && !$this->hasLostArmour()) {
			$this->setOldHardware(HARDWARE_ARMOUR, $amount);
		}
		$this->setHardware(HARDWARE_ARMOUR, $amount);
	}

	public function decreaseArmour(int $amount) : void {
		$this->setArmour($this->getArmour() - $amount);
	}

	public function increaseArmour(int $amount) : void {
		$this->setArmour($this->getArmour() + $amount);
	}

	public function getOldArmour() : int {
		return $this->getOldHardware(HARDWARE_ARMOUR);
	}

	public function setOldArmour(int $amount) : void {
		$this->setOldHardware(HARDWARE_ARMOUR, $amount);
	}

	public function hasArmour() : bool {
		return $this->getArmour() > 0;
	}

	public function hasLostArmour() : bool {
		return $this->getArmour() < $this->getOldArmour();
	}

	public function hasMaxArmour() : bool {
		return $this->getArmour() == $this->getMaxArmour();
	}

	public function getMaxArmour() : int {
		return $this->getMaxHardware(HARDWARE_ARMOUR);
	}

	public function isDead() : bool {
		return !$this->hasArmour() && !$this->hasShields();
	}

	public function canAcceptCDs() : bool {
		return $this->getCDs() < $this->getMaxCDs();
	}

	public function canAcceptSDs() : bool {
		return $this->getSDs() < $this->getMaxSDs();
	}

	public function canAcceptMines() : bool {
		return $this->getMines() < $this->getMaxMines();
	}

	public function hasCDs() : bool {
		return $this->getCDs() > 0;
	}

	public function hasSDs() : bool {
		return $this->getSDs() > 0;
	}

	public function hasMines() : bool {
		return $this->getMines() > 0;
	}

	public function getCDs() : int {
		return $this->getHardware(HARDWARE_COMBAT);
	}

	public function setCDs(int $amount, bool $updateOldAmount = false) : void {
		if ($updateOldAmount && !$this->hasLostCDs()) {
			$this->setOldHardware(HARDWARE_COMBAT, $amount);
		}
		$this->setHardware(HARDWARE_COMBAT, $amount);
	}

	/**
	 * Decreases the ship CDs. Use $updateOldAmount=true to prevent
	 * this change from triggering `isUnderAttack`.
	 */
	public function decreaseCDs(int $amount, bool $updateOldAmount = false) : void {
		$this->setCDs($this->getCDs() - $amount, $updateOldAmount);
	}

	public function increaseCDs(int $amount) : void {
		$this->setCDs($this->getCDs() + $amount);
	}

	public function getOldCDs() : int {
		return $this->getOldHardware(HARDWARE_COMBAT);
	}

	public function setOldCDs(int $amount) : void {
		$this->setOldHardware(HARDWARE_COMBAT, $amount);
	}

	public function hasLostCDs() : bool {
		return $this->getCDs() < $this->getOldCDs();
	}

	public function getMaxCDs() : int {
		return $this->getMaxHardware(HARDWARE_COMBAT);
	}

	public function getSDs() : int {
		return $this->getHardware(HARDWARE_SCOUT);
	}

	public function setSDs(int $amount) : void {
		$this->setHardware(HARDWARE_SCOUT, $amount);
	}

	public function decreaseSDs(int $amount) : void {
		$this->setSDs($this->getSDs() - $amount);
	}

	public function increaseSDs(int $amount) : void {
		$this->setSDs($this->getSDs() + $amount);
	}

	public function getMaxSDs() : int {
		return $this->getMaxHardware(HARDWARE_SCOUT);
	}

	public function getMines() : int {
		return $this->getHardware(HARDWARE_MINE);
	}

	public function setMines(int $amount) : void {
		$this->setHardware(HARDWARE_MINE, $amount);
	}

	public function decreaseMines(int $amount) : void {
		$this->setMines($this->getMines() - $amount);
	}

	public function increaseMines(int $amount) : void {
		$this->setMines($this->getMines() + $amount);
	}

	public function getMaxMines() : int {
		return $this->getMaxHardware(HARDWARE_MINE);
	}

	public function getCargoHolds() : int {
		return $this->getHardware(HARDWARE_CARGO);
	}

	public function setCargoHolds(int $amount) : void {
		$this->setHardware(HARDWARE_CARGO, $amount);
	}

	public function getCargo(int $goodID = null) : int|array {
		if ($goodID === null) {
			return $this->cargo;
		}
		return $this->cargo[$goodID] ?? 0;
	}

	public function hasCargo(int $goodID = null) : bool {
		if ($goodID === null) {
			return $this->getUsedHolds() > 0;
		}
		return $this->getCargo($goodID) > 0;
	}

	public function setCargo(int $goodID, int $amount) : void {
		if ($this->getCargo($goodID) === $amount) {
			return;
		}
		$this->cargo[$goodID] = $amount;
		$this->hasChangedCargo = true;
		// Sort cargo by goodID to make sure it shows up in the correct order
		// before the next page is loaded.
		ksort($this->cargo);
	}

	public function decreaseCargo(int $goodID, int $amount) : void {
		if ($amount < 0) {
			throw new Exception('Trying to decrease negative cargo.');
		}
		$this->setCargo($goodID, $this->getCargo($goodID) - $amount);
	}

	public function increaseCargo(int $goodID, int $amount) : void {
		if ($amount < 0) {
			throw new Exception('Trying to increase negative cargo.');
		}
		$this->setCargo($goodID, $this->getCargo($goodID) + $amount);
	}

	public function getEmptyHolds() : int {
		return $this->getCargoHolds() - $this->getUsedHolds();
	}

	public function getUsedHolds() : int {
		return array_sum($this->getCargo());
	}

	public function hasMaxCargoHolds() : bool {
		return $this->getCargoHolds() === $this->getMaxCargoHolds();
	}

	public function getMaxCargoHolds() : int {
		return $this->getMaxHardware(HARDWARE_CARGO);
	}

	public function isUnderAttack() : bool {
		return $this->hasLostShields() || $this->hasLostArmour() || $this->hasLostCDs();
	}

	public function removeUnderAttack() : bool {
		global $var;
		$underAttack = $this->isUnderAttack();
		$this->setOldShields($this->getShields());
		$this->setOldCDs($this->getCDs());
		$this->setOldArmour($this->getArmour());
		if (isset($var['UnderAttack'])) {
			return $var['UnderAttack'];
		}
		if ($underAttack && !USING_AJAX) {
			SmrSession::updateVar('UnderAttack', $underAttack); //Remember we are under attack for AJAX
		}
		return $underAttack;
	}

	public function hasWeapons() : bool {
		return $this->getNumWeapons() > 0;
	}

	public function getWeapons() : array {
		return $this->weapons;
	}

	public function canAttack() : bool {
		return $this->hasWeapons() || $this->hasCDs();
	}

	public function getNumWeapons() : int {
		return count($this->getWeapons());
	}

	public function getOpenWeaponSlots() : int {
		return $this->getHardpoints() - $this->getNumWeapons();
	}

	public function hasOpenWeaponSlots() : bool {
		return $this->getOpenWeaponSlots() > 0;
	}

	public function getHardpoints() : int {
		return $this->baseShip['Hardpoint'];
	}

	public function getTotalShieldDamage() : int {
		$shieldDamage = 0;
		foreach ($this->getWeapons() as $weapon) {
			$shieldDamage += $weapon->getShieldDamage();
		}
		return $shieldDamage;
	}

	public function getTotalArmourDamage() : int {
		$armourDamage = 0;
		foreach ($this->getWeapons() as $weapon) {
			$armourDamage += $weapon->getArmourDamage();
		}
		return $armourDamage;
	}

	public function isFederal() : bool {
		return $this->getShipTypeID() === SHIP_TYPE_FEDERAL_DISCOVERY ||
		       $this->getShipTypeID() === SHIP_TYPE_FEDERAL_WARRANT ||
		       $this->getShipTypeID() === SHIP_TYPE_FEDERAL_ULTIMATUM;
	}

	public function isUnderground() : bool {
		return $this->getShipTypeID() === SHIP_TYPE_THIEF ||
		       $this->getShipTypeID() === SHIP_TYPE_ASSASSIN ||
		       $this->getShipTypeID() === SHIP_TYPE_DEATH_CRUISER;
	}

	public function shootPlayers(array $targetPlayers) : array {
		$thisPlayer = $this->getPlayer();
		$results = array('Player' => $thisPlayer, 'TotalDamage' => 0, 'Weapons' => []);
		if ($thisPlayer->isDead()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		foreach ($this->weapons as $orderID => $weapon) {
			$results['Weapons'][$orderID] = $weapon->shootPlayer($thisPlayer, array_rand_value($targetPlayers));
			if ($results['Weapons'][$orderID]['Hit']) {
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
			}
		}
		if ($this->hasCDs()) {
			$thisCDs = new SmrCombatDrones($this->getGameID(), $this->getCDs());
			$results['Drones'] = $thisCDs->shootPlayer($thisPlayer, array_rand_value($targetPlayers));
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
		}
		$thisPlayer->increaseExperience(IRound($results['TotalDamage'] * self::EXP_PER_DAMAGE_PLAYER));
		$thisPlayer->increaseHOF($results['TotalDamage'], array('Combat', 'Player', 'Damage Done'), HOF_PUBLIC);
		$thisPlayer->increaseHOF(1, array('Combat', 'Player', 'Shots'), HOF_PUBLIC);
		return $results;
	}

	public function shootForces(SmrForce $forces) : array {
		$thisPlayer = $this->getPlayer();
		$results = array('Player' => $thisPlayer, 'TotalDamage' => 0, 'Weapons' => []);
		if ($thisPlayer->isDead()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		foreach ($this->weapons as $orderID => $weapon) {
			$results['Weapons'][$orderID] = $weapon->shootForces($thisPlayer, $forces);
			if ($results['Weapons'][$orderID]['Hit']) {
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['NumMines'], array('Combat', 'Forces', 'Mines', 'Killed'), HOF_PUBLIC);
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['Mines'], array('Combat', 'Forces', 'Mines', 'Damage Done'), HOF_PUBLIC);
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['NumCDs'], array('Combat', 'Forces', 'Combat Drones', 'Killed'), HOF_PUBLIC);
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['CDs'], array('Combat', 'Forces', 'Combat Drones', 'Damage Done'), HOF_PUBLIC);
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['NumSDs'], array('Combat', 'Forces', 'Scout Drones', 'Killed'), HOF_PUBLIC);
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['SDs'], array('Combat', 'Forces', 'Scout Drones', 'Damage Done'), HOF_PUBLIC);
				$thisPlayer->increaseHOF($results['Weapons'][$orderID]['ActualDamage']['NumMines'] + $results['Weapons'][$orderID]['ActualDamage']['NumCDs'] + $results['Weapons'][$orderID]['ActualDamage']['NumSDs'], array('Combat', 'Forces', 'Killed'), HOF_PUBLIC);
			}
		}
		if ($this->hasCDs()) {
			$thisCDs = new SmrCombatDrones($this->getGameID(), $this->getCDs());
			$results['Drones'] = $thisCDs->shootForces($thisPlayer, $forces);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['NumMines'], array('Combat', 'Forces', 'Mines', 'Killed'), HOF_PUBLIC);
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['Mines'], array('Combat', 'Forces', 'Mines', 'Damage Done'), HOF_PUBLIC);
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['NumCDs'], array('Combat', 'Forces', 'Combat Drones', 'Killed'), HOF_PUBLIC);
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['CDs'], array('Combat', 'Forces', 'Combat Drones', 'Damage Done'), HOF_PUBLIC);
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['NumSDs'], array('Combat', 'Forces', 'Scout Drones', 'Killed'), HOF_PUBLIC);
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['SDs'], array('Combat', 'Forces', 'Scout Drones', 'Damage Done'), HOF_PUBLIC);
			$thisPlayer->increaseHOF($results['Drones']['ActualDamage']['NumMines'] + $results['Drones']['ActualDamage']['NumCDs'] + $results['Drones']['ActualDamage']['NumSDs'], array('Combat', 'Forces', 'Killed'), HOF_PUBLIC);
		}
		$thisPlayer->increaseExperience(IRound($results['TotalDamage'] * self::EXP_PER_DAMAGE_FORCE));
		$thisPlayer->increaseHOF($results['TotalDamage'], array('Combat', 'Forces', 'Damage Done'), HOF_PUBLIC);
		$thisPlayer->increaseHOF(1, array('Combat', 'Forces', 'Shots'), HOF_PUBLIC);
		return $results;
	}

	public function shootPort(SmrPort $port) : array {
		$thisPlayer = $this->getPlayer();
		$results = array('Player' => $thisPlayer, 'TotalDamage' => 0, 'Weapons' => []);
		if ($thisPlayer->isDead()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		foreach ($this->weapons as $orderID => $weapon) {
			$results['Weapons'][$orderID] = $weapon->shootPort($thisPlayer, $port);
			if ($results['Weapons'][$orderID]['Hit']) {
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
			}
		}
		if ($this->hasCDs()) {
			$thisCDs = new SmrCombatDrones($this->getGameID(), $this->getCDs());
			$results['Drones'] = $thisCDs->shootPort($thisPlayer, $port);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
		}
		$thisPlayer->increaseExperience(IRound($results['TotalDamage'] * self::EXP_PER_DAMAGE_PORT));
		$thisPlayer->increaseHOF($results['TotalDamage'], array('Combat', 'Port', 'Damage Done'), HOF_PUBLIC);
//		$thisPlayer->increaseHOF(1,array('Combat','Port','Shots')); //in SmrPortt::attackedBy()

		// Change alignment if we reach a damage threshold.
		// Increase if player and port races are at war; decrease otherwise.
		if ($results['TotalDamage'] >= SmrPort::DAMAGE_NEEDED_FOR_ALIGNMENT_CHANGE) {
			$relations = Globals::getRaceRelations($thisPlayer->getGameID(), $thisPlayer->getRaceID());
			if ($relations[$port->getRaceID()] <= RELATIONS_WAR) {
				$thisPlayer->increaseAlignment(1);
				$thisPlayer->increaseHOF(1, array('Combat', 'Port', 'Alignment', 'Gain'), HOF_PUBLIC);
			} else {
				$thisPlayer->decreaseAlignment(1);
				$thisPlayer->increaseHOF(1, array('Combat', 'Port', 'Alignment', 'Loss'), HOF_PUBLIC);
			}
		}
		return $results;
	}

	public function shootPlanet(SmrPlanet $planet, bool $delayed) : array {
		$thisPlayer = $this->getPlayer();
		$results = array('Player' => $thisPlayer, 'TotalDamage' => 0, 'Weapons' => []);
		if ($thisPlayer->isDead()) {
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		foreach ($this->weapons as $orderID => $weapon) {
			$results['Weapons'][$orderID] = $weapon->shootPlanet($thisPlayer, $planet, $delayed);
			if ($results['Weapons'][$orderID]['Hit']) {
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
			}
		}
		if ($this->hasCDs()) {
			$thisCDs = new SmrCombatDrones($this->getGameID(), $this->getCDs());
			$results['Drones'] = $thisCDs->shootPlanet($thisPlayer, $planet, $delayed);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
		}
		$thisPlayer->increaseExperience(IRound($results['TotalDamage'] * self::EXP_PER_DAMAGE_PLANET));
		$thisPlayer->increaseHOF($results['TotalDamage'], array('Combat', 'Planet', 'Damage Done'), HOF_PUBLIC);
//		$thisPlayer->increaseHOF(1,array('Combat','Planet','Shots')); //in SmrPlanet::attackedBy()
		return $results;
	}

	public function doWeaponDamage(array $damage) : array {
		$alreadyDead = $this->getPlayer()->isDead();
		$armourDamage = 0;
		$cdDamage = 0;
		$shieldDamage = 0;
		if (!$alreadyDead) {
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
		}
		return array(
						'KillingShot' => !$alreadyDead && $this->isDead(),
						'TargetAlreadyDead' => $alreadyDead,
						'Shield' => $shieldDamage,
						'CDs' => $cdDamage,
						'NumCDs' => $cdDamage / CD_ARMOUR,
						'Armour' => $armourDamage,
						'HasCDs' => $this->hasCDs(),
						'TotalDamage' => $shieldDamage + $cdDamage + $armourDamage
		);
	}

	public function doMinesDamage(array $damage) : array {
		$alreadyDead = $this->getPlayer()->isDead();
		$armourDamage = 0;
		$cdDamage = 0;
		$shieldDamage = 0;
		if (!$alreadyDead) {
			$shieldDamage = $this->doShieldDamage(min($damage['MaxDamage'], $damage['Shield']));
			$damage['MaxDamage'] -= $shieldDamage;
			if (!$this->hasShields() && ($shieldDamage == 0 || $damage['Rollover'])) { //skip CDs if it's mines
				$armourDamage = $this->doArmourDamage(min($damage['MaxDamage'], $damage['Armour']));
			}
		}
		return array(
						'KillingShot' => !$alreadyDead && $this->isDead(),
						'TargetAlreadyDead' => $alreadyDead,
						'Shield' => $shieldDamage,
						'CDs' => $cdDamage,
						'NumCDs' => $cdDamage / CD_ARMOUR,
						'Armour' => $armourDamage,
						'HasCDs' => $this->hasCDs(),
						'TotalDamage' => $shieldDamage + $cdDamage + $armourDamage
		);
	}

	protected function doShieldDamage(int $damage) : int {
		$actualDamage = min($this->getShields(), $damage);
		$this->decreaseShields($actualDamage);
		return $actualDamage;
	}

	protected function doCDDamage(int $damage) : int {
		$actualDamage = min($this->getCDs(), IFloor($damage / CD_ARMOUR));
		$this->decreaseCDs($actualDamage);
		return $actualDamage * CD_ARMOUR;
	}

	protected function doArmourDamage(int $damage) : int {
		$actualDamage = min($this->getArmour(), $damage);
		$this->decreaseArmour($actualDamage);
		return $actualDamage;
	}

	/**
	 * Returns the maneuverability rating for this ship.
	 */
	public function getMR() : int {
		//700 - [ (ship hit points / 25) + (ship stat factors) ]
		//Minimum value of 0 because negative values cause issues with calculations calling this routine
		return max(0, IRound(
						700 -
						(
							(
								$this->getShields()
								+$this->getArmour()
								+$this->getCDs() * 3
							) / 25
							+(
								$this->getCargoHolds() / 100
								-$this->getSpeed() * 5
								+($this->getHardpoints()/*+$ship['Increases']['Ship Power']*/) * 5
								/*+(
									$ship['Increases']['Mines']
									+$ship['Increases']['Scout Drones']
								)/12*/
								+$this->getCDs() / 5
							)
						)
					)
					);
	}

}
