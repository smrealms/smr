<?php declare(strict_types=1);

namespace Smr\Combat\Weapon;

use Exception;
use Smr\AbstractPlayer;
use Smr\Force;
use Smr\Planet;
use Smr\Port;

class ScoutDrones extends AbstractWeapon {

	use ForcesTrait;

	public function __construct(int $numberOfSDs) {
		$this->amount = $numberOfSDs;
		$this->name = 'Scout Drones';
		$this->shieldDamage = 20;
		$this->armourDamage = 20;
		$this->accuracy = 100;
		$this->damageRollover = false;
	}

	public function getModifiedAccuracy(): float {
		return $this->getBaseAccuracy();
	}

	public function getModifiedForceAccuracyAgainstPlayer(Force $forces, AbstractPlayer $targetPlayer): float {
		return $this->getModifiedForceAccuracyAgainstPlayerUsingRandom($forces, $targetPlayer, rand(1, 7) * rand(1, 7));
	}

	protected function getModifiedForceAccuracyAgainstPlayerUsingRandom(Force $forces, AbstractPlayer $targetPlayer, int $random): float {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy -= $targetPlayer->getLevelID() + $random;

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

	public function getModifiedForceDamageAgainstPlayer(Force $forces, AbstractPlayer $targetPlayer): array {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
			return ['Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
		}
		$damage = $this->getDamage();
		$damage['Launched'] = ICeil($this->getAmount() * $this->getModifiedForceAccuracyAgainstPlayer($forces, $targetPlayer) / 100);
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

	public function shootPlayerAsForce(Force $forces, AbstractPlayer $targetPlayer): array {
		$return = ['Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => true];
		$return = $this->doForceDamageToPlayer($return, $forces, $targetPlayer);
		if (!isset($return['WeaponDamage']['Launched'])) {
			throw new Exception('ScoutDrones must report the number launched');
		}
		$this->amount -= $return['WeaponDamage']['Launched']; // kamikaze
		return $return;
	}

}
