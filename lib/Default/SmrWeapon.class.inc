<?php
require_once('AbstractSmrCombatWeapon.class.inc');
require_once('WeightedRandom.class.inc');

class SmrWeapon extends AbstractSmrCombatWeapon {
	protected static $CACHE_WEAPONS = array();
	
	protected static $db;
	
	protected $weaponTypeID;
	protected $cost;
	protected $powerLevel;
	protected $buyerRestriction;

	
	protected static function initialiseDatabase() {
		if(self::$db==null)
			self::$db = new SmrMySqlDatabase();
	}

	public static function &getWeapon($gameTypeID,$weaponTypeID,$forceUpdate = false) {
		if($forceUpdate || !isset(self::$CACHE_WEAPONS[$gameTypeID][$weaponTypeID])) {
			$w = new SmrWeapon($gameTypeID,$weaponTypeID);
			if($w->getName()!='')
				self::$CACHE_WEAPONS[$gameTypeID][$weaponTypeID] = $w;
			else
				self::$CACHE_WEAPONS[$gameTypeID][$weaponTypeID] = false;
		}
		return self::$CACHE_WEAPONS[$gameTypeID][$weaponTypeID];
	}

	public static function &getAllWeapons($gameTypeID,$forceUpdate = false) {
		$db = new SmrMySqlDatabase();
		$db->query('SELECT weapon_type_id FROM weapon_type');
		$weapons=array();
		while($db->nextRecord()) {
			$weapons[] = self::getWeapon($gameTypeID,$db->getInt('weapon_type_id'),$forceUpdate);
		}
		return $weapons;
	}
	
	protected function __construct($gameTypeID,$weaponTypeID) {
		self::initialiseDatabase();
		
		$this->gameTypeID = $gameTypeID;
		$this->weaponTypeID = $weaponTypeID;
			
		self::$db->query('SELECT weapon_name,race_id,cost,shield_damage,armour_damage,accuracy,power_level,buyer_restriction FROM weapon_type WHERE weapon_type_id = ' . self::$db->escapeNumber($weaponTypeID) . ' LIMIT 1');
		
		if(self::$db->nextRecord()) {
			$this->name = self::$db->getField('weapon_name');
			$this->raceID = self::$db->getInt('race_id');
			$this->cost = self::$db->getInt('cost');
			$this->shieldDamage = self::$db->getInt('shield_damage');
			$this->armourDamage = self::$db->getInt('armour_damage');
			$this->accuracy = self::$db->getInt('accuracy');
			$this->powerLevel = self::$db->getInt('power_level');
			$this->buyerRestriction = self::$db->getInt('buyer_restriction');
			$this->damageRollover = false;
			$this->raidWeapon = false;
			$this->maxDamage = max($this->shieldDamage,$this->armourDamage);
		}
	}

	public function getBuyHREF(SmrLocation $location) {
		$container = create_container('shop_weapon_processing.php');
		$container['LocationID'] = $location->getTypeID();
		$container['WeaponTypeID'] = $this->getWeaponTypeID();
		return SmrSession::getNewHREF($container);
	}

	public function getSellHREF(SmrLocation $location, $orderID) {
		$container = create_container('shop_weapon_processing.php');
		$container['LocationID'] = $location->getTypeID();
		$container['WeaponTypeID'] = $this->getWeaponTypeID();
		$container['OrderID'] = $orderID;
		return SmrSession::getNewHREF($container);
	}
	
	public function getWeaponTypeID() {
		return $this->weaponTypeID;
	}
	
	public function getCost() {
		return $this->cost;
	}
	
	public function getPowerLevel() {
		return $this->powerLevel;
	}
	
	public function getBuyerRestriction() {
		return $this->buyerRestriction;
	}
	
	protected function &getWeightedRandomForPlayer(AbstractSmrPlayer &$player) {
		return WeightedRandom::getWeightedRandomForPlayer($player,'Weapon',$this->getWeaponTypeID());
	}
	
	protected function checkMiss(AbstractSmrPlayer &$player, $percent) {
		return $this->getWeightedRandomForPlayer($player)->percentFailed($percent);
	}
	
	public static function getPlayerLevelAccuracyMod(AbstractSmrPlayer &$player) {
		return ($player->getLevelID()*$player->getLevelID()/60+$player->getLevelID()/2+2) / 100;
	}
	
	public function getModifiedAccuracy(AbstractSmrPlayer &$weaponPlayer) {
		$modifiedAccuracy = $this->getBaseAccuracy();
		$modifiedAccuracy += $this->getBaseAccuracy() * ($weaponPlayer->getStat('Accuracy') * ACCURACY_STAT_FACTOR)/100;
		$modifiedAccuracy += $this->getBaseAccuracy() * self::getPlayerLevelAccuracyMod($weaponPlayer);
		if ($weaponPlayer->isGadgetEquipped('Increased Accuracy'))
			$modifiedAccuracy += $this->getBaseAccuracy() * INCREASED_ACC_GADGET_FACTOR/100;
		return $modifiedAccuracy;
	}
	
	public function getModifiedAccuracyAgainstForces(AbstractSmrPlayer &$weaponPlayer, SmrForce &$forces) {
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
		return $modifiedAccuracy;
	}
	
	public function getModifiedAccuracyAgainstPort(AbstractSmrPlayer &$weaponPlayer, SmrPort &$port) {
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
		$modifiedAccuracy -= $this->getBaseAccuracy() * $port->getLevel()/50;
		return $modifiedAccuracy;
	}
	
	public function getModifiedAccuracyAgainstPlanet(AbstractSmrPlayer &$weaponPlayer, SmrPlanet &$planet) {
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
		$modifiedAccuracy -= $this->getBaseAccuracy() * $planet->getLevel()/350;
		return $modifiedAccuracy;
	}
	
	public function getModifiedAccuracyAgainstPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer) {
		$modifiedAccuracy = $this->getModifiedAccuracy($weaponPlayer);
		$modifiedAccuracy -= $this->getBaseAccuracy() * self::getPlayerLevelAccuracyMod($targetPlayer) / 2;
		if ($targetPlayer->isGadgetEquipped('Increased Maneuverability'))
			$modifiedAccuracy -= $this->getBaseAccuracy() * INCREASED_MAN_GADGET_FACTOR/100;
		
		$weaponShip = $weaponPlayer->getShip();
		$targetShip = $targetPlayer->getShip();
		$mrDiff = $targetShip->getMR() - $weaponShip->getMR();
		if($mrDiff > 0)
			$modifiedAccuracy -= $this->getBaseAccuracy() * ($mrDiff/MR_FACTOR)/100;
	
		return $modifiedAccuracy;
	}
	
	public function getModifiedPortAccuracy(SmrPort &$port) {
		$modifiedAccuracy = $this->getBaseAccuracy();
		return $modifiedAccuracy;
	}
	
	public function getModifiedPortAccuracyAgainstPlayer(SmrPort &$port, AbstractSmrPlayer &$targetPlayer) {
		$modifiedAccuracy = $this->getModifiedPortAccuracy($port);
		$modifiedAccuracy -= $this->getBaseAccuracy() * self::getPlayerLevelAccuracyMod($targetPlayer);
		return $modifiedAccuracy;
	}
	
	public function getModifiedPlanetAccuracy(SmrPlanet &$planet) {
		$modifiedAccuracy = $this->getBaseAccuracy();
		$modifiedAccuracy += $planet->getLevel() / 2;
		return $modifiedAccuracy;
	}
	
	public function getModifiedPlanetAccuracyAgainstPlayer(SmrPlanet &$planet, AbstractSmrPlayer &$targetPlayer) {
		$modifiedAccuracy = $this->getModifiedPlanetAccuracy($planet);
		$modifiedAccuracy -= $this->getBaseAccuracy() * self::getPlayerLevelAccuracyMod($targetPlayer);
		return $modifiedAccuracy;
	}
	
	public function &getModifiedDamage(AbstractSmrPlayer &$weaponPlayer) {
		$damage = $this->getDamage();
		//do they have the weapon damage gadget?
		if($weaponPlayer->isGadgetEquipped('Increased Weapon Damage')) {
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
	
	public function &getModifiedDamageAgainstForces(AbstractSmrPlayer &$weaponPlayer, SmrForce &$forces) {
		if(!$this->canShootForces()) // If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		$damage =& $this->getModifiedDamage($weaponPlayer);
		
		$diminishing = $weaponPlayer->getDiminishingForceAttack();
		$damage['MaxDamage'] = ceil($diminishing * $damage['MaxDamage']);
		$damage['Shield'] = ceil($diminishing * $damage['Shield']);
		$damage['Armour'] = ceil($diminishing * $damage['Armour']);
		return $damage;
	}
	
	public function &getModifiedDamageAgainstPort(AbstractSmrPlayer &$weaponPlayer, SmrPort &$port) {
		if(!$this->canShootPorts()) // If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		$damage =& $this->getModifiedDamage($weaponPlayer);
		
		$diminishing = $weaponPlayer->getDiminishingPortAttack();
		$damage['MaxDamage'] = ceil($diminishing * $damage['MaxDamage']);
		$damage['Shield'] = ceil($diminishing * $damage['Shield']);
		$damage['Armour'] = ceil($diminishing * $damage['Armour']);
		return $damage;
	}
	
	public function &getModifiedDamageAgainstPlanet(AbstractSmrPlayer &$weaponPlayer, SmrPlanet &$planet) {
		if(!$this->canShootPlanets()) // If we can't shoot forces then just return a damageless array and don't waste resources calculated any damage mods.
			return array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		$damage =& $this->getModifiedDamage($weaponPlayer);
		
		$damage['MaxDamage'] /= 5;
		$damage['Shield'] /= 5;
		$damage['Armour'] /= 5;
		
		$diminishing = $weaponPlayer->getDiminishingPlanetAttack();
		$damage['MaxDamage'] = ceil($diminishing * $damage['MaxDamage']);
		$damage['Shield'] = ceil($diminishing * $damage['Shield']);
		$damage['Armour'] = ceil($diminishing * $damage['Armour']);
		return $damage;
	}
	
	public function &getModifiedForceDamageAgainstPlayer(SmrForce &$forces, AbstractSmrPlayer &$targetPlayer) {
		$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
		return $return;
	}
	
	public function &getModifiedDamageAgainstPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer) {
		if(!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculating any damage mods.
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
	
	public function &getModifiedPortDamageAgainstPlayer(SmrPort &$port, AbstractSmrPlayer &$targetPlayer) {
		if(!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculating any damage mods.
			$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
			return $return;
		}
		$damage = $this->getDamage();
		return $damage;
	}
	
	public function &getModifiedPlanetDamageAgainstPlayer(SmrPlanet &$planet, AbstractSmrPlayer &$targetPlayer) {
		if(!$this->canShootTraders()) { // If we can't shoot traders then just return a damageless array and don't waste resources calculating any damage mods.
			$return = array('MaxDamage' => 0, 'Shield' => 0, 'Armour' => 0, 'Rollover' => $this->isDamageRollover());
			return $return;
		}
		$damage = $this->getDamage();
		return $damage;
	}
	
	public function &shootForces(AbstractSmrPlayer &$weaponPlayer, SmrForce &$forces) {
		$return = array('Weapon' => &$this, 'TargetForces' => &$forces);
		if($this->checkMiss($weaponPlayer, $this->getModifiedAccuracyAgainstForces($weaponPlayer,$forces))) { //Check if we miss
			$return['Hit'] = false;
			return $return;
		}
		$return['Hit'] = true;
		return $this->doPlayerDamageToForce($return,$weaponPlayer, $forces);
	}
	
	public function &shootPort(AbstractSmrPlayer &$weaponPlayer, SmrPort &$port) {
		$return = array('Weapon' => &$this, 'TargetPort' => &$port);
		if($this->checkMiss($weaponPlayer, $this->getModifiedAccuracyAgainstPort($weaponPlayer,$port))) { //Check if we miss
			$return['Hit'] = false;
			return $return;
		}
		$return['Hit'] = true;
		return $this->doPlayerDamageToPort($return,$weaponPlayer, $port);
	}
	
	public function &shootPlanet(AbstractSmrPlayer &$weaponPlayer, SmrPlanet &$planet, $delayed) {
		$return = array('Weapon' => &$this, 'TargetPlanet' => &$planet);
		if($this->checkMiss($weaponPlayer, $this->getModifiedAccuracyAgainstPlanet($weaponPlayer,$planet))) { //Check if we miss
			$return['Hit'] = false;
			return $return;
		}
		$return['Hit'] = true;
		return $this->doPlayerDamageToPlanet($return,$weaponPlayer, $planet, $delayed);
	}
	
	public function &shootPlayer(AbstractSmrPlayer &$weaponPlayer, AbstractSmrPlayer &$targetPlayer) {
		$return = array('Weapon' => &$this, 'TargetPlayer' => &$targetPlayer);
		if($this->checkMiss($weaponPlayer, $this->getModifiedAccuracyAgainstPlayer($weaponPlayer,$targetPlayer))) { //Check if we miss
			$return['Hit'] = false;
			return $return;
		}
		$return['Hit'] = true;
		return $this->doPlayerDamageToPlayer($return,$weaponPlayer, $targetPlayer);
	}
	
	public function &shootPlayerAsForce(SmrForce &$forces, AbstractSmrPlayer &$targetPlayer) {
		$return = array('Weapon' => &$this, 'TargetPlayer' => &$targetPlayer, 'Hit' => false);
		return $return;
	}
	
	public function &shootPlayerAsPort(SmrPort &$port, AbstractSmrPlayer &$targetPlayer) {
		$return = array('Weapon' => &$this, 'TargetPlayer' => &$targetPlayer);
		if($this->checkMiss($targetPlayer, $this->getModifiedPortAccuracyAgainstPlayer($port,$targetPlayer))) { //Check if the port misses, weight based upon the player being shot at.
			$return['Hit'] = false;
			return $return;
		}
		$return['Hit'] = true;
		return $this->doPortDamageToPlayer($return,$port, $targetPlayer);
	}
	
	public function &shootPlayerAsPlanet(SmrPlanet &$planet, AbstractSmrPlayer &$targetPlayer) {
		$return = array('Weapon' => &$this, 'TargetPlayer' => &$targetPlayer);
		if($this->checkMiss($targetPlayer, $this->getModifiedPlanetAccuracyAgainstPlayer($planet,$targetPlayer))) { //Check if the planet misses, weight based upon the player being shot at.
			$return['Hit'] = false;
			return $return;
		}
		$return['Hit'] = true;
		return $this->doPlanetDamageToPlayer($return,$planet, $targetPlayer);
	}
	
//private function get_weapon($weaponPlayer,$weaponShip, $weapon_id, $purpose = 'Normal',$targetPlayer=null,$targetShip=null) {
//	//this will be used in the future to display more accurate info for the person using the weapon
//	global TIME,$GAME_ID, $WEAPONS, $ACCURACY_MOD, $WEAPON_DAMAGE_MOD, $BOMBARDMENT_MOD, $GADGETS, $INCREASED_ACC, $INCREASED_MAN, $INCREASED_DAMAGE_GADGET_VALUE, $WEAPON_RESTRICTIONS,$INCREASED_PLANET_DAMAGE_GADGET_VALUE;
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
//	if (gadget_equipped($weaponPlayer,'Weapon Accelerator') && ($purpose == 'PortRaid' || $purpose == 'PlanetRaid')) {
//		$this_weapon['Shield Damage'] *= $INCREASED_PLANET_DAMAGE_GADGET_VALUE;
//		$this_weapon['Armour Damage'] *= $INCREASED_PLANET_DAMAGE_GADGET_VALUE;
//	}
//	//increase for raid damage stat if they are raiding
//	if ($purpose == 'PortRaid' || $purpose == 'PlanetRaid') {
//		$this_weapon['Shield Damage'] *= 1 + ($BOMBARDMENT_MOD * $weaponPlayer->getStat('Bombardment')) / 100;
//		$this_weapon['Armour Damage'] *= 1 + ($BOMBARDMENT_MOD * $weaponPlayer->getStat('Bombardment')) / 100;
//	}
//	//get diminishing returns for attacking
//	$diminishing=1;
//	if($purpose == 'PlanetRaid') {
//		global $REPAIR_BEFORE_DIMINISHING_PLANET_RAID;
//		$diminishing = 1 - ($weaponPlayer->getPastMaint() / $REPAIR_BEFORE_DIMINISHING_PLANET_RAID);
//	}
//	else if($purpose == 'PortRaid') {
//		global $REPAIR_BEFORE_DIMINISHING_PORT_RAID;
//		$diminishing = 1 - ($weaponPlayer->getPastMaint() / $REPAIR_BEFORE_DIMINISHING_PORT_RAID);
//	}
//	else if($purpose == 'Forces') {
//		global $REPAIR_BEFORE_DIMINISHING_FORCE_ATTACK;
//		$diminishing = 1 - ($weaponPlayer->getPastMaint() / $REPAIR_BEFORE_DIMINISHING_FORCE_ATTACK);
//	}
//	else if($purpose == 'Normal') {
//		$REPAIR_BEFORE_DIMINISHING_ATTACK = $weaponShip['Speed'] * 25 + 475;
//		$diminishing = 1 - ($weaponPlayer->getPastMaint() / $REPAIR_BEFORE_DIMINISHING_ATTACK);
//	}
//	if ($diminishing <= 0) $diminishing = 0.00001;
//	$this_weapon['Shield Damage'] = ceil($diminishing * $this_weapon['Shield Damage']);
//	$this_weapon['Armour Damage'] = ceil($diminishing * $this_weapon['Armour Damage']);
//
//
//	return $this_weapon;
//}
}

?>
