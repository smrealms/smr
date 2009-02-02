<?php
require_once('SmrMySqlDatabase.class.inc');
require_once('Globals.class.inc');
class SmrPort
{
	protected static $CACHE_PORTS = array();
	protected static $CACHE_CACHED_PORTS = array();
	
	protected $db;
	
	protected static $damageNeededForDowngradeChance = 1000;
	protected static $chanceToDowngrade = 15;
	
	protected $gameID;
	protected $sectorID;
	protected $raceID;
	protected $lastUpdate;
	protected $shields;
	protected $combatDrones;
	protected $armour;
	protected $reinforceTime;
	protected $attackStarted;
	protected $level;
	protected $credits;
	protected $upgradeCredits;
	protected $experience;
	protected $goods;
	
	protected $turrets = 20;
	protected $turretDamage = 250;
	
	protected $tradeHREF;
	protected $raidWarningHREF;
	
	protected $cachedVersion = false;
	protected $cachedTime = TIME;
	
	protected $SQL;

	public static function &getPort($gameID,$sectorID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_PORTS[$gameID . ':' . $sectorID]))
		{
			$p = new SmrPort($gameID,$sectorID);
			return self::$CACHE_PORTS[$gameID . ':' . $sectorID] =& $p;
		}
		return self::$CACHE_PORTS[$gameID . ':' . $sectorID];
	}
	
	protected function __construct($gameID,$sectorID)
	{
		$this->db = new SmrMySqlDatabase();
		$this->db->query('SELECT * FROM port WHERE sector_id = '.$sectorID.' AND game_id = '.$gameID.' LIMIT 1');
		if ($this->db->nextRecord())
		{
			$this->gameID = $this->db->getField('game_id');
			$this->sectorID = $this->db->getField('sector_id');
			$this->lastUpdate = $this->db->getField('last_update');
			$this->shields = $this->db->getField('shields');
			$this->combatDrones = $this->db->getField('combat_drones');
			$this->armour = $this->db->getField('armor');
			$this->reinforceTime = $this->db->getField('reinforce_time');
			$this->attackStarted = $this->db->getField('attack_started');
			$this->raceID = $this->db->getField('race_id');
			$this->level = $this->db->getField('level');
			$this->credits = $this->db->getField('credits');
			$this->upgrade = $this->db->getField('upgrade');
			$this->experience = $this->db->getField('experience');
			
			
			$this->SQL = 'sector_id = '.$this->sectorID.' AND game_id = '.$this->gameID;
			
			$this->checkDefenses();
			$this->getGoods();
			$this->checkForUpgrade();
		}
		else
		{
			return;
		}
	}
	
	public function checkDefenses()
	{
		if(!$this->isUnderAttack())
		{
			$rich_mod = floor($this->getUpgrade() / $this->getUpgradeRequirement()); // be equal to port level for bonus
			if($rich_mod < 0) $rich_mod = 0;
			$this->setShields($this->getLevel() * 1000 + 1000 + round($rich_mod * 375));
			$this->setArmour($this->getLevel() * 1000 + 1000 + round($rich_mod * 375));
			$this->setCDs($this->getLevel() * 100 + 100 + round($rich_mod * 37.5));
		}
	}
	
	public function &getGoods()
	{
		if(!$this->isCachedVersion() && empty($this->goods))
		{
			$GOODS = Globals::getGoods();
			
			$refresh_per_hour = 250 * Globals::getGameSpeed($this->gameID);
			$refresh_per_sec = $refresh_per_hour / 3600;
			$seconds_since_refresh = TIME - $this->lastUpdate;
			if ($seconds_since_refresh < 0) $seconds_since_refresh = 0;
			$amt_to_add = floor($seconds_since_refresh * $refresh_per_sec);
			
			$this->lastUpdate += round($amt_to_add / $refresh_per_sec);
				
			$this->db->query('SELECT * FROM port_has_goods WHERE ' . $this->SQL . ' ORDER BY good_id ASC LIMIT '.sizeof($GOODS));
			
			$this->goods['Sell'] = array();
			$this->goods['Buy'] = array();
			$this->goods['All Goods'] = array();
			$db = new SmrMySqlDatabase();
			while ($this->db->nextRecord())
			{
				$good = $GOODS[$this->db->getField('good_id')];
				$good['Amount'] = $this->db->getField('amount');
				$good['TransactionType'] = $this->db->getField('transaction_type');
				
				if($good['Amount'] > $good['Max']) // possible to have more than max goods.
				{
					$good['Amount'] = $good['Max'];
					$db->query('UPDATE port_has_goods SET amount = '.$good['Amount'].' WHERE '.$this->SQL.' AND good_id = '.$good['ID'].' LIMIT 1');
				}
				elseif ($good['Amount'] < $good['Max'] && $amt_to_add > 0)
				{
					if ($good['Amount'] + $amt_to_add > $good['Max'])
					{
						$good['Amount'] = $good['Max'];
					}
					else
					{
						$good['Amount'] += $amt_to_add;
					}
					$db->query('UPDATE port_has_goods SET amount = '.$good['Amount'].' WHERE '.$this->SQL.' AND good_id = '.$good['ID'].' LIMIT 1');
				}
				$g = $good;
				$this->goods['All Goods'][$g['ID']] =& $g;
				$this->goods[$g['TransactionType']][$g['ID']] =& $g;
				unset($g); //HACK Don't want to keep g around - it kills goods due to references.
			}
			$this->db->query('UPDATE port SET last_update = '.$this->lastUpdate.' WHERE '.$this->SQL.' LIMIT 1');
		}
		return $this->goods;
	}
	public function &getVisibleGoods(AbstractSmrPlayer &$player)
	{
		$goods = $this->getGoods();
		$visibleGoods = array();
		$visibleGoods['All Goods'] = array();
		$visibleGoods['Sell'] = array();
		$visibleGoods['Buy'] = array();
		foreach($goods['All Goods'] as $key => &$good)
		{
			if($player->meetsAlignmentRestriction($good['AlignRestriction']))
			{
				$visibleGoods['All Goods'][$key] =& $good;
				$visibleGoods[$good['TransactionType']][$key] =& $good;
			}
		}
		return $visibleGoods;
	}
	public function &getVisibleGoodsAll(&$player)
	{
		$goods = $this->getVisibleGoods($player);
		return $goods['All Goods'];
	}
	public function &getVisibleGoodsSold(&$player)
	{
		$goods = $this->getVisibleGoods($player);
		return $goods['Sell'];
	}
	public function &getVisibleGoodsBought(AbstractSmrPlayer &$player)
	{
		$goods = $this->getVisibleGoods($player);
		return $goods['Buy'];
	}
	
	public function &getGoodsAll()
	{
		$this->getGoods();
		return $this->goods['All Goods'];
	}
	
	public function &getGoodsSold()
	{
		$this->getGoods();
		return $this->goods['Sell'];
	}
	
	public function &getGoodsBought()
	{
		$this->getGoods();
		return $this->goods['Buy'];
	}
	
	public function &getGood($goodID)
	{
		$goods = $this->getGoods();
		return isset($goods['All Goods'][$goodID]) ? $goods['All Goods'][$goodID] : false;
	}
	
	public function hasGood($goodID)
	{
		return $this->getGood($goodID)!==false;
	}
	
	public function setGoodAmount($goodID,$amount)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot update a cached port!');
		$this->getGoods();
		if($amount < 0)
			$amount = 0;
		$max = $this->goods['All Goods'][$goodID]['Max'];
		if($amount > $max)
			$amount = $max;
		if($this->goods['All Goods'][$goodID]['Amount'] == $amount)
			return;
		$this->goods['All Goods'][$goodID]['Amount'] = $amount;
		if(isset($this->goods['Sell'][$goodID]))
			$this->goods['Sell'][$goodID]['Amount'] = $amount;
		else if(isset($this->goods['Buy'][$goodID]))
			$this->goods['Buy'][$goodID]['Amount'] = $amount;
		$this->db->query('UPDATE port_has_goods SET amount = '.$amount.' WHERE '.$this->SQL.' AND good_id = '.$goodID.' LIMIT 1');
	}
	
	public function getGoodAmount($goodID)
	{
		$good = $this->getGood($goodID);
		return $good['Amount'];
	}
	
	public function decreaseGood(array &$good,$amount,$doRefresh)
	{
		$this->setGoodAmount($good['ID'],$this->getGoodAmount($good['ID']) - $amount);
		if($doRefresh===true)
		{
			global $LEVEL_1_GOODS, $LEVEL_2_GOODS, $LEVEL_3_GOODS, $LEVEL_4_GOODS;
			//get id of goods to replenish
			$this->refreshGoods($good['Class'],$amount);
		}
	}
	
	public function increaseGoodAmount($goodID,$amount)
	{
		$this->setGoodAmount($goodID,$this->getGoodAmount($goodID) + $amount);
	}
	
	public function decreaseGoodAmount($goodID,$amount)
	{
		$this->setGoodAmount($goodID,$this->getGoodAmount($goodID) - $amount);
	}
	
	protected function refreshGoods($classTraded,$amountTraded)
	{
		$refreshAmount = $amountTraded / 8;
		//refresh goods that need it
		$portGoods =& $this->getGoodsAll();
		$refreshClass = $classTraded-1;
		foreach ($portGoods as &$good)
		{
			if($good['Class']==$refreshClass)
			{
				$this->increaseGoodAmount($good['ID'], $refreshAmount);
			}
		}
	}
	
	public function buyGoods(array &$good,$amount,$cost,$exp)
	{
		$this->increaseUpgrade(min($amount * 1000,$cost));
		$this->increaseCredits($cost);
		$this->increaseExperience($exp);
		$this->decreaseGood($good,$amount,true);
//		$this->setGood($goodID,$this->getGood($goodID) + $amount);
	}
	
	public function sellGoods(array &$good,$amount,$cost,$exp)
	{
		$this->increaseExperience($exp);
		$this->decreaseGood($good,$amount,true);
//		$this->setGood($goodID,$this->getGood($goodID) + $amount);
	}
	
	public function checkForUpgrade()
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot upgrade a cached port!');
		while($this->upgrade >= $this->getUpgradeRequirement() && $this->level < 9)
		{
			$GOODS = Globals::getGoods();
			
			$this->upgrade -= $this->getUpgradeRequirement();
			$this->credits -= $this->getUpgradeRequirement();
			shuffle($GOODS);
			
			$type = (mt_rand(1,2) == 1) ? 'Buy' : 'Sell';
			switch($this->level)
			{
				case 0:
					for($i=0;$i<3;$i++) // Add 3 goods for upgrading to level 1
					{
						do
						{
							$newGood = array_shift($GOODS);
						} while( ($this->hasGood($newGood['ID']) || $newGood['Class']!=1) && ($count = count($GOODS))!=0 );
						if($count == 0)
							break;
						$this->addPortGood($newGood['ID'],$type);
					}
				break;
				
				case 1:
					do
					{
						$newGood = array_shift($GOODS);
					} while( ($this->hasGood($newGood['ID']) || $newGood['Class']!=1) && ($count = count($GOODS))!=0 );
					if($count == 0)
						break;
					$this->addPortGood($newGood['ID'],$type);
				break;
				
				case 2:
				case 3:
				case 4:
				case 5:
					do
					{
						$newGood = array_shift($GOODS);
					} while( ($this->hasGood($newGood['ID']) || $newGood['Class']!=2) && ($count = count($GOODS))!=0 );
					if($count == 0)
						break;
					$this->addPortGood($newGood['ID'],$type);
				break;
				
				case 6:
				case 7:
					do
					{
						$newGood = array_shift($GOODS);
					} while( ($this->hasGood($newGood['ID']) || $newGood['Class']!=3) && ($count = count($GOODS))!=0 );
					if($count == 0)
						break;
					$this->addPortGood($newGood['ID'],$type);
				break;
	
				case 8: // Add 1 level 3, and 50% chance of level 4
					do
					{
						$newGood = array_shift($GOODS);;
					} while( ($this->hasGood($newGood['ID']) || $newGood['Class']!=3) && ($count = count($GOODS))!=0 );
					if($count == 0)
						break;
					$this->addPortGood($newGood['ID'],$type);
			
					if (mt_rand(1,100) <= 50) //50% chance to get tier 4 when upgrading to level 9
					{
						$type = (mt_rand(1,2) == 1) ? 'Buy' : 'Sell';
						do
						{
							$newGood = array_shift($GOODS);;
						} while( ($this->hasGood($newGood['ID']) || $newGood['Class']!=4) && ($count = count($GOODS))!=0 );
						if($count == 0)
							break;
						$this->addPortGood($newGood['ID'],$type);
					}
				break;
				
				default: // We don't want to add goods.
				break;
			}
			$this->level++;
			$this->db->query('UPDATE `port` SET `level`=' . $this->level . ',`upgrade`='.$this->upgrade.',`credits`='.$this->credits.' WHERE '.$this->SQL.';');
		}
	}
	
	public function getUpgradeRequirement()
	{
//		return round(exp($this->getLevel()/1.7)+3)*1000000;
		return $this->getLevel() * 1000000;
	}
	
	protected function addPortGood($goodID,$type)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot update a cached port!');
		if(!is_numeric($goodID))
			return false;
		$newGood = Globals::getGood($goodID);
		$newGood['Amount'] = $newGood['Max'];
		$newGood['TransactionType'] = $type;
		$this->goods['All Goods'][$goodID] =& $newGood;
		$this->goods[$type][$type] =& $newGood;
		$this->db->query('REPLACE INTO port_has_goods (game_id, sector_id, good_id, transaction_type, amount) VALUES (' . $this->getGameID() . ',' . $this->getSectorID() . ',' . $goodID . ',' . $this->db->escapeString($type) . ',' . $newGood['Amount'] . ')');
	}
	
	protected function removePortGood($goodID)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot update a cached port!');
		unset($this->goods['All Goods'][$goodID]);
		if(isset($this->goods['Sell'][$goodID])) unset($this->goods['Sell'][$goodID]);
		elseif(isset($this->goods['Buy'][$goodID])) unset($this->goods['Buy'][$goodID]);
		
		$this->db->query('DELETE FROM port WHERE '.$this->SQL.' AND `good_id`='.$goodID.';');
	}
	
	public function checkForDowngrade($damageDone)
	{
		for(;$damageDone>self::$damageNeededForDowngradeChance;$damageDone-=self::$damageNeededForDowngradeChance)
		{
			if (mt_rand(1,100) <= self::$chanceToDowngrade && $this->level > 1)
			{
				$this->doDowngrade();
			}
		}
	}
	
	protected function doDowngrade()
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot downgrade a cached port!');
		global $LEVEL_1_GOODS,$LEVEL_2_GOODS,$LEVEL_3_GOODS,$LEVEL_4_GOODS;
		$level1_goods = $LEVEL_1_GOODS; //5 of these chosen for lvl 9 port
		$level2_goods = $LEVEL_2_GOODS; //4 of these chosen for lvl 9 port
		$level3_goods = $LEVEL_3_GOODS; //3 of these chosen for lvl 9 port
		$level4_goods = $LEVEL_4_GOODS; //50% of one of these for lvl 9 port
		//mix up goods
		shuffle($level1_goods);
		shuffle($level2_goods);
		shuffle($level3_goods);
		shuffle($level4_goods);
		
		switch($this->level)
		{
	//		case 1:
	//			for($i=0;$i<3;$i++) // Add 3 goods for levels 1
	//			{
	//				do
	//				{
	//					$newGood = array_shift($level1_goods);;
	//				} while( !isset($this->goods['All Goods'][$newGood]) && !empty($level1_goods) );
	//				$this->removePortGood($newGood);
	//			}
	//		break;
			
			case 2:
				do
				{
					$newGood = array_shift($level1_goods);;
				} while( !isset($this->goods['All Goods'][$newGood]) && !empty($level1_goods) );
				$this->removePortGood($newGood);
			break;
			
			case 3:
			case 4:
			case 5:
			case 6:
				do
				{
					$newGood = array_shift($level2_goods);;
				} while( !isset($this->goods['All Goods'][$newGood]) && !empty($level2_goods) );
				$this->removePortGood($newGood);
			break;
			
			case 7:
			case 8:
				do
				{
					$newGood = array_shift($level3_goods);;
				} while( !isset($this->goods['All Goods'][$newGood]) && !empty($level3_goods) );
				$this->removePortGood($newGood);
			break;
	
			case 9: // Add 1 level 3, and 50% chance of level 4
				do
				{
					$newGood = array_shift($level3_goods);;
				} while( !isset($this->goods['All Goods'][$newGood]) && !empty($level3_goods) );
				$this->removePortGood($newGood);
		
				
				do
				{
					$newGood = array_shift($level4_goods);;
				} while( !isset($this->goods['All Goods'][$newGood]) && !empty($level4_goods) );
				$this->removePortGood($newGood);
			break;
	
			default: // We don't want to remove goods.
			break;
		}
		$this->level--;
		$this->db->query('UPDATE `port` SET `level`=' . $this->level . ',`upgrade`=0 WHERE '.$this->SQL.';');
	}
	
	public function attackedBy(AbstractSmrPlayer $trigger, array $attackers)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot attack a cached port!');
		if (!$this->isUnderAttack())
		{
			//5 mins per port level
			$nextReinforce = round(TIME + $this->getLevel() * 60 * 5);
			
			$this->db->query('UPDATE port SET reinforce_time = '.$nextReinforce.', attack_started = '.TIME.' WHERE '.$this->SQL.' LIMIT 1');
	
			//add news
			$newsMessage = '<span class="red">*MAYDAY* *MAYDAY*</span> The port in sector #<span class="sectorColor">' . $this->sectorID . '</span> is under attack by ';
			if($trigger->hasAlliance())
				$newsMessage .= 'members of <span class="yellow">'.$trigger->getAllianceName().'</span>';
			else
				$newsMessage .= $trigger->getDisplayName();
			
			$newsMessage .= '! We request immediate backup!';
//			$irc_message = '[k00,01]The port in sector [k11]'.$this->sectorID.'[k00] is under attack![/k]';
			$this->db->query('INSERT INTO news (game_id, time, news_message, type) VALUES ('.$this->getGameID().','.TIME.','.$this->db->escapeString($newsMessage).',\'REGULAR\')');
		}
	}
	
	public function getDisplayName()
	{
		return '<span style="color:yellow;font-variant:small-caps">Port ' . $this->getSectorID() . '</span>';
	}
	
	public function setShields($shields)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot update a cached port!');
		if($shields<0)
			$shields=0;
		if($this->shields == $shields)
			return;
		$this->shields=$shields;
		$this->db->query('UPDATE port SET shields = '.$this->shields.' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public function setArmour($armour)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot update a cached port!');
		if($armour<0)
			$armour=0;
		if($this->armour == $armour)
			return;
		$this->armour=$armour;
		$this->db->query('UPDATE port SET armor = '.$this->armour.' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public function setCDs($combatDrones)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot update a cached port!');
		if($combatDrones<0)
			$combatDrones=0;
		if($this->combatDrones == $combatDrones)
			return;
		$this->combatDrones=$combatDrones;
		$this->db->query('UPDATE port SET combat_drones = '.$this->combatDrones.' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public function setCredits($credits)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot update a cached port!');
		if($this->credits == $credits)
			return;
		$this->credits = $credits;
		$this->db->query('UPDATE port SET credits = '.$this->credits.' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public function increaseCredits($credits)
	{
		if($credits<0)
			throw new Exception('Cannot increase negative credits.');
		$this->setCredits($this->getCredits()+$credits);
	}
	
	public function setUpgrade($upgrade)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot update a cached port!');
		if($this->upgrade == $upgrade)
			return;
		$this->upgrade = $upgrade;
		$this->db->query('UPDATE port SET upgrade = '.$this->upgrade.' WHERE '.$this->SQL.' LIMIT 1');
		$this->checkForUpgrade();
	}
	
	public function increaseUpgrade($upgrade)
	{
		if($upgrade<0)
			throw new Exception('Cannot increase negative upgrade.');
		$this->setUpgrade($this->getUpgrade()+$upgrade);
	}
	
	public function increaseExperience($experience)
	{
		if($experience<0)
			throw new Exception('Cannot increase negative experience.');
		$this->setExperience($this->getExperience()+$experience);
	}
	
	public function setExperience($experience)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot update a cached port!');
		if($this->experience == $experience)
			return;
		$this->experience = $experience;
		$this->db->query('UPDATE port SET experience = '.$this->experience.' WHERE '.$this->SQL.' LIMIT 1');
	}
	
	public function getGameID()
	{
		return $this->gameID;
	}
	
	public function getSectorID()
	{
		return $this->sectorID;
	}
	
	public function getRaceID()
	{
		return $this->raceID;
	}
	
	public function getRaceName()
	{
		$races = Globals::getRaces();
		return $races[$this->getRaceID()]['Race Name'];
	}
	
	public function getLevel()
	{
		return $this->level;
	}
	
	public function getLastUpdate()
	{
		return $this->lastUpdate;
	}
	
	public function getShields()
	{
		return $this->shields;
	}
	
	public function hasShields()
	{
		return ($this->getShields() > 0);
	}
	
	public function getCDs()
	{
		return $this->combatDrones;
	}
	
	public function hasCDs()
	{
		return ($this->getCDs() > 0);
	}
	
	public function getArmour()
	{
		return $this->armour;
	}
	
	public function hasArmour()
	{
		return ($this->getArmour() > 0);
	}
	
	public function getExperience()
	{
		return $this->experience;
	}
	
	public function getCredits()
	{
		return $this->credits;
	}
	
	public function getUpgrade()
	{
		return $this->upgrade;
	}
	
	public function &getWeapons()
	{
		$weapons = array();
		for($i=0;$i<$this->getLevel();++$i)
			$weapons[$i] =& SmrWeapon::getWeapon(Globals::getGameType($this->getGameID()),WEAPON_PORT_TURRET);
		return $weapons;
	}
	
	public function getAccuracy($targetLevel)
	{
		return $this->getBaseAccuracy() - $targetLevel / 1.5;
	}

	function getUpgradePercent()
	{
		return min(1,$this->upgrade / $this->getUpgradeRequirement());
	}
	
	function getCreditsPercent()
	{
		return min(1,$this->credits / 3200000);
	}
	
	function getReinforcePercent()
	{
		if(!$this->isUnderAttack())
			return 0;
		return max(1,$this->getReinforceTime() - TIME) / ($this->getReinforceTime() - $this->getAttackStarted());
	}
	
	function getReinforceTime()
	{
		return $this->reinforceTime;
	}
	
	function getAttackStarted()
	{
		return $this->attackStarted;
	}
	
	public function isUnderAttack()
	{
		return ($this->getReinforceTime() >= TIME);
	}
	
	public function isDestroyed()
	{
		return ($this->getArmour()<1&&$this->isUnderAttack());
	}
	
	public function exists()
	{
		return $this->sectorID!=null;
	}
	
	public function addCredits($credits,$portIsSelling=false)
	{
		if($portIsSelling===true)
			$this->addUpgrade($credits);
		$this->setCredits($this->credits + $credits);
	}
	
	public function addUpgrade($upgrade)
	{
		$this->setUpgrade($this->upgrade + $upgrade);
	}
	
	public function addExperience($exp)
	{
		$this->setExperience($this->experience + $exp);
	}
	
	public function decreaseShields($number)
	{
		$this->setShields($this->getShields() - $number);
	}
	
	public function decreaseCombatDrones($number)
	{
		$this->setCDs($this->getCDs() - $number);
	}
	
	public function decreaseArmour($number)
	{
		$this->setArmour($this->getArmour() - $number);
	}
	
	function getIdealPrice($goodID, $dist, $transactionType, $numGoods, $relations)
	{
//		echo 'Good:'.$goodID.',Dist::'.$dist.',Type:'.$transactionType.',NumGoods:'.$numGoods.',Relations:'.$relations.',';
		if($relations>1000)
			$relations=1000;
		$goods = $this->getGoods();
		$good = $goods['All Goods'][$goodID];
		$base = $good['BasePrice'];
		$maxSupply = $good['Max'];
		$supply = $good['Amount'];
	
//		$relations_factor_buy = 2-(($relations + 500) / 1500);
//		$relations_factor_sell = ($relations + 500) / 1500;
		if($transactionType == 'Sell')
			$idealPrice = round( $numGoods * $base * 0.6 * (pow($dist + .5, 1.8)) * (2.5-($supply / $maxSupply)) * (($relations + 350) / 8415) );
		else if($transactionType == 'Buy')
			$idealPrice = round( $numGoods * $base * 0.5 * pow($dist, 1.84) * (2.5-($supply / $maxSupply)) * (2-($relations + 50) / 850) * (($relations + 350) / 1500) / 38.56 * (1 + (10 - $this->getLevel()) / 50));
		return $idealPrice;
	}
	
	function getOfferPrice($idealPrice, $relations, $transactionType)
	{
		$relations=max(0,min(1000,$relations));
		$relationsEffect = pow(1+($relations-1000)/10000,1.2-$relations/1000);
		 
		 if($transactionType == 'Buy')
			$relationsEffect = 2-$relationsEffect;
		return round($idealPrice * $relationsEffect);
//		$range = .11 - .095;
//		$rand = .095 + $range * mt_rand(0, 32767)/32767;
//		
//		if($transactionType == 'Buy')
//			$offeredPrice = round( $idealPrice * (($relations - 1250) / 10000 + 1 * (pow($relations / 500 + 1, $rand)) - ($relations / 9500)) );
//		else if($transactionType == 'Sell')
//			$offeredPrice = round( $idealPrice * (($relations - 1500) / 10000 + 1 * (pow($relations / 500 + 1, $rand)) - ($relations / 2450) + .35) );
//		return $offeredPrice;
	}
	
	public function getTradeHREF()
	{
		if(!isset($this->tradeHREF))
		{
			$container = array();
			$container['url'] = 'skeleton.php';
			$container['body'] = 'shop_goods.php';
			$this->tradeHREF = SmrSession::get_new_href($container);
		}
		return $this->tradeHREF;
	}
	
	public function getRaidWarningHREF()
	{
		if(!isset($this->raidWarningHREF))
		{
			$container = array();
			$container['url'] = 'skeleton.php';
			$container['body'] = 'port_attack_warning.php';
			$this->raidWarningHREF = SmrSession::get_new_href($container);
		}
		return $this->raidWarningHREF;
	}
	
	public function getAttackHREF()
	{
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'port_attack_processing_new.php';
		$container['port_id'] = $this->getSectorID();
		return SmrSession::get_new_href($container);
	}
	
	public function getClaimHREF()
	{
		$container = array();
		$container['url'] = 'port_claim_processing.php';
		$container['port_id'] = $this->getSectorID();
		return SmrSession::get_new_href($container);
	}
	
	public function getLootHREF()
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot raid a cached port!');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'port_loot.php';
		$container['port_id'] = $this->getSectorID();
		return SmrSession::get_new_href($container);
	}
	public function isCachedVersion()
	{
		return $this->cachedVersion;
	}
	public function getCachedTime()
	{
		return $this->cachedTime;
	}
	public function addCachePort($accountID,$forceUpdate=true)
	{
		if ($this->exists())
		{
			$cache = serialize(&$this);
			//give them the port info
			$command = 'REPLACE';
			if(!$forceUpdate)
				$command = 'INSERT IGNORE';
			$this->db->query($command.' INTO player_visited_port ' .
						'(account_id, game_id, sector_id, visited, port_info) ' .
						'VALUES ('.$accountID.', '.$this->getGameID().', '.$this->getSectorID().', '.$this->getCachedTime().', '.$this->db->escapeString($cache).')');	
			$this->cachedVersion = false; //Serializing this would have designated it as cached, this version isn't.
			unserialize(&$cache);
		}
	}
	public static function &getCachedPort($gameID,$sectorID,$accountID,$forceUpdate=false)
	{
		if($forceUpdate || !isset(self::$CACHE_CACHED_PORTS[$gameID . ':' . $sectorID . ':' . $accountID]))
		{
			$db = new SmrMySqlDatabase();
			$db->query('SELECT port_info FROM player_visited_port ' .
							  'WHERE account_id = '.$accountID.' AND ' .
							  		'game_id = '.$gameID.' AND ' .
							  		'sector_id = '.$sectorID.' LIMIT 1');
							  		
			if($db->nextRecord())
			{
				return self::$CACHE_CACHED_PORTS[$gameID . ':' . $sectorID . ':' . $accountID] = unserialize($db->getField('port_info'));
			}
			else
				return self::$CACHE_CACHED_PORTS[$gameID . ':' . $sectorID . ':' . $accountID] = false;
		}
		return self::$CACHE_CACHED_PORTS[$gameID . ':' . $sectorID . ':' . $accountID];
	}
	
	public function __sleep()
	{
		$this->getGoods(); //have to make sure goods have been loaded
		$this->cachedVersion = true;
        return array('gameID', 'sectorID', 'raceID', 'cachedVersion', 'cachedTime', 'goods');
	}
	
	public function __wakeup()
	{
	}
	
	function update()
	{
		$this->db->query('UPDATE `port` SET `experience` = '.$this->getExperience().', ' .
								'`last_update` = '.$this->getLastUpdate().', ' .
								'`shields` = '.$this->getShields().', ' .
								'`armor` = '.$this->getArmour().', ' .
								'`combat_drones` = '.$this->getCDs().', ' .
								'`level` = '.$this->getLevel().', ' .
								'`credits` = '.$this->getCredits().', ' .
								'`upgrade` = '.$this->getUpgrade().', ' .
								'`reinforce_time` = '.$this->getReinforceTime().', ' .
								'`attack_started` = '.$this->getAttackStarted().' ' .
							'WHERE `game_id` = '.$this->getGameID().' AND ' .
								'`sector_id` = '.$this->getSectorID().' LIMIT 1');

		if(isset($this->goods))
			foreach ($this->goods['All Goods'] as $id => $good)
				$this->db->query('UPDATE port_has_goods SET amount = '.$good['Amount'].' ' .
					'WHERE game_id = '.$this->getGameID().' AND ' .
                              'sector_id = '.$this->getSectorID().' AND ' .
                              'good_id = '.$good['ID'].' LIMIT 1');
    }
	
	
	public function &shootPlayer(AbstractSmrPlayer &$targetPlayer)
	{
		return $this->shootPlayers(array($targetPlayer));
	}
	
	public function &shootPlayers(array &$targetPlayers)
	{
		$results = array('Port' => &$this, 'TotalDamage' => 0);
		if($this->isDestroyed())
		{
			$results['DeadBeforeShot'] = true;
			return $results;
		}
		$results['DeadBeforeShot'] = false;
		$weapons =& $this->getWeapons();
		foreach($weapons as $orderID => &$weapon)
		{
			$results['Weapons'][$orderID] =& $weapon->shootPlayerAsPort($this, $targetPlayers[array_rand($targetPlayers)]);
			if($results['Weapons'][$orderID]['Hit'])
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
		}
		if($this->hasCDs())
		{
			$thisCDs = new SmrCombatDrones($this->getGameID(),$this->getCDs());
			$results['Drones'] =& $thisCDs->shootPlayerAsPort($this, $targetPlayers[array_rand($targetPlayers)]);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
		}
		return $results;
	}
	
	public function &doWeaponDamage(array $damage)
	{
		$alreadyDead = $this->isDestroyed();
		$shieldDamage = 0;
		$cdDamage = 0;
		$armourDamage = 0;
		if(!$alreadyDead)
		{
			$shieldDamage = $this->doShieldDamage(min($damage['MaxDamage'],$damage['Armour']));
			$damage['MaxDamage'] -= $shieldDamage;
			if(!$this->hasShields() && ($shieldDamage == 0 || $damage['Rollover']))
			{
				$cdDamage = $this->doCDDamage(min($damage['MaxDamage'],$damage['Armour']));
				$damage['Armour'] -= $cdDamage;
				$damage['MaxDamage'] -= $cdDamage;
				if(!$this->hasCDs() && ($cdDamage == 0 || $damage['Rollover']))
					$armourDamage = $this->doArmourDamage(min($damage['MaxDamage'],$damage['Armour']));
			}
		}

		$return = array(
						'KillingShot' => !$alreadyDead && $this->isDestroyed(),
						'TargetAlreadyDead' => $alreadyDead,
						'Shield' => $shieldDamage,
						'CDs' => $cdDamage,
						'NumCDs' => $cdDamage/CD_ARMOUR,
						'HasCDs' => $this->hasCDs(),
						'Armour' => $armourDamage,
						'TotalDamage' => $shieldDamage+$cdDamage+$armourDamage
		);
		return $return;
	}
	
	protected function doShieldDamage($damage)
	{
		$actualDamage = min($this->getShields(),$damage);
		$this->decreaseShields($actualDamage);
		return $actualDamage;
	}
	
	protected function doCDDamage($damage)
	{
		$actualDamage = min($this->getCDs(),floor($damage/CD_ARMOUR));
		$this->decreaseCDs($actualDamage);
		return $actualDamage*CD_ARMOUR;
	}
	
	protected function doArmourDamage($damage)
	{
		$actualDamage = min($this->getArmour(),floor($damage));
		$this->decreaseArmour($actualDamage);
		return $actualDamage;
	}
	
	protected function creditCurrentAttackersForKill()
	{
		//get all players involved for HoF
		$allowed = TIME - 60 * 60 * 3;
		$this->db->query('SELECT account_id,level FROM player_attacks_port WHERE game_id = '.$this->getGameID().' AND sector_id = '.$this->getSectorID().' AND time > '.$allowed);
		$numToDelete = $this->db->getNumRows();
		while ($this->db->nextRecord())
		{
			$currPlayer =& SmrPlayer::getPlayer($this->db->getField('account_id'),$this->getGameID());
			$currPlayer->increaseHOF('port_raid',$this->db->getField('level'),'raid_levels');
			$currPlayer->increaseHOF('port_raid',1,'raids_completed');
		}
		$this->db->query('DELETE FROM player_attacks_port WHERE sector_id = '.$this->getSectorID().' AND game_id = '.$this->getGameID().' LIMIT '.$numToDelete);
	}
	
	public function &killPortByPlayer(AbstractSmrPlayer &$killer)
	{
		$return = array();
		$return['KillerCredits'] = $this->getCredits();
		$killer->increaseCredits($return['KillerCredits']);
		$killer->increaseHOF('port_raid',$return['KillerCredits'],'credits');
		$this->setCredits(0);
		
		$this->creditCurrentAttackersForKill();
		
		// News Entry
		$news = $this->getDisplayName() . ' has been successfully raided by ';
		if ($killer->hasAlliance()) $news .= 'the members of <span class="yellow">' . $killer->getAllianceName() . '</span>';
		else $news .= $killer->getDisplayName();
		$this->db->query('INSERT INTO news (game_id, time, news_message, type) VALUES ('.$this->getGameID().', ' . TIME . ', '.$this->db->escapeString($news).', \'REGULAR\')');
		// Trigger gets an alignment change and a bounty if port is taken
		$return['KillerBounty'] = $killer->getExperience() * $this->getLevel();
		$killer->increaseCurrentBountyAmount('HQ',$return['KillerBounty']);
		$killer->increaseHOF('port_raid',$return['KillerBounty'],'bounty_gain');
		
		if($this->getRaceID() > 1)
		{
			$return['KillerRelations'] = 45;
			$killer->decreaseRelations($return['KillerRelations'],$this->getRaceID());
			$killer->increaseHOF('port_raid',$return['KillerRelations'],'relation_loss');
		}
		// also we change alignment
		$relations = Globals::getRaceRelations($killer->getGameID(),$killer->getRaceID());
		if ($relations[$this->getRaceID()] < -299)
		{
			$return['KillerAlign'] = $this->getLevel() * 2;
			$killer->increaseAlignment($return['KillerAlign']);
			$killer->increaseHOF('port_raid',$return['KillerAlign'],'align_gain');
		}
		else
		{
			$return['KillerAlign'] = -$this->getLevel() * 2;
			$killer->decreaseAlignment(-$return['KillerAlign']);
			$killer->increaseHOF('port_raid',$return['KillerAlign'],'align_loss');
		}
		
		return $return;
	}
}
?>
