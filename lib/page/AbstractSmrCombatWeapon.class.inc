<?
require_once('CombatWeapon.interface.inc');
require_once('SmrMySqlDatabase.class.inc');

require_once('AbstractSmrPlayer.class.inc');
abstract class AbstractSmrCombatWeapon implements CombatWeapon
{
	protected $gameTypeID;
	protected $name;
	protected $raceID;
	protected $maxDamage;
	protected $shieldDamage;
	protected $armourDamage;
	protected $accuracy;
	protected $damageRollover;
	protected $raidWeapon;
	
	public function getBaseAccuracy()
	{
		return $this->accuracy;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function getMaxDamage()
	{
		return $this->maxDamage;
	}
	
	public function getShieldDamage()
	{
		return $this->shieldDamage;
	}
	
	public function getArmourDamage()
	{
		return $this->armourDamage;
	}
	
	public function isDamageRollover()
	{
		return $this->damageRollover;
	}
	
	public function isRaidWeapon()
	{
		return $this->raidWeapon;
	}
	
	public function canShootTraders()
	{
		return !$this->isRaidWeapon();
	}
	
	public function getDamage()
	{
		return array('MaxDamage' => $this->getMaxDamage(), 'Shield' => $this->getShieldDamage(), 'Armour' => $this->getArmourDamage(), 'Rollover' => $this->isDamageRollover());
	}
}

?>