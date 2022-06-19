<?php declare(strict_types=1);

class SmrScoutDrones extends AbstractSmrCombatWeapon {

	use Traits\CombatWeaponForce;

	public function __construct(int $numberOfSDs) {
		$this->amount = $numberOfSDs;
		$this->name = 'Scout Drones';
		$this->shieldDamage = 20;
		$this->armourDamage = 20;
		$this->accuracy = 100;
		$this->damageRollover = false;
	}

	public function getModifiedAccuracy(): float {
		$modifiedAccuracy = $this->getBaseAccuracy();
		return $modifiedAccuracy;
	}

	public function getModifiedForceAccuracyAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer): float {
		return $this->getModifiedForceAccuracyAgainstPlayerUsingRandom($forces, $targetPlayer, rand(1, 7) * rand(1, 7));
	}

	protected function getModifiedForceAccuracyAgainstPlayerUsingRandom(SmrForce $forces, AbstractSmrPlayer $targetPlayer, int $random): float {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy -= $targetPlayer->getLevelID() + $random;

		return max(0, min(100, $modifiedAccuracy));
	}

	public function getModifiedDamageAgainstForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function getModifiedDamageAgainstPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function getModifiedDamageAgainstPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function getModifiedDamageAgainstPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function getModifiedPortDamageAgainstPlayer(SmrPort $port, AbstractSmrPlayer $targetPlayer): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function getModifiedPlanetDamageAgainstPlayer(SmrPlanet $planet, AbstractSmrPlayer $targetPlayer): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function getModifiedForceDamageAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer): array {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
			$return = ['Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover()];
			return $return;
		}
		$damage = $this->getDamage();
		$damage['Launched'] = ICeil($this->getAmount() * $this->getModifiedForceAccuracyAgainstPlayer($forces, $targetPlayer) / 100);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour']);
		return $damage;
	}

	public function shootForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function shootPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function shootPlayerAsForce(SmrForce $forces, AbstractSmrPlayer $targetPlayer): array {
		$return = ['Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => true];
		$return = $this->doForceDamageToPlayer($return, $forces, $targetPlayer);
		$forces->takeSDs($return['WeaponDamage']['Launched']);
		return $return;
	}

}
