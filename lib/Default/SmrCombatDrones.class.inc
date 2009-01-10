<?
require_once('AbstractSmrCombatWeapon.class.inc');
require_once('SmrMySqlDatabase.class.inc');

require_once('AbstractSmrPlayer.class.inc');
class SmrCombatDrones extends AbstractSmrCombatWeapon
{
	const MAX_CDS_RAND = 54;
	protected $numberOfCDs;
	
	public function __construct($gameTypeID,$numberOfCDs)
	{
		$this->gameTypeID = $gameTypeID;
		$this->numberOfCDs = $numberOfCDs;
		$this->name = 'Combat Drones';
		$this->raceID = 0;
		$this->maxDamage = 2;
		$this->shieldDamage = 2;
		$this->armourDamage = 2;
		$this->accuracy = 3;
		$this->damageRollover = true;
		$this->raidWeapon = false;
	}
	
	public function getNumberOfCDs()
	{
		return $this->numberOfCDs;
	}
	
	public function getModifiedAccuracy()
	{
		$modifiedAccuracy = $this->getBaseAccuracy();
		return $modifiedAccuracy;
	}
	
	protected function getModifiedAccuracyAgainstForcesUsingRandom(AbstractSmrPlayer &$forces,SmrForce &$forces, $random)
	{
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy += $random;
	
		return max(0,min(100,$modifiedAccuracy));
	}
	public function getMaxModifiedAccuracyAgainstForces(AbstractSmrPlayer &$weaponPlayer, SmrForce &$forces)
	{
		return $this->getModifiedAccuracyAgainstForcesUsingRandom($weaponPlayer,$forces,self::MAX_CDS_RAND);
	}
	public function getModifiedAccuracyAgainstForces(AbstractSmrPlayer &$weaponPlayer, SmrForce &$forces)
	{
		return $this->getModifiedAccuracyAgainstForcesUsingRandom($weaponPlayer,$forces,mt_rand(3,self::MAX_CDS_RAND));
	}
	
	
	public function getModifiedAccuracyAgainstPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer)
	{
		return $this->getModifiedAccuracyAgainstPlayerUsingRandom($weaponPlayer,$targetPlayer,mt_rand(3,self::MAX_CDS_RAND));
	}
	
	protected function getModifiedAccuracyAgainstPlayerUsingRandom(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer, $random)
	{
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy += ($random + mt_rand($weaponPlayer->getLevelID() / 2,$weaponPlayer->getLevelID()) - ($targetPlayer->getLevelID() - $weaponPlayer->getLevelID())/3)/1.5;

		$weaponShip =& $weaponPlayer->getShip();
		$targetShip =& $targetPlayer->getShip();
		$mrDiff = $targetShip->getMR() - $weaponShip->getMR();
		if($mrDiff > 0)
			$modifiedAccuracy -= $this->accuracy * ($mrDiff/MR_FACTOR)/100;
	
		return max(0,min(100,$modifiedAccuracy));
	}
	
	public function getMaxModifiedAccuracyAgainstPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer)
	{
		return $this->getModifiedAccuracyAgainstPlayerUsingRandom($weaponPlayer,$targetPlayer,self::MAX_CDS_RAND);
	}
	
	public function getModifiedForceAccuracyAgainstPlayer(SmrForce &$forces,AbstractSmrPlayer &$targetPlayer)
	{
		return $this->getModifiedForceAccuracyAgainstPlayerUsingRandom($forces,$targetPlayer,mt_rand(3,self::MAX_CDS_RAND));
	}
	
	protected function getModifiedForceAccuracyAgainstPlayerUsingRandom(SmrForce &$forces,AbstractSmrPlayer &$targetPlayer, $random)
	{
		$modifiedAccuracy = $this->getModifiedAccuracy();
		$modifiedAccuracy += $random;
	
		return max(0,min(100,$modifiedAccuracy));
	}
	
	public function getMaxModifiedForceAccuracyAgainstPlayer(SmrForce &$forces,AbstractSmrPlayer &$targetPlayer)
	{
		return $this->getModifiedForceAccuracyAgainstPlayerUsingRandom($forces,$targetPlayer,self::MAX_CDS_RAND);
	}
	
	public function &getModifiedDamage()
	{
		$damage = $this->getDamage();
		return $damage;
	}
	
	public function &getModifiedDamageAgainstForces(AbstractSmrPlayer &$weaponPlayer, SmrForce &$forces)
	{
		if(!$this->canShootForces()) // If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		$damage =& $this->getModifiedDamage();
		$damage['Launched'] = ceil($this->getNumberOfCDs() * $this->getModifiedAccuracyAgainstForces($weaponPlayer,$forces) / 100);
		$damage['Kamikaze'] = 0;
		if($weaponPlayer->isCombatDronesKamikazeOnMines()) // If kamikaze then damage is same as MINE_ARMOUR
		{
			$damage['Kamikaze'] = min($damage['Launched'],$forces->getMines());
			$damage['Launched'] -= $damage['Kamikaze'];
		}
		$damage['MaxDamage'] = ceil($damage['Launched'] * $damage['MaxDamage']);
		$damage['Shield'] = ceil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ceil($damage['Launched'] * $damage['Armour']);
		
		$diminishing = $weaponPlayer->getDiminishingForceAttack();
		$damage['MaxDamage'] = ceil($diminishing * $damage['MaxDamage']);
		$damage['Shield'] = ceil($diminishing * $damage['Shield']);
		$damage['Armour'] = ceil($diminishing * $damage['Armour']);
		
		$damage['MaxDamage'] += $damage['Kamikaze'] * MINE_ARMOUR;
		$damage['Shield'] += $damage['Kamikaze'] * MINE_ARMOUR;
		$damage['Armour'] += $damage['Kamikaze'] * MINE_ARMOUR;
			
		return $damage;
	}
	
	public function &getModifiedDamageAgainstPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer)
	{
		if(!$this->canShootTraders()) // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
		{
			$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
			return $return;
		}
		$damage =& $this->getModifiedDamage();
		$diminishing = $weaponPlayer->getDiminishingTraderAttack();
		$damage['MaxDamage'] = ceil($diminishing * $damage['MaxDamage']);
		$damage['Shield'] = ceil($diminishing * $damage['Shield']);
		$damage['Armour'] = ceil($diminishing * $damage['Armour']);
		$damage['Launched'] = ceil($this->getNumberOfCDs() * $this->getModifiedAccuracyAgainstPlayer($weaponPlayer,$targetPlayer) / 100);
		$damage['MaxDamage'] = ceil($damage['Launched'] * $damage['MaxDamage']);
		$damage['Shield'] = ceil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ceil($damage['Launched'] * $damage['Armour']);
		return $damage;
	}
	
	public function &getModifiedForceDamageAgainstPlayer(SmrForce &$forces, AbstractSmrPlayer &$targetPlayer)
	{
		if(!$this->canShootTraders()) // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
		{
			$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
			return $return;
		}
		$damage =& $this->getModifiedDamage();
		$damage['Launched'] = ceil($this->getNumberOfCDs() * $this->getModifiedForceAccuracyAgainstPlayer($forces,$targetPlayer) / 100);
		$damage['MaxDamage'] = ceil($damage['Launched'] * $damage['MaxDamage']);
		$damage['Shield'] = ceil($damage['Launched'] * $damage['Shield']);
		$damage['Armour'] = ceil($damage['Launched'] * $damage['Armour']);
		return $damage;
	}
	
	public function &shootForces(AbstractSmrPlayer &$weaponPlayer, SmrForce &$forces)
	{
		$return = array('Weapon' => &$this, 'TargetForces' => &$forces, 'Hit' => true);
		$this->doPlayerDamageToForce($return,$weaponPlayer, $forces);
		if($return['WeaponDamage']['Kamikaze'] > 0)
		{
			$weaponPlayer->decreaseCDs($return['WeaponDamage']['Kamikaze']);
		}
		return $return;
	}
	
	public function &shootPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer)
	{
		$return = array('Weapon' => &$this, 'TargetPlayer' => &$targetPlayer, 'Hit' => true);
		return $this->doPlayerDamageToPlayer($return, $weaponPlayer, $targetPlayer);
	}
	
	public function &shootPlayerAsForce(SmrForce &$forces, AbstractSmrPlayer &$targetPlayer)
	{
		$return = array('Weapon' => &$this, 'TargetPlayer' => &$targetPlayer, 'Hit' => true);
		return $this->doForceDamageToPlayer($return, $targetPlayer);
	}
}

?>