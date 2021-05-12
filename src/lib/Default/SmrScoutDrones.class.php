<?php declare(strict_types=1);

class SmrScoutDrones extends AbstractSmrCombatWeapon {
	protected $numberOfSDs;

	public function __construct($numberOfSDs) {
		$this->numberOfSDs = $numberOfSDs;
		$this->name = 'Scout Drones';
		$this->maxDamage = 20;
		$this->shieldDamage = 20;
		$this->armourDamage = 20;
		$this->accuracy = 100;
		$this->damageRollover = false;
	}

	public function getNumberOfSDs() {
		return $this->numberOfSDs;
	}

	public function getModifiedAccuracy() {
		$modifiedAccuracy = $this->getBaseAccuracy();
		return $modifiedAccuracy;
	}

	public function getModifiedForceAccuracyAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer) {
		return $this->getModifiedForceAccuracyAgainstPlayerUsingRandom($forces, $targetPlayer, rand(1, 7) * rand(1, 7));
	}

	protected function getModifiedForceAccuracyAgainstPlayerUsingRandom(SmrForce $forces, AbstractSmrPlayer $targetPlayer, $random) {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy -= $targetPlayer->getLevelID() + $random;

		return max(0, min(100, $modifiedAccuracy));
	}

	public function getModifiedDamageAgainstForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces) {
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}

	public function getModifiedDamageAgainstPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port) {
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}

	public function getModifiedDamageAgainstPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet) {
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}

	public function getModifiedDamageAgainstPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) {
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}

	public function getModifiedPortDamageAgainstPlayer(SmrPort $port, AbstractSmrPlayer $targetPlayer) {
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}

	public function getModifiedPlanetDamageAgainstPlayer(SmrPlanet $planet, AbstractSmrPlayer $targetPlayer) {
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}

	public function getModifiedForceDamageAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer) {
		if (!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
			$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
			return $return;
		}
		$damage = $this->getDamage();
		$damage['Launched'] = ICeil($this->getNumberOfSDs() * $this->getModifiedForceAccuracyAgainstPlayer($forces, $targetPlayer) / 100);
		$damage['MaxDamage'] = ICeil($damage['Launched'] * $damage['MaxDamage']);
		$damage['Shield'] = ICeil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ICeil($damage['Launched'] * $damage['Armour']);
		return $damage;
	}

	public function shootForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces) {
		$return = array('Weapon' => $this, 'TargetForces' => $forces);
		return $return;
	}

	public function shootPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) {
		$return = array('Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => false);
		return $return;
	}

	public function shootPlayerAsForce(SmrForce $forces, AbstractSmrPlayer $targetPlayer) {
		$return = array('Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => true);
		$return = $this->doForceDamageToPlayer($return, $forces, $targetPlayer);
		$forces->takeSDs($return['WeaponDamage']['Launched']);
		return $return;
	}
}
