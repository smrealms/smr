<?php declare(strict_types=1);

namespace Smr\Combat\Weapon;

use Exception;
use Override;
use Smr\AbstractShip;
use Smr\BuyerRestriction;
use Smr\Combat\CombatantInterface;
use Smr\Combat\Results\Damage\WeaponDamage;
use Smr\Combat\Results\Weapon\HitWeaponResult;
use Smr\Combat\Results\Weapon\MissedWeaponResult;
use Smr\Combat\WeaponShotAtCombatant;
use Smr\DatabaseRecord;
use Smr\Location;
use Smr\Pages\Player\ShopWeaponProcessor;
use Smr\Planet;
use Smr\Port;
use Smr\Traits\RaceID;
use Smr\WeaponType;
use Smr\WeightedRandom;

/**
 * Defines a concrete realization of a weapon type for ships/planets.
 */
class Weapon extends AbstractWeapon {

	use RaceID;

	protected const int BONUS_DAMAGE = 15; // additive bonus
	protected const int BONUS_ACCURACY = 4; // additive bonus

	protected const int HIGHEST_POWER_LEVEL = 5; // must track the highest power level in db

	protected readonly WeaponType $weaponType;
	protected bool $bonusAccuracy = false; // default
	protected bool $bonusDamage = false; // default
	#[Override]
	protected bool $damageRollover = false; // fixed for all Weapons

	public static function getWeapon(int $weaponTypeID, ?DatabaseRecord $dbRecord = null): self {
		return new self($weaponTypeID, $dbRecord);
	}

	protected function __construct(
		protected readonly int $weaponTypeID,
		?DatabaseRecord $dbRecord = null,
	) {
		$this->weaponType = WeaponType::getWeaponType($weaponTypeID, $dbRecord);
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

	public function getBuyHREF(Location $location): string {
		$container = new ShopWeaponProcessor($location->getTypeID(), $this);
		return $container->href();
	}

	public function getSellHREF(Location $location, int $orderID): string {
		$container = new ShopWeaponProcessor($location->getTypeID(), $this, $orderID);
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

	public function getBuyerRestriction(): BuyerRestriction {
		return $this->weaponType->getBuyerRestriction();
	}

	/**
	 * Ships are only allowed to equip one of each type of Unique weapon
	 */
	public function isUniqueType(): bool {
		return $this->getPowerLevel() === self::HIGHEST_POWER_LEVEL;
	}

	protected function getWeightedRandom(CombatantInterface $shooter, CombatantInterface $target): WeightedRandom {
		if ($shooter instanceof AbstractShip) {
			// If shooter is a player, use shooter's weighted random
			$player = $shooter->getPlayer();
			$type = 'Weapon';
		} elseif ($target instanceof AbstractShip) {
			// If target is a player and shooter is not, use target's weighted random
			$player = $target->getPlayer();
			$type = match (true) {
				$shooter instanceof Port => 'PortWeapon',
				$shooter instanceof Planet => 'PlanetWeapon',
				default => throw new Exception('Unsupported Combatant'),
			};
		} else {
			throw new Exception('Unsupported Combatant');
		}
		return WeightedRandom::getWeightedRandomForPlayer($player, $type, $this->getWeaponTypeID());
	}

	/**
	 * Given $weaponAccuracy as a percent, decide if the weapon hits.
	 */
	protected function checkHit(CombatantInterface $shooter, CombatantInterface $target, float $weaponAccuracy): bool {
		// Skip weighting factor for guaranteed hits/misses.
		return match (true) {
			$weaponAccuracy >= 100 => true,
			$weaponAccuracy <= 0 => false,
			default => $this->getWeightedRandom($shooter, $target)->flipWeightedCoin($weaponAccuracy),
		};
	}

	public static function getPlayerLevelAccuracyMod(AbstractShip $ship): float {
		$level = $ship->getLevel();
		return ($level * $level / 60 + $level / 2 + 2) / 100;
	}

	public function getModifiedPlayerAccuracy(AbstractShip $shooter, CombatantInterface $target): float {
		$modifiedAccuracy = $this->getBaseAccuracy() * (1 + self::getPlayerLevelAccuracyMod($shooter));
		if ($target instanceof Port) {
			$modifiedAccuracy -= $this->getBaseAccuracy() * $target->getLevel() / 50;
		} elseif ($target instanceof Planet) {
			$modifiedAccuracy -= $this->getBaseAccuracy() * $target->getLevel() / 350;
		} elseif ($target instanceof AbstractShip) {
			$modifiedAccuracy -= $this->getBaseAccuracy() * self::getPlayerLevelAccuracyMod($target) / 2;
			$mrDiff = $target->getMR() - $shooter->getMR();
			if ($mrDiff > 0) {
				$modifiedAccuracy -= $this->getBaseAccuracy() * ($mrDiff / MR_FACTOR) / 100;
			}
		}

		return $modifiedAccuracy;
	}

	public function getModifiedPortAccuracy(Port $port, AbstractShip $target): float {
		return $this->getBaseAccuracy() * (1 - self::getPlayerLevelAccuracyMod($target));
	}

	public function getPlanetAccuracy(Planet $planet): float {
		$modifiedAccuracy = $this->getBaseAccuracy();
		if ($this->getWeaponTypeID() === WEAPON_PLANET_TURRET) {
			$modifiedAccuracy += $planet->getLevel() / 2;
		} else {
			$modifiedAccuracy += $planet->getAccuracyBonus();
		}
		return $modifiedAccuracy;
	}

	public function getModifiedPlanetAccuracy(Planet $planet, AbstractShip $target): float {
		$modifiedAccuracy = $this->getPlanetAccuracy($planet);
		$modifiedAccuracy -= $this->getBaseAccuracy() * self::getPlayerLevelAccuracyMod($target);
		return $modifiedAccuracy;
	}

	public function getModifiedAccuracyAgainstTarget(CombatantInterface $shooter, CombatantInterface $target): float {
		if ($shooter instanceof AbstractShip) {
			return $this->getModifiedPlayerAccuracy($shooter, $target);
		}
		if (!($target instanceof AbstractShip)) {
			throw new Exception('Combatant not supported');
		}
		return match (true) {
			$shooter instanceof Port => $this->getModifiedPortAccuracy($shooter, $target),
			$shooter instanceof Planet => $this->getModifiedPlanetAccuracy($shooter, $target),
			default => throw new Exception('Combatant not supported'),
		};
	}

	public function getModifiedDamageAgainstTarget(CombatantInterface $shooter, CombatantInterface $target): WeaponDamage {
		if (!$this->canShootTarget($target)) {
			return new WeaponDamage(shieldDamage: 0, armourDamage: 0, damageRollover: $this->isDamageRollover());
		}
		$damage = $this->getDamage();

		if ($target instanceof Planet) {
			$planetMod = self::PLANET_DAMAGE_MOD;
			$damage->shieldDamage = ICeil($damage->shieldDamage * $planetMod);
			$damage->armourDamage = ICeil($damage->armourDamage * $planetMod);
		}

		return $damage;
	}

	/**
	 * @template TTarget of CombatantInterface
	 * @param WeaponShotAtCombatant<TTarget> $shot
	 * @return \Smr\Combat\Results\Weapon\MissedWeaponResult|HitWeaponResult<TTarget>
	 */
	public function shoot(WeaponShotAtCombatant $shot): MissedWeaponResult|HitWeaponResult {
		$shooter = $shot->shooter;
		$target = $shot->target;
		$modifiedAccuracy = $this->getModifiedAccuracyAgainstTarget($shooter, $target);
		if ($this->checkHit($shooter, $target, $modifiedAccuracy)) {
			return $this->hitTarget($shot);
		}
		return new MissedWeaponResult($this, $target);
	}

}
