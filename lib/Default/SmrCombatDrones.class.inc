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
	
	public function getModifiedAccuracy(AbstractSmrPlayer &$weaponPlayer)
	{
		$modifiedAccuracy = $this->getBaseAccuracy();
		return $modifiedAccuracy;
	}
	
	public function getModifiedAccuracyAgainstPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer)
	{
		return $this->getModifiedAccuracyAgainstPlayerUsingRandom($weaponPlayer,$targetPlayer,mt_rand(3,self::MAX_CDS_RAND));
	}
	
	protected function getModifiedAccuracyAgainstPlayerUsingRandom(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer, $random)
	{
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
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
	
	public function &getModifiedDamage(AbstractSmrPlayer &$weaponPlayer)
	{
		$damage = $this->getDamage();
		//do they have the weapon damage gadget?
		if($weaponPlayer->isGadgetEquipped('Increased Weapon Damage'))
		{
			$damage['MaxDamage'] += $this->getMaxDamage() * INCREASED_DAMAGE_GADGET_FACTOR;
			$damage['Shield'] += $this->getShieldDamage() * INCREASED_DAMAGE_GADGET_FACTOR;
			$damage['Armour'] += $this->getArmourDamage() * INCREASED_DAMAGE_GADGET_FACTOR;
		}
		//increase for weapon damage stat
		$damage['MaxDamage'] += $this->getMaxDamage() * WEAPON_DAMAGE_STAT_FACTOR * $weaponPlayer->getStat('Weapon Damage') / 100;
		$damage['Shield'] += $this->getShieldDamage() * WEAPON_DAMAGE_STAT_FACTOR * $weaponPlayer->getStat('Weapon Damage') / 100;
		$damage['Armour'] += $this->getArmourDamage() * WEAPON_DAMAGE_STAT_FACTOR * $weaponPlayer->getStat('Weapon Damage') / 100;
		return $damage;
	}
	
	public function &getModifiedDamageAgainstPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer)
	{
		if(!$this->canShootTraders()) // If we can't shot traders then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		$damage =& $this->getModifiedDamage($weaponPlayer);
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
	
	public function &shootPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer)
	{
		$return = array('Weapon' => &$this, 'TargetPlayer' => &$targetPlayer, 'Hit' => true);
		
		return $this->doDamageToPlayer($return, $weaponPlayer, $targetPlayer);
	}
}

?>