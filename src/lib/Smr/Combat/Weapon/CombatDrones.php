<?php declare(strict_types=1);

namespace Smr\Combat\Weapon;

use Smr\Combat\CombatantInterface;
use Smr\Combat\Results\Damage\WeaponDamage;
use Smr\Combat\Results\Weapon\HitWeaponResult;
use Smr\Combat\WeaponShotAtCombatant;
use Smr\Force;
use Smr\Planet;
use Smr\Port;
use Smr\Ship;

class CombatDrones extends AbstractWeapon {

	use ForcesTrait;

	protected const int MAX_CDS_RAND = 54;
	protected const int MIN_CDS_RAND = 3;

	public function __construct(int $numberOfCDs, bool $portPlanetDrones = false) {
		$this->amount = $numberOfCDs;
		$this->name = 'Combat Drones';
		if ($portPlanetDrones === false) {
			$this->shieldDamage = 2;
			$this->armourDamage = 2;
		} else {
			$this->shieldDamage = 1;
			$this->armourDamage = 1;
		}
		$this->accuracy = 3;
		$this->damageRollover = true;
	}

	public function getModifiedAccuracyAgainstTarget(CombatantInterface $shooter, CombatantInterface $target): float {
		if ($shooter instanceof Planet || $shooter instanceof Port) {
			// Ports/planets launch all their CDs
			return 100;
		}

		$modifiedAccuracy = $this->getBaseAccuracy();
		$random = rand(self::MIN_CDS_RAND, self::MAX_CDS_RAND);

		if (($shooter instanceof Force) || !($target instanceof Ship)) {
			$modifiedAccuracy += $random;
		} else {
			// Player vs. Player
			assert($shooter instanceof Ship);
			$levelRand = rand(IFloor($shooter->getLevel() / 2), $shooter->getLevel());
			$modifiedAccuracy += ($random + $levelRand - ($target->getLevel() - $shooter->getLevel()) / 3) / 1.5;

			$mrDiff = $target->getMR() - $shooter->getMR();
			if ($mrDiff > 0) {
				$modifiedAccuracy -= $this->getBaseAccuracy() * ($mrDiff / MR_FACTOR) / 100;
			}
		}

		return max(0, min(100, $modifiedAccuracy));
	}

	public function getModifiedDamageAgainstTarget(
		CombatantInterface $shooter,
		CombatantInterface $target,
	): WeaponDamage {
		if (!$this->canShootTarget($target)) {
			return new WeaponDamage(
				shieldDamage: 0,
				armourDamage: 0,
				damageRollover: $this->isDamageRollover(),
			);
		}
		$baseDamage = $this->getDamage();
		$launched = ICeil($this->getAmount() * $this->getModifiedAccuracyAgainstTarget($shooter, $target) / 100);

		$isKamikaze = ($shooter instanceof Ship) && ($target instanceof Force) && $shooter->getPlayer()->isCombatDronesKamikazeOnMines();
		$kamikaze = $isKamikaze ? min($launched, $target->getMines()) : 0;

		// Compute damage modifier
		$mod = match (true) {
			$target instanceof Planet => self::PLANET_DAMAGE_MOD,
			$target instanceof Ship && $target->hasDCS() => $shooter->reduceDamageDoneDCS(),
			default => 1,
		};

		$normalLaunched = $launched - $kamikaze;
		return new WeaponDamage(
			shieldDamage: ICeil($mod * $baseDamage->shieldDamage * $normalLaunched),
			armourDamage: ICeil($mod * $baseDamage->armourDamage * $normalLaunched + $kamikaze * MINE_ARMOUR),
			damageRollover: $baseDamage->damageRollover,
			launched: $launched, // includes both normal and kamikaze
			kamikaze: $kamikaze,
		);
	}

	/**
	 * @template TTarget of CombatantInterface
	 * @param WeaponShotAtCombatant<TTarget> $shot
	 * @return HitWeaponResult<TTarget>
	 */
	public function shoot(WeaponShotAtCombatant $shot): HitWeaponResult {
		$shooter = $shot->shooter;
		$return = $this->hitTarget($shot);
		if ($return->weaponDamage->kamikaze > 0) {
			$shooter->decreaseCDs($return->weaponDamage->kamikaze);
		}
		return $return;
	}

}
