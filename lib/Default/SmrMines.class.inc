<?php
require_once('AbstractSmrCombatWeapon.class.inc');
class SmrMines extends AbstractSmrCombatWeapon {
	const TOTAL_ENEMY_MINES_MODIFIER = 25;
	const FED_SHIP_DAMAGE_MODIFIER = .5;
	const DCS_DAMAGE_MODIFIER = .75;
	protected $numberOfMines;
	
	public function __construct($gameTypeID,$numberOfMines) {
		$this->gameTypeID = $gameTypeID;
		$this->numberOfMines = $numberOfMines;
		$this->name = 'Mines';
		$this->raceID = 0;
		$this->maxDamage = 20;
		$this->shieldDamage = 20;
		$this->armourDamage = 20;
		$this->accuracy = 100;
		$this->damageRollover = false;
		$this->raidWeapon = false;
	}
	
	public function getNumberOfMines() {
		return $this->numberOfMines;
	}
	
	public function getModifiedAccuracy() {
		$modifiedAccuracy = $this->getBaseAccuracy();
		return $modifiedAccuracy;
	}
	
	public function getModifiedForceAccuracyAgainstPlayer(SmrForce $forces,AbstractSmrPlayer $targetPlayer,$minesAreAttacker) {
		return $this->getModifiedForceAccuracyAgainstPlayerUsingRandom($forces,$targetPlayer,mt_rand(1,7) * mt_rand(1,7),$minesAreAttacker);
	}
	
	protected function getModifiedForceAccuracyAgainstPlayerUsingRandom(SmrForce $forces,AbstractSmrPlayer $targetPlayer, $random,$minesAreAttacker) {
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy -= $targetPlayer->getLevelID() + $random;
		if($minesAreAttacker) {
			$modifiedAccuracy /= pow(SmrSector::getSector($forces->getGameID(),$forces->getSectorID())->getNumberOfConnections(), 0.6);
		}
	
		if(self::TOTAL_ENEMY_MINES_MODIFIER > 0) {
			$enemyMines = 0;
			$enemyForces = $forces->getSector()->getEnemyForces($targetPlayer);
			foreach($enemyForces as $enemyForce) {
				$enemyMines += $enemyForce->getMines();
			}
			$modifiedAccuracy += $enemyMines / self::TOTAL_ENEMY_MINES_MODIFIER;
		}
		return max(0,min(100,$modifiedAccuracy));
	}
	
	public function getMaxModifiedForceAccuracyAgainstPlayer(SmrForce $forces,AbstractSmrPlayer $targetPlayer,$minesAreAttacker) {
		return $this->getModifiedForceAccuracyAgainstPlayerUsingRandom($forces,$targetPlayer,49,$minesAreAttacker);
	}
	
	public function &getModifiedDamage() {
		$damage = $this->getDamage();
		return $damage;
	}
	
	public function &getModifiedDamageAgainstForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces) {
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}
	
	public function &getModifiedDamageAgainstPort(AbstractSmrPlayer $weaponPlayer, SmrPort $port) {
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}
	
	public function &getModifiedDamageAgainstPlanet(AbstractSmrPlayer $weaponPlayer, SmrPlanet $planet) {
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}
	
	public function &getModifiedDamageAgainstPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) {
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}
	
	public function &getModifiedPortDamageAgainstPlayer(SmrPort $port, AbstractSmrPlayer $targetPlayer) {
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}
	
	public function &getModifiedPlanetDamageAgainstPlayer(SmrPlanet $planet, AbstractSmrPlayer $targetPlayer) {
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}
	
	public function &getModifiedForceDamageAgainstPlayer(SmrForce $forces, AbstractSmrPlayer $targetPlayer,$minesAreAttacker=false) {
		if(!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
			$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
			return $return;
		}
		$damage =& $this->getModifiedDamage();
		if($targetPlayer->getShip()->isFederal()) { // do less damage to fed ships 
			$damage['MaxDamage'] = round($damage['MaxDamage'] * self::FED_SHIP_DAMAGE_MODIFIER);
			$damage['Shield'] = round($damage['Shield'] * self::FED_SHIP_DAMAGE_MODIFIER);
			$damage['Armour'] = round($damage['Armour'] * self::FED_SHIP_DAMAGE_MODIFIER);
		} 
		
		if ($targetPlayer->getShip()->hasDCS()) { // do less damage to DCS (Drone Scrambler) 
			$damage['MaxDamage'] = round($damage['MaxDamage'] * self::DCS_DAMAGE_MODIFIER);
			$damage['Shield'] = round($damage['Shield'] * self::DCS_DAMAGE_MODIFIER);
			$damage['Armour'] = round($damage['Armour'] * self::DCS_DAMAGE_MODIFIER);		
		}
		$damage['Launched'] = ceil($this->getNumberOfMines() * $this->getModifiedForceAccuracyAgainstPlayer($forces,$targetPlayer,$minesAreAttacker) / 100);
		$damage['MaxDamage'] = ceil($damage['Launched'] * $damage['MaxDamage']);
		$damage['Shield'] = ceil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ceil($damage['Launched'] * $damage['Armour']);
		return $damage;
	}
	
	public function &shootForces(AbstractSmrPlayer $weaponPlayer, SmrForce $forces) {
		$return = array('Weapon' => $this, 'TargetForces' => $forces);
		return $return;
	}
	
	public function &shootPlayer(AbstractSmrPlayer $weaponPlayer, AbstractSmrPlayer $targetPlayer) {
		$return = array('Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => false);
		return $return;
	}
	
	public function &shootPlayerAsForce(SmrForce $forces, AbstractSmrPlayer $targetPlayer,$minesAreAttacker=false) {
		$return = array('Weapon' => $this, 'TargetPlayer' => $targetPlayer, 'Hit' => true);
		$this->doForceDamageToPlayer($return, $forces, $targetPlayer, $minesAreAttacker);
		$forces->takeMines($return['ActualDamage']['Launched']);
		return $return;
	}
	
	protected function &doForceDamageToPlayer(array &$return, SmrForce $forces, AbstractSmrPlayer $targetPlayer,$minesAreAttacker=false) {
		$return['WeaponDamage'] =& $this->getModifiedForceDamageAgainstPlayer($forces,$targetPlayer,$minesAreAttacker);
		$return['ActualDamage'] =& $targetPlayer->getShip()->doMinesDamage($return['WeaponDamage']);
		$return['ActualDamage']['Launched'] = ceil($return['WeaponDamage']['Launched'] * $return['ActualDamage']['TotalDamage'] / $return['WeaponDamage']['MaxDamage']);

		if($return['ActualDamage']['KillingShot']) {
			$return['KillResults'] =& $targetPlayer->killPlayerByForces($forces);
		}
		return $return;
	}
}

?>
