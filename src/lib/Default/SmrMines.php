<?php declare(strict_types=1);

class SmrMines extends AbstractSmrCombatWeapon {

	use Traits\CombatWeaponForce;

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

	public function getModifiedForceAccuracyAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer, bool $minesAreAttacker): float {
		return $this->getModifiedForceAccuracyAgainstPlayerUsingRandom($forces, $targetPlayer, rand(1, 7) * rand(1, 7), $minesAreAttacker);
	}

	protected function getModifiedForceAccuracyAgainstPlayerUsingRandom(SmrForce $forces, AbstractSmrPlayer $targetPlayer, int $random, bool $minesAreAttacker): float {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy -= $targetPlayer->getLevelID() + $random;
		if ($minesAreAttacker) {
			$modifiedAccuracy /= pow(SmrSector::getSector($forces->getGameID(), $forces->getSectorID())->getNumberOfConnections(), 0.6);
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

	public function getModifiedPortDamageAgainstPlayer(AbstractSmrPort $port, AbstractSmrPlayer $targetPlayer): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function getModifiedPlanetDamageAgainstPlayer(SmrPlanet $planet, AbstractSmrPlayer $targetPlayer): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function getModifiedForceDamageAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer, bool $minesAreAttacker = false): array {
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

	public function shootForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function shootPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer): never {
		throw new Exception('This weapon should not be used in this context');
	}

	public function shootPlayerAsForce(SmrForce $forces, AbstractSmrPlayer $targetPlayer, bool $minesAreAttacker = false): array {
		$return = ['Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => true];
		$return = $this->doForceDamageToPlayer($return, $forces, $targetPlayer, $minesAreAttacker);
		$this->amount -= $return['ActualDamage']['Launched']; // kamikaze
		return $return;
	}

	protected function doForceDamageToPlayer(array $return, SmrForce $forces, AbstractSmrPlayer $targetPlayer, bool $minesAreAttacker = false): array {
		$return['WeaponDamage'] = $this->getModifiedForceDamageAgainstPlayer($forces, $targetPlayer, $minesAreAttacker);
		$return['ActualDamage'] = $targetPlayer->getShip()->takeDamageFromMines($return['WeaponDamage']);

		// Update the number of mines launched so that we don't detonate more than needed
		if (!isset($return['WeaponDamage']['Launched'])) {
			throw new Exception('Mines must report the number launched');
		}
		$return['ActualDamage']['Launched'] = ICeil($return['WeaponDamage']['Launched'] * $return['ActualDamage']['TotalDamage'] / $return['WeaponDamage']['Shield']); // assumes mines do the same shield/armour damage

		if ($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] = $targetPlayer->killPlayerByForces($forces);
		}
		return $return;
	}

}
