<?
require_once('AbstractSmrCombatWeapon.class.inc');
require_once('SmrMySqlDatabase.class.inc');

class SmrWeapon extends AbstractSmrCombatWeapon
{
	protected static $CACHE_WEAPONS = array();
	
	protected static $db;
	
	protected $weaponTypeID;
	protected $cost;
	protected $powerLevel;
	protected $buyerRestriction;

	
	protected static function initialiseDatabase()
	{
		if(self::$db==null)
			self::$db = new SmrMySqlDatabase();
	}

	public static function &getWeapon($gameTypeID,$weaponTypeID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_WEAPONS[$gameTypeID . ':' . $weaponTypeID]))
		{
			$w = new SmrWeapon($gameTypeID,$weaponTypeID);
			if($w->getName()!='')
				self::$CACHE_WEAPONS[$gameTypeID . ':' . $weaponTypeID] =& $w;
			else
				self::$CACHE_WEAPONS[$gameTypeID . ':' . $weaponTypeID] = false;
		}
		return self::$CACHE_WEAPONS[$gameTypeID . ':' . $weaponTypeID];
	}

	public static function &getAllWeapons($gameTypeID,$forceUpdate = false)
	{
		$db = new SmrMySqlDatabase();
		$db->query('SELECT weapon_type_id FROM weapon_type');
		$weapons=array();
		while($db->nextRecord())
		{
			$weapons[] =& self::getWeapon($gameTypeID,$db->getField('weapon_type_id'),$forceUpdate);
		}
		return $weapons;
	}
	
	protected function __construct($gameTypeID,$weaponTypeID)
	{
		self::initialiseDatabase();
		
		$this->gameTypeID = $gameTypeID;
		$this->weaponTypeID = $weaponTypeID;
			
		self::$db->query('SELECT weapon_name,race_id,cost,shield_damage,armor_damage,accuracy,power_level,buyer_restriction FROM weapon_type WHERE weapon_type_id = ' . $weaponTypeID . ' LIMIT 1');
		
		if(self::$db->nextRecord())
		{
			$this->name = self::$db->getField('weapon_name');
			$this->raceID = (int)self::$db->getField('race_id');
			$this->cost = (int)self::$db->getField('cost');
			$this->shieldDamage = (int)self::$db->getField('shield_damage');
			$this->armourDamage = (int)self::$db->getField('armor_damage');
			$this->accuracy = (int)self::$db->getField('accuracy');
			$this->powerLevel = (int)self::$db->getField('power_level');
			$this->buyerRestriction = (int)self::$db->getField('buyer_restriction');
			$this->damageRollover = false;
			$this->raidWeapon = false;
			$this->maxDamage = max($this->shieldDamage,$this->armourDamage);
		}
	}
	
	public function getWeaponTypeID()
	{
		return $this->weaponTypeID;
	}
	
	public function getCost()
	{
		return $this->cost;
	}
	
	public function getPowerLevel()
	{
		return $this->powerLevel;
	}
	
	public function getModifiedAccuracy(AbstractSmrPlayer &$weaponPlayer)
	{
		$modifiedAccuracy = $this->getBaseAccuracy();
		$modifiedAccuracy += $this->getBaseAccuracy() * ($weaponPlayer->getStat('Accuracy') * ACCURACY_STAT_FACTOR)/100;
		$modifiedAccuracy += $this->getBaseAccuracy() * ($weaponPlayer->getLevelID()*$weaponPlayer->getLevelID()*.008+.219*$weaponPlayer->getLevelID()+1.1694) / 2;
		if ($weaponPlayer->isGadgetEquipped('Increased Accuracy'))
			$modifiedAccuracy += $this->getBaseAccuracy() * INCREASED_ACC_GADGET_FACTOR/100;
		return $modifiedAccuracy;
	}
	
	public function getModifiedAccuracyAgainstForces(AbstractSmrPlayer &$weaponPlayer, SmrForce &$forces)
	{
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
		return $modifiedAccuracy;
	}
	
	public function getModifiedAccuracyAgainstPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer)
	{
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
		$modifiedAccuracy -= $this->getBaseAccuracy() * ($targetPlayer->getLevelID()*$targetPlayer->getLevelID()*.008+.219*$targetPlayer->getLevelID()+1.1694) / 2;
		if ($targetPlayer->isGadgetEquipped('Increased Maneuverability'))
			$modifiedAccuracy -= $this->getBaseAccuracy() * INCREASED_MAN_GADGET_FACTOR/100;
		
		$weaponShip =& $weaponPlayer->getShip();
		$targetShip =& $targetPlayer->getShip();
		$mrDiff = $targetShip->getMR() - $weaponShip->getMR();
		if($mrDiff > 0)
			$modifiedAccuracy -= $this->getBaseAccuracy() * ($mrDiff/MR_FACTOR)/100;
	
		return $modifiedAccuracy;
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
	
	public function &getModifiedDamageAgainstForces(AbstractSmrPlayer &$weaponPlayer, SmrForce &$forces)
	{
		if(!$this->canShootForces()) // If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		$damage =& $this->getModifiedDamage($weaponPlayer);
		
		$diminishing = $weaponPlayer->getDiminishingForceAttack();
		$damage['MaxDamage'] = ceil($diminishing * $damage['MaxDamage']);
		$damage['Shield'] = ceil($diminishing * $damage['Shield']);
		$damage['Armour'] = ceil($diminishing * $damage['Armour']);
		return $damage;
	}
	
	public function &getModifiedForceDamageAgainstPlayer(SmrForce &$forces, AbstractSmrPlayer &$targetPlayer)
	{
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}
	
	public function &getModifiedDamageAgainstPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer)
	{
		if(!$this->canShootTraders()) // If we can't shoot traders then just return a damageless array and don't waste resources calculated any damage mods.
		{
			$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
			return $return;
		}
		$damage =& $this->getModifiedDamage($weaponPlayer);
		
		$diminishing = $weaponPlayer->getDiminishingTraderAttack();
		$damage['MaxDamage'] = ceil($diminishing * $damage['MaxDamage']);
		$damage['Shield'] = ceil($diminishing * $damage['Shield']);
		$damage['Armour'] = ceil($diminishing * $damage['Armour']);
		return $damage;
	}
	
	public function &shootForces(AbstractSmrPlayer &$weaponPlayer, SmrForce &$forces)
	{
		$return = array('Weapon' => &$this, 'TargetForces' => &$forces);
		if(mt_rand(0,99)>=$this->getModifiedAccuracyAgainstForces($weaponPlayer,$forces)) //Check if we miss
		{
			$return['Hit'] = false;
			return $return;
		}
		$return['Hit'] = true;
		return $this->doPlayerDamageToForce($return,$weaponPlayer, $forces);
	}
	
	public function &shootPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer)
	{
		$return = array('Weapon' => &$this, 'TargetPlayer' => &$targetPlayer);
		if(mt_rand(0,99)>=$this->getModifiedAccuracyAgainstPlayer($weaponPlayer,$targetPlayer)) //Check if we miss
		{
			$return['Hit'] = false;
			return $return;
		}
		$return['Hit'] = true;
		return $this->doPlayerDamageToPlayer($return,$weaponPlayer, $targetPlayer);
	}
	
	public function &shootPlayerAsForce(SmrForce &$forces, AbstractSmrPlayer &$targetPlayer)
	{
		$return = array('Weapon' => &$this, 'TargetPlayer' => &$targetPlayer, 'Hit' => false);
		return $return;
	}
	
//private function get_weapon($weaponPlayer,$weaponShip, $weapon_id, $purpose = 'Normal',$targetPlayer=null,$targetShip=null)
//{
//	//this will be used in the future to display more accurate info for the person using the weapon
//	global $TIME,$GAME_ID, $WEAPONS, $ACCURACY_MOD, $WEAPON_DAMAGE_MOD, $BOMBARDMENT_MOD, $GADGETS, $INCREASED_ACC, $INCREASED_MAN, $INCREASED_DAMAGE_GADGET_VALUE, $WEAPON_RESTRICTIONS,$INCREASED_PLANET_DAMAGE_GADGET_VALUE;
//	
//	//get the weapon restrictions
//	$res_rev = array();
//	foreach ($WEAPON_RESTRICTIONS as $res_id => $res_name) $res_rev[$res_name] = $res_id;
//	
//	//get base weapon
//	$this_weapon = $WEAPONS[$weapon_id];
//	
//	//check for special restrictions/abilities
//	if (in_array($res_rev['Raiding Damage Only'],$this_weapon['Restriction'])) $this_weapon['Raid Only'] = 'TRUE';
//	else $this_weapon['Raid Only'] = 'FALSE';
//	if (in_array($res_rev['May Damage Fleet'],$this_weapon['Restriction'])) $this_weapon['May Damage Fleet'] = 'TRUE';
//	else $this_weapon['May Damage Fleet'] = 'FALSE';
//	
//	//Determine New Damage
//	//do they have the raid damage gadget and are raiding?
//	if (gadget_equipped($weaponPlayer,'Weapon Accelerator') && ($purpose == 'PortRaid' || $purpose == 'PlanetRaid'))
//	{
//		$this_weapon['Shield Damage'] *= $INCREASED_PLANET_DAMAGE_GADGET_VALUE;
//		$this_weapon['Armor Damage'] *= $INCREASED_PLANET_DAMAGE_GADGET_VALUE;
//	}
//	//increase for raid damage stat if they are raiding
//	if ($purpose == 'PortRaid' || $purpose == 'PlanetRaid')
//	{
//		$this_weapon['Shield Damage'] *= 1 + ($BOMBARDMENT_MOD * $weaponPlayer->getStat('Bombardment')) / 100;
//		$this_weapon['Armor Damage'] *= 1 + ($BOMBARDMENT_MOD * $weaponPlayer->getStat('Bombardment')) / 100;
//	}
//	//get diminishing returns for attacking
//	$diminishing=1;
//	if($purpose == 'PlanetRaid')
//	{
//		global $REPAIR_BEFORE_DIMINISHING_PLANET_RAID;
//		$diminishing = 1 - ($weaponPlayer->getPastMaint() / $REPAIR_BEFORE_DIMINISHING_PLANET_RAID);
//	}
//	else if($purpose == 'PortRaid')
//	{
//		global $REPAIR_BEFORE_DIMINISHING_PORT_RAID;
//		$diminishing = 1 - ($weaponPlayer->getPastMaint() / $REPAIR_BEFORE_DIMINISHING_PORT_RAID);
//	}
//	else if($purpose == 'Forces')
//	{
//		global $REPAIR_BEFORE_DIMINISHING_FORCE_ATTACK;
//		$diminishing = 1 - ($weaponPlayer->getPastMaint() / $REPAIR_BEFORE_DIMINISHING_FORCE_ATTACK);
//	}
//	else if($purpose == 'Normal')
//	{
//		$REPAIR_BEFORE_DIMINISHING_ATTACK = $weaponShip['Speed'] * 25 + 475;
//		$diminishing = 1 - ($weaponPlayer->getPastMaint() / $REPAIR_BEFORE_DIMINISHING_ATTACK);
//	}
//	if ($diminishing <= 0) $diminishing = 0.00001;
//	$this_weapon['Shield Damage'] = ceil($diminishing * $this_weapon['Shield Damage']);
//	$this_weapon['Armor Damage'] = ceil($diminishing * $this_weapon['Armor Damage']);
//	
//	
//	return $this_weapon;
//}
}

?>