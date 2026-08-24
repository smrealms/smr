<?php declare(strict_types=1);

namespace Smr\Combat\Weapon;

use Exception;
use Smr\Combat\CombatantInterface;
use Smr\Combat\Results\Damage\WeaponDamage;
use Smr\Combat\Results\Weapon\HitWeaponResult;
use Smr\Combat\WeaponShotAtCombatant;
use Smr\Force;
use Smr\Ship;

class Mines extends AbstractWeapon {

	use ForcesTrait;

	protected const float TOTAL_ENEMY_MINES_MODIFIER = 25;
	protected const float FED_SHIP_DAMAGE_MODIFIER = .5;
	protected const float DCS_DAMAGE_MODIFIER = .75;

	public function __construct(int $numberOfMines) {
		$this->amount = $numberOfMines;
		$this->name = 'Mines';
		$this->shieldDamage = 20;
		$this->armourDamage = 20;
		$this->accuracy = 100;
		$this->damageRollover = false;
	}

	public function getModifiedForceAccuracyAgainstPlayer(Force $forces, Ship $target, bool $minesAreAttacker): float {
		$random = rand(1, 7) * rand(1, 7);
		$modifiedAccuracy = $this->getBaseAccuracy() - $target->getLevel() - $random;
		if ($minesAreAttacker) {
			$modifiedAccuracy /= pow($forces->getSector()->getNumberOfConnections(), 0.6);
		}

		if (self::TOTAL_ENEMY_MINES_MODIFIER > 0) {
			$enemyMines = 0;
			$enemyForces = $forces->getSector()->getEnemyForces($target->getPlayer());
			foreach ($enemyForces as $enemyForce) {
				$enemyMines += $enemyForce->getMines();
			}
			$modifiedAccuracy += $enemyMines / self::TOTAL_ENEMY_MINES_MODIFIER;
		}
		return max(0, min(100, $modifiedAccuracy));
	}

	public function getModifiedDamageAgainstTarget(
		CombatantInterface $shooter,
		CombatantInterface $target,
		bool $minesAreAttacker = false,
	): WeaponDamage {
		if (!$this->canShootTarget($target)) {
			return new WeaponDamage(
				shieldDamage: 0,
				armourDamage: 0,
				damageRollover: $this->isDamageRollover(),
			);
		}
		assert($target instanceof Ship);
		$baseDamage = $this->getDamage();

		// Compute damage modifier
		$mod = 1;
		if ($target->isFederal()) {
			$mod *= self::FED_SHIP_DAMAGE_MODIFIER;
		}
		if ($target->hasDCS()) {
			$mod *= self::DCS_DAMAGE_MODIFIER;
		}

		assert($shooter instanceof Force);
		$launched = ICeil($this->getAmount() * $this->getModifiedForceAccuracyAgainstPlayer($shooter, $target, $minesAreAttacker) / 100);
		return new WeaponDamage(
			shieldDamage: ICeil(IRound($mod * $baseDamage->shieldDamage) * $launched),
			armourDamage: ICeil(IRound($mod * $baseDamage->armourDamage) * $launched),
			damageRollover: $baseDamage->damageRollover,
			launched: $launched,
		);
	}

	/**
	 * @param WeaponShotAtCombatant<Ship> $shot
	 * @return HitWeaponResult<Ship>
	 */
	public function shoot(
		WeaponShotAtCombatant $shot,
		bool $minesAreAttacker = false,
	): HitWeaponResult {
		$shooter = $shot->shooter;
		if (!($shooter instanceof Force)) {
			throw new Exception('Mines should not be used in this context');
		}
		return $this->hitShipTarget($shot, $minesAreAttacker);
	}

	/**
	 * @param WeaponShotAtCombatant<Ship> $shot
	 * @return HitWeaponResult<Ship>
	 */
	private function hitShipTarget(
		WeaponShotAtCombatant $shot,
		bool $minesAreAttacker = false,
	): HitWeaponResult {
		$shooter = $shot->shooter;
		$target = $shot->target;
		$weaponDamage = $this->getModifiedDamageAgainstTarget($shooter, $target, $minesAreAttacker);
		$actualDamage = $target->takeDamageFromMines($weaponDamage);

		// Update the number of mines launched so that we don't detonate more than needed
		$weaponDamage->launched = ICeil($weaponDamage->launched * $actualDamage->totalDamage / $weaponDamage->shieldDamage); // assumes mines do the same shield/armour damage

		// Launched mines are lost
		$this->amount -= $weaponDamage->launched;

		return new HitWeaponResult(
			weapon: $this,
			target: $target,
			weaponDamage: $weaponDamage,
			actualDamage: $actualDamage,
			killResult: $actualDamage->killingShot ? $shot->resolveKill() : null,
		);
	}

}
