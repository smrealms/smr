<?php declare(strict_types=1);

namespace Smr\Combat\Weapon;

use Exception;
use Smr\AbstractPlayer;
use Smr\Force;
use Smr\Planet;
use Smr\Port;
use Smr\Sector;

class Mines extends AbstractWeapon {

	use ForcesTrait;

	protected const TOTAL_ENEMY_MINES_MODIFIER = 25;
	protected const FED_SHIP_DAMAGE_MODIFIER = .5;
	protected const DCS_DAMAGE_MODIFIER = .75;

	public function __construct(int $numberOfMines) {
		$this->amount = $numberOfMines;
		$this->name = 'Mines';
		$this->shieldDamage = 20;
		$this->armourDamage = 20;
		$this->accuracy = 100;
		$this->damageRollover = false;
	}

	public function getModifiedAccuracy(): float {
		return $this->getBaseAccuracy();
	}

	public function getModifiedForceAccuracyAgainstPlayer(Force $forces, AbstractPlayer $targetPlayer, bool $minesAreAttacker): float {
		return $this->getModifiedForceAccuracyAgainstPlayerUsingRandom($forces, $targetPlayer, rand(1, 7) * rand(1, 7), $minesAreAttacker);
	}

	protected function getModifiedForceAccuracyAgainstPlayerUsingRandom(Force $forces, AbstractPlayer $targetPlayer, int $random, bool $minesAreAttacker): float {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy -= $targetPlayer->getLevelID() + $random;
		if ($minesAreAttacker) {
			$modifiedAccuracy /= pow(Sector::getSector($forces->getGameID(), $forces->getSectorID())->getNumberOfConnections(), 0.6);
		}

		if (self::TOTAL_ENEMY_MINES_MODIFIER > 0) {
			$enemyMines = 0;
			$enemyForces = $forces->getSector()->getEnemyForces($targetPlayer);
			foreach ($enemyForces as $enemyForce) {
				$enemyMines += $enemyForce->getMines();
			}
			$modifiedAccuracy += $enemyMines / self::TOTAL_ENEMY_MINES_MODIFIER;
		}
		return max(0, min(100, $modifiedAccuracy));
	}

	public function getModifiedDamageAgainstForces(AbstractPlayer $weaponPlayer, Force $forces): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function getModifiedDamageAgainstPort(AbstractPlayer $weaponPlayer, Port $port): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function getModifiedDamageAgainstPlanet(AbstractPlayer $weaponPlayer, Planet $planet): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function getModifiedDamageAgainstPlayer(AbstractPlayer $weaponPlayer, AbstractPlayer $targetPlayer): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function getModifiedPortDamageAgainstPlayer(Port $port, AbstractPlayer $targetPlayer): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function getModifiedPlanetDamageAgainstPlayer(Planet $planet, AbstractPlayer $targetPlayer): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function getModifiedForceDamageAgainstPlayer(Force $forces, AbstractPlayer $targetPlayer, bool $minesAreAttacker = false): array {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
			return ['Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
		}
		$damage = $this->getDamage();
		if ($targetPlayer->getShip()->isFederal()) { // do less damage to fed ships
			$damage['Shield'] = IRound($damage['Shield'] * self::FED_SHIP_DAMAGE_MODIFIER);
			$damage['Armour'] = IRound($damage['Armour'] * self::FED_SHIP_DAMAGE_MODIFIER);
		}

		if ($targetPlayer->getShip()->hasDCS()) { // do less damage to DCS (Drone Scrambler)
			$damage['Shield'] = IRound($damage['Shield'] * self::DCS_DAMAGE_MODIFIER);
			$damage['Armour'] = IRound($damage['Armour'] * self::DCS_DAMAGE_MODIFIER);
		}
		$damage['Launched'] = ICeil($this->getAmount() * $this->getModifiedForceAccuracyAgainstPlayer($forces, $targetPlayer, $minesAreAttacker) / 100);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour']);
		return $damage;
	}

	public function shootForces(AbstractPlayer $weaponPlayer, Force $forces): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function shootPlayer(AbstractPlayer $weaponPlayer, AbstractPlayer $targetPlayer): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function shootPlayerAsForce(Force $forces, AbstractPlayer $targetPlayer, bool $minesAreAttacker = false): array {
		$return = ['Weapon' => $this, 'Target' => $targetPlayer, 'Hit' => true];
		return $this->doForceDamageToPlayer($return, $forces, $targetPlayer, $minesAreAttacker);
	}

	protected function doForceDamageToPlayer(array $return, Force $forces, AbstractPlayer $targetPlayer, bool $minesAreAttacker = false): array {
		$return['WeaponDamage'] = $this->getModifiedForceDamageAgainstPlayer($forces, $targetPlayer, $minesAreAttacker);
		$return['ActualDamage'] = $targetPlayer->getShip()->takeDamageFromMines($return['WeaponDamage']);

		// Update the number of mines launched so that we don't detonate more than needed
		if (!isset($return['WeaponDamage']['Launched'])) {
			throw new Exception('Mines must report the number launched');
		}
		$return['WeaponDamage']['Launched'] = ICeil($return['WeaponDamage']['Launched'] * $return['ActualDamage']['TotalDamage'] / $return['WeaponDamage']['Shield']); // assumes mines do the same shield/armour damage

		// Launched mines are lost
		$this->amount -= $return['WeaponDamage']['Launched'];

		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $targetPlayer->killPlayerByForces($forces);
		}
		return $return;
	}

}
