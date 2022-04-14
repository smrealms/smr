<?php declare(strict_types=1);

/**
 * Defines a concrete realization of a weapon type for ships/planets.
 */
class SmrWeapon extends AbstractSmrCombatWeapon {

	use Traits\RaceID;

	protected const BONUS_DAMAGE = 15; // additive bonus
	protected const BONUS_ACCURACY = 4; // additive bonus

	protected const HIGHEST_POWER_LEVEL = 5; // must track the highest power level in db

	protected int $weaponTypeID;
	protected SmrWeaponType $weaponType;
	protected bool $bonusAccuracy = false; // default
	protected bool $bonusDamage = false; // default
	protected bool $damageRollover = false; // fixed for all SmrWeapons

	public static function getWeapon(int $weaponTypeID, Smr\DatabaseRecord $dbRecord = null): self {
		return new self($weaponTypeID, $dbRecord);
	}

	protected function __construct(int $weaponTypeID, Smr\DatabaseRecord $dbRecord = null) {
		$this->weaponType = SmrWeaponType::getWeaponType($weaponTypeID, $dbRecord);
		$this->weaponTypeID = $weaponTypeID;
		$this->raceID = $this->weaponType->getRaceID();
	}

	public function hasBonusAccuracy(): bool {
		return $this->bonusAccuracy;
	}

	public function setBonusAccuracy(bool $bonusAccuracy): void {
		$this->bonusAccuracy = $bonusAccuracy;
	}

	public function hasBonusDamage(): bool {
		return $this->bonusDamage;
	}

	public function setBonusDamage(bool $bonusDamage): void {
		$this->bonusDamage = $bonusDamage;
	}

	private function hasEnhancements(): bool {
		return $this->getNumberOfEnhancements() > 0;
	}

	private function getNumberOfEnhancements(): int {
		return (int)$this->bonusAccuracy + (int)$this->bonusDamage;
	}

	/**
	 * Return weapon name suitable for HTML display.
	 * The name is displayed in green with pluses if enhancements are present.
	 */
	public function getName(): string {
		$name = $this->weaponType->getName();
		if ($this->hasEnhancements()) {
			$name = '<span class="green">' . $name . str_repeat('+', $this->getNumberOfEnhancements()) . '</span>';
		}
		return $name;
	}

	/**
	 * Return the weapon base accuracy.
	 */
	public function getBaseAccuracy(): int {
		$baseAccuracy = $this->weaponType->getAccuracy();
		if ($this->bonusAccuracy) {
			$baseAccuracy += self::BONUS_ACCURACY;
		}
		return $baseAccuracy;
	}

	/**
	 * Return the weapon shield damage.
	 */
	public function getShieldDamage(): int {
		$shieldDamage = $this->weaponType->getShieldDamage();
		if ($this->bonusDamage && $shieldDamage > 0) {
			$shieldDamage += self::BONUS_DAMAGE;
		}
		return $shieldDamage;
	}

	/**
	 * Return the weapon armour damage.
	 */
	public function getArmourDamage(): int {
		$armourDamage = $this->weaponType->getArmourDamage();
		if ($this->bonusDamage && $armourDamage > 0) {
			$armourDamage += self::BONUS_DAMAGE;
		}
		return $armourDamage;
	}

	public function getBuyHREF(SmrLocation $location): string {
		$container = Page::create('shop_weapon_processing.php');
		$container['LocationID'] = $location->getTypeID();
		$container['Weapon'] = $this;
		return $container->href();
	}

	public function getSellHREF(SmrLocation $location, int $orderID): string {
		$container = Page::create('shop_weapon_processing.php');
		$container['LocationID'] = $location->getTypeID();
		$container['Weapon'] = $this;
		$container['OrderID'] = $orderID;
		return $container->href();
	}

	public function getWeaponTypeID(): int {
		return $this->weaponTypeID;
	}

	/**
	 * Weapon cost is increased by 100% for each enhancement present
	 */
	public function getCost(): int {
		return $this->weaponType->getCost() * (1 + $this->getNumberOfEnhancements());
	}

	public function getPowerLevel(): int {
		return $this->weaponType->getPowerLevel();
	}

	public function getBuyerRestriction(): int {
		return $this->weaponType->getBuyerRestriction();
	}

	/**
	 * Ships are only allowed to equip one of each type of Unique weapon
	 */
	public function isUniqueType(): bool {
		return $this->getPowerLevel() === self::HIGHEST_POWER_LEVEL;
	}

	protected function getWeightedRandomForPlayer(AbstractSmrPlayer $player): WeightedRandom {
		return WeightedRandom::getWeightedRandomForPlayer($player, 'Weapon', $this->getWeaponTypeID());
	}

	/**
	 * Given $weaponAccuracy as a percent, decide if the weapon hits.
	 */
	protected function checkHit(AbstractSmrPlayer $player, float $weaponAccuracy): bool {
		// Skip weighting factor for guaranteed hits/misses.
		return match (true) {
			$weaponAccuracy >= 100 => true,
			$weaponAccuracy <= 0 => false,
			default => $this->getWeightedRandomForPlayer($player)->flipWeightedCoin($weaponAccuracy),
		};
	}

	public static function getPlayerLevelAccuracyMod(AbstractSmrPlayer $player): float {
		return ($player->getLevelID() * $player->getLevelID() / 60 + $player->getLevelID() / 2 + 2) / 100;
	}

	public function getModifiedAccuracy(AbstractSmrPlayer $weaponPlayer): float {
		$modifiedAccuracy = $this->getBaseAccuracy();
		$modifiedAccuracy += $this->getBaseAccuracy() * self::getPlayerLevelAccuracyMod($weaponPlayer);
		return $modifiedAccuracy;
	}

	public function getModifiedAccuracyAgainstForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces): float {
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
		return $modifiedAccuracy;
	}

	public function getModifiedAccuracyAgainstPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port): float {
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
		$modifiedAccuracy -= $this->getBaseAccuracy() * $port->getLevel() / 50;
		return $modifiedAccuracy;
	}

	public function getModifiedAccuracyAgainstPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet): float {
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
		$modifiedAccuracy -= $this->getBaseAccuracy() * $planet->getLevel() / 350;
		return $modifiedAccuracy;
	}

	public function getModifiedAccuracyAgainstPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer): float {
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
		$modifiedAccuracy -= $this->getBaseAccuracy() * self::getPlayerLevelAccuracyMod($targetPlayer) / 2;

		$weaponShip = $weaponPlayer->getShip();
		$targetShip = $targetPlayer->getShip();
		$mrDiff = $targetShip->getMR() - $weaponShip->getMR();
		if ($mrDiff > 0) {
			$modifiedAccuracy -= $this->getBaseAccuracy() * ($mrDiff / MR_FACTOR) / 100;
		}

		return $modifiedAccuracy;
	}

	public function getModifiedPortAccuracy(SmrPort $port): float {
		$modifiedAccuracy = $this->getBaseAccuracy();
		return $modifiedAccuracy;
	}

	public function getModifiedPortAccuracyAgainstPlayer(SmrPort $port, AbstractSmrPlayer $targetPlayer): float {
		$modifiedAccuracy = $this->getModifiedPortAccuracy($port);
		$modifiedAccuracy -= $this->getBaseAccuracy() * self::getPlayerLevelAccuracyMod($targetPlayer);
		return $modifiedAccuracy;
	}

	public function getModifiedPlanetAccuracy(SmrPlanet $planet): float {
		$modifiedAccuracy = $this->getBaseAccuracy();
		if ($this->getWeaponTypeID() == WEAPON_PLANET_TURRET) {
			$modifiedAccuracy += $planet->getLevel() / 2;
		} else {
			$modifiedAccuracy += $planet->getAccuracyBonus();
		}
		return $modifiedAccuracy;
	}

	public function getModifiedPlanetAccuracyAgainstPlayer(SmrPlanet $planet, AbstractSmrPlayer $targetPlayer): float {
		$modifiedAccuracy = $this->getModifiedPlanetAccuracy($planet);
		$modifiedAccuracy -= $this->getBaseAccuracy() * self::getPlayerLevelAccuracyMod($targetPlayer);
		return $modifiedAccuracy;
	}

	public function getModifiedDamageAgainstForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces): array {
		if (!$this->canShootForces()) {
			// If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return ['MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
		}
		$damage = $this->getDamage();
		return $damage;
	}

	public function getModifiedDamageAgainstPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port): array {
		if (!$this->canShootPorts()) {
			// If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return ['MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
		}
		$damage = $this->getDamage();
		return $damage;
	}

	public function getModifiedDamageAgainstPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet): array {
		if (!$this->canShootPlanets()) {
			// If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return ['MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
		}
		$damage = $this->getDamage();

		$planetMod = self::PLANET_DAMAGE_MOD;
		$damage['MaxDamage'] = ICeil($damage['MaxDamage'] * $planetMod);
		$damage['Shield'] = ICeil($damage['Shield'] * $planetMod);
		$damage['Armour'] = ICeil($damage['Armour'] * $planetMod);

		return $damage;
	}

	public function getModifiedForceDamageAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer): array {
		$return = ['MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
		return $return;
	}

	public function getModifiedDamageAgainstPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer): array {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculating any damage mods.
			$return = ['MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
			return $return;
		}
		$damage = $this->getDamage();
		return $damage;
	}

	public function getModifiedPortDamageAgainstPlayer(SmrPort $port, AbstractSmrPlayer $targetPlayer): array {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculating any damage mods.
			$return = ['MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
			return $return;
		}
		$damage = $this->getDamage();
		return $damage;
	}

	public function getModifiedPlanetDamageAgainstPlayer(SmrPlanet $planet, AbstractSmrPlayer $targetPlayer): array {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculating any damage mods.
			$return = ['MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
			return $return;
		}
		$damage = $this->getDamage();
		return $damage;
	}

	public function shootForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces): array {
		$return = ['Weapon' => $this, 'TargetForces' => $forces, 'Hit' => false];
		$modifiedAccuracy = $this->getModifiedAccuracyAgainstForces($weaponPlayer, $forces);
		if ($this->checkHit($weaponPlayer, $modifiedAccuracy)) {
			$return['Hit'] = true;
			return $this->doPlayerDamageToForce($return, $weaponPlayer, $forces);
		}
		return $return;
	}

	public function shootPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port): array {
		$return = ['Weapon' => $this, 'TargetPort' => $port, 'Hit' => false];
		$modifiedAccuracy = $this->getModifiedAccuracyAgainstPort($weaponPlayer, $port);
		if ($this->checkHit($weaponPlayer, $modifiedAccuracy)) {
			$return['Hit'] = true;
			return $this->doPlayerDamageToPort($return, $weaponPlayer, $port);
		}
		return $return;
	}

	public function shootPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet): array {
		$return = ['Weapon' => $this, 'TargetPlanet' => $planet, 'Hit' => false];
		$modifiedAccuracy = $this->getModifiedAccuracyAgainstPlanet($weaponPlayer, $planet);
		if ($this->checkHit($weaponPlayer, $modifiedAccuracy)) {
			$return['Hit'] = true;
			return $this->doPlayerDamageToPlanet($return, $weaponPlayer, $planet);
		}
		return $return;
	}

	public function shootPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer): array {
		$return = ['Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => false];
		$modifiedAccuracy = $this->getModifiedAccuracyAgainstPlayer($weaponPlayer, $targetPlayer);
		if ($this->checkHit($weaponPlayer, $modifiedAccuracy)) {
			$return['Hit'] = true;
			return $this->doPlayerDamageToPlayer($return, $weaponPlayer, $targetPlayer);
		}
		return $return;
	}

	public function shootPlayerAsForce(SmrForce $forces, AbstractSmrPlayer $targetPlayer): array {
		$return = ['Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => false];
		return $return;
	}

	public function shootPlayerAsPort(SmrPort $port, AbstractSmrPlayer $targetPlayer): array {
		$return = ['Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => false];
		$modifiedAccuracy = $this->getModifiedPortAccuracyAgainstPlayer($port, $targetPlayer);
		if ($this->checkHit($targetPlayer, $modifiedAccuracy)) {
			$return['Hit'] = true;
			return $this->doPortDamageToPlayer($return, $port, $targetPlayer);
		}
		return $return;
	}

	public function shootPlayerAsPlanet(SmrPlanet $planet, AbstractSmrPlayer $targetPlayer): array {
		$return = ['Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => false];
		$modifiedAccuracy = $this->getModifiedPlanetAccuracyAgainstPlayer($planet, $targetPlayer);
		if ($this->checkHit($targetPlayer, $modifiedAccuracy)) {
			$return['Hit'] = true;
			return $this->doPlanetDamageToPlayer($return, $planet, $targetPlayer);
		}
		return $return;
	}

}
