<?php
class SmrPort
{
	protected static $CACHE_PORTS = array();
	protected static $CACHE_CACHED_PORTS = array();
	
	const NUMBER_OF_PORT_WEAPONS = 10;
	const DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE = 1000;
	const CHANCE_TO_DOWNGRADE = 15;
	const TIME_FEDS_STAY = 1800;
	const MAX_FEDS_BONUS = 4000;
	const BARGAIN_LENIENCY_PERCENT = 0.01;
	
	protected $db;
	
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
	protected $upgrade;
	protected $experience;
	protected $goods;
	
	protected $turrets = 20;
	protected $turretDamage = 250;
	
	protected $cachedVersion = false;
	protected $cachedTime = TIME;
	
	protected $SQL;
	
	protected $hasChanged=false;
	protected $isNew=false;
	
	public static function refreshCache()
	{
		foreach(self::$CACHE_PORTS as $gameID => &$gamePorts)
		{
			foreach($gamePorts as $sectorID => &$port)
			{
				$port = self::getPort($gameID,$sectorID,true);
			}
		}
	}
	
	public static function clearCache()
	{
		self::$CACHE_PORTS = array();
		self::$CACHE_CACHED_PORTS = array();
	}

	public static function &getPort($gameID,$sectorID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_PORTS[$gameID][$sectorID]))
		{
			self::$CACHE_PORTS[$gameID][$sectorID] = new SmrPort($gameID,$sectorID);
		}
		return self::$CACHE_PORTS[$gameID][$sectorID];
	}

	public static function removePort($gameID,$sectorID)
	{
		$db = new SmrMySqlDatabase();
		$db->query('DELETE FROM port WHERE game_id = '.$gameID.' AND ' .
										'sector_id = '.$sectorID);
		$db->query('DELETE FROM port_has_goods WHERE game_id = '.$gameID.' AND ' .
										'sector_id = '.$sectorID);
		$db->query('DELETE FROM player_visited_port WHERE game_id = '.$gameID.' AND ' .
										'sector_id = '.$sectorID);
		$db->query('DELETE FROM player_attacks_port WHERE game_id = '.$gameID.' AND ' .
										'sector_id = '.$sectorID);
		self::$CACHE_PORTS[$gameID][$sectorID] = null;
		unset(self::$CACHE_PORTS[$gameID][$sectorID]);
	}

	public static function &createPort($gameID,$sectorID)
	{
		if(!isset(self::$CACHE_PORTS[$gameID][$sectorID]))
		{
			$p = new SmrPort($gameID,$sectorID,true);
			self::$CACHE_PORTS[$gameID][$sectorID] =& $p;
		}
		return self::$CACHE_PORTS[$gameID][$sectorID];
	}
	
	public static function savePorts()
	{
		foreach(self::$CACHE_PORTS as &$gamePorts)
		{
			foreach($gamePorts as &$port)
			{
				$port->update();
			}
		}
	}
	
	protected function __construct($gameID,$sectorID,$create=false)
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
			$this->armour = $this->db->getField('armour');
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
			$this->isNew = true;
			$this->gameID = $gameID;
			$this->sectorID = $sectorID;
			$this->lastUpdate = 0;
			$this->shields = 0;
			$this->combatDrones = 0;
			$this->armour = 0;
			$this->reinforceTime = 0;
			$this->attackStarted = 0;
			$this->raceID = 1;
			$this->level = 0;
			$this->credits = 0;
			$this->upgrade = 0;
			$this->experience = 0;
			
			$this->SQL = 'sector_id = '.$this->sectorID.' AND game_id = '.$this->gameID;
		}
	}
	
	public function checkDefenses()
	{
		if(!$this->isUnderAttack())
		{
			$richMod = $this->getUpgrade() / $this->getUpgradeRequirement(); // be equal to port level for bonus
			if($richMod < 0) $richMod = 0;
			//defences restock (check for fed arrival)
			$federalMod = 0;
			if(TIME < $this->getReinforceTime() + self::TIME_FEDS_STAY)
				$federalMod = (self::TIME_FEDS_STAY - (TIME - $this->getReinforceTime())) / self::TIME_FEDS_STAY;
			if ($federalMod < 0) $federalMod = 0;
			$this->setShields($this->getLevel() * 1000 + 1000 + round($richMod * 375) + round($federalMod * self::MAX_FEDS_BONUS));
			$this->setArmour($this->getLevel() * 1000 + 1000 + round($richMod * 375) + round($federalMod * self::MAX_FEDS_BONUS));
			$this->setCDs($this->getLevel() * 100 + 100 + round($richMod * 37.5) + round($federalMod * self::MAX_FEDS_BONUS/10));
		}
	}
	
	public function &getGoods()
	{
		if(!$this->isCachedVersion() && empty($this->goods))
		{
			$GOODS = Globals::getGoods();
			
			$refresh_per_hour = 250 * Globals::getGameSpeed($this->getGameID());
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
		if(isset($goods['All Goods'][$goodID]))
			return $goods['All Goods'][$goodID];
		else
		{
			$return = false;
			return $return;
		}
	}
	
	public function getGoodDistance($goodID)
	{
		$good =& $this->getGood($goodID);
		if(!isset($good['Distance']))
		{
			$x = Globals::getGood($goodID);
			switch($good['TransactionType'])
			{
				case 'Buy':
					$x['TransactionType'] = 'Sell';
				break;
				case 'Sell':
					$x['TransactionType'] = 'Buy';
			}
			$di = Plotter::findDistanceToX($x, $this->getSector(), true);
			if(is_object($di))
				$di = $di->getRelativeDistance();
			$good['Distance'] = max(1,$di);
		}
		return $good['Distance'];
	}
	
	public function getGoodStatus($goodID)
	{
		$good =& $this->getGood($goodID);
		return $good['TransactionType'];
	}
	
	public function hasGood($goodID,$type=false)
	{
		$good =& $this->getGood($goodID);
		if($good===false)
			return false;
		if(!$type)
			return true;
		return $good['TransactionType'] == $type;
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
		$good =& $this->getGood($goodID);
		return $good['Amount'];
	}
	
	public function decreaseGood(array &$good,$amount,$doRefresh)
	{
		$this->setGoodAmount($good['ID'],$this->getGoodAmount($good['ID']) - $amount);
		if($doRefresh===true)
		{
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
		$refreshAmount = round($amountTraded / 4);
		//refresh goods that need it
		$portGoods =& $this->getGoodsAll();
		$refreshClass = $classTraded+1;
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
			$this->decreaseUpgrade($this->getUpgradeRequirement());
			$this->decreaseCredits($this->getUpgradeRequirement());
			$this->doUpgrade();
		}
	}
	
	public function upgradeToLevel($level)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot upgrade a cached port!');
		while($this->getLevel()<$level)
		{
			$this->doUpgrade();
		}
	}
	
	protected function doUpgrade()
	{
		$GOODS = Globals::getGoods();
		shuffle($GOODS);
		
		$type = (mt_rand(1,2) == 1) ? 'Buy' : 'Sell';
		$count = count($GOODS);
		switch($this->getLevel())
		{
			case 0:
				for($i=0;$i<3;$i++) // Add 3 goods for upgrading to level 1
				{
					$type = (mt_rand(1,2) == 1) ? 'Buy' : 'Sell'; //Regenerate type for each good.
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
		$this->increaseLevel(1);
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
		$this->db->query('DELETE FROM route_cache WHERE game_id='.$this->db->escapeNumber($this->getGameID()));
	}
	
	protected function removePortGood($goodID)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot update a cached port!');
		unset($this->goods['All Goods'][$goodID]);
		if(isset($this->goods['Sell'][$goodID])) unset($this->goods['Sell'][$goodID]);
		elseif(isset($this->goods['Buy'][$goodID])) unset($this->goods['Buy'][$goodID]);
		
		$this->db->query('DELETE FROM port_has_goods WHERE '.$this->SQL.' AND good_id='.$goodID.';');
		$this->db->query('DELETE FROM route_cache WHERE game_id='.$this->db->escapeNumber($this->getGameID()));
	}
	
	public function checkForDowngrade($damageDone)
	{
		$downgrades = 0;
		for(;$damageDone>self::DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE;$damageDone-=self::DAMAGE_NEEDED_FOR_DOWNGRADE_CHANCE)
		{
			if (mt_rand(1,100) <= self::CHANCE_TO_DOWNGRADE && $this->level > 1)
			{
				++$downgrades;
				$this->doDowngrade();
			}
		}
		return $downgrades;
	}
	
	protected function doDowngrade()
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot downgrade a cached port!');
		
		$GOODS = Globals::getGoods();
		shuffle($GOODS);
		$count = count($GOODS);
		switch($this->level)
		{
			case 1: // we don't want to downgrade a level 1 port
//				for($i=0;$i<3;$i++) // Add 3 goods for upgrading to level 1
//				{
//					do
//					{
//						$newGood = array_shift($GOODS);
//					} while( (!$this->hasGood($newGood['ID']) || $newGood['Class']!=1) && ($count = count($GOODS))!=0 );
//					if($count == 0)
//						break;
//					$this->removePortGood($newGood['ID'],);
//				}
			break;
			
			case 2:
				do
				{
					$newGood = array_shift($GOODS);
				} while( (!$this->hasGood($newGood['ID']) || $newGood['Class']!=1) && ($count = count($GOODS))!=0 );
				if($count == 0)
					break;
				$this->removePortGood($newGood['ID']);
			break;
			
			case 3:
			case 4:
			case 5:
			case 6:
				do
				{
					$newGood = array_shift($GOODS);
				} while( (!$this->hasGood($newGood['ID']) || $newGood['Class']!=2) && ($count = count($GOODS))!=0 );
				if($count == 0)
					break;
				$this->removePortGood($newGood['ID']);
			break;
			
			case 7:
			case 8:
				do
				{
					$newGood = array_shift($GOODS);
				} while( (!$this->hasGood($newGood['ID']) || $newGood['Class']!=3) && ($count = count($GOODS))!=0 );
				if($count == 0)
					break;
				$this->removePortGood($newGood['ID']);
			break;

			case 9: // Add 1 level 3, and 50% chance of level 4
				do
				{
					$newGood = array_shift($GOODS);;
				} while( (!$this->hasGood($newGood['ID']) || $newGood['Class']!=3) && ($count = count($GOODS))!=0 );
				if($count == 0)
					break;
				$this->removePortGood($newGood['ID']);
		
				$GOODS = Globals::getGoods();
				shuffle($GOODS);
				$count = count($GOODS);
				do
				{
					$newGood = array_shift($GOODS);;
				} while( (!$this->hasGood($newGood['ID']) || $newGood['Class']!=4) && ($count = count($GOODS))!=0 );
				if($count == 0)
					break;
				$this->removePortGood($newGood['ID']);
			break;
			
			default: // We don't want to add goods.
			break;
		}
		$this->decreaseLevel(1);
		$this->setUpgrade(0);
	}
	
	public function attackedBy(AbstractSmrPlayer $trigger, array $attackers)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot attack a cached port!');
			
		$trigger->increaseHOF(1,array('Combat','Port','Number Of Triggers'), HOF_PUBLIC);
		foreach ($attackers as &$attacker)
		{
			$attacker->increaseHOF(1,array('Combat','Port','Number Of Attacks'), HOF_PUBLIC);
			$this->db->query('REPLACE INTO player_attacks_port (game_id, account_id, sector_id, time, level) VALUES ' .
						'('.$this->getGameID().', '.$attacker->getAccountID().', '.$this->getSectorID().', ' . TIME . ', ' . $this->getLevel() . ')');
		} unset($attacker);
		if (!$this->isUnderAttack())
		{
	
			//5 mins per port level
			$nextReinforce = round(TIME + $this->getLevel() * 300);
			
			$this->setReinforceTime($nextReinforce);
			$this->updateAttackStarted();
			//add news
			$newsMessage = '<span class="red bold">*MAYDAY* *MAYDAY*</span> The federal government has received a distress call from the port in sector #<span class="sectorColor">' . $this->getSectorID() . '</span>. It is under attack by ';
			if($trigger->hasAlliance())
				$newsMessage .= 'members of <span class="yellow">'.$trigger->getAllianceBBLink().'</span>';
			else
				$newsMessage .= $trigger->getBBLink();
			
			$newsMessage .= '. The Federal Government is offering ';
			
			if($trigger->hasAlliance())
				$newsMessage .= 'various bounties for the deaths of any raiding members of <span class="yellow">'.$trigger->getAllianceBBLink().'</span>';
			else
				$newsMessage .= 'a bounty of ' . number_format(round($trigger->getLevelID() * DEFEND_PORT_BOUNTY_PER_LEVEL)) . ' credits for the death of '.$trigger->getBBLink();
			$newsMessage .= ' prior to the destruction of the port, or until federal forces arrive to defend the port.';
//			$irc_message = '[k00,01]The port in sector [k11]'.$this->sectorID.'[k00] is under attack![/k]';
			$this->db->query('INSERT INTO news (game_id, time, news_message, type,killer_id,killer_alliance,dead_id) VALUES ('.$this->getGameID().','.TIME.','.$this->db->escapeString($newsMessage).',\'REGULAR\','.$trigger->getAccountID().','.$trigger->getAllianceID().','.ACCOUNT_ID_PORT.')');
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
		$this->hasChanged=true;
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
		$this->hasChanged=true;
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
		$this->hasChanged=true;
	}
	
	public function setCredits($credits)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot update a cached port!');
		if($this->credits == $credits)
			return;
		$this->credits = $credits;
		$this->hasChanged=true;
	}
	
	public function decreaseCredits($credits)
	{
		if($credits<0)
			throw new Exception('Cannot decrease negative credits.');
		$this->setCredits($this->getCredits()-$credits);
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
		$this->hasChanged=true;
		$this->checkForUpgrade();
	}
	
	public function decreaseUpgrade($upgrade)
	{
		if($upgrade<0)
			throw new Exception('Cannot decrease negative upgrade.');
		$this->setUpgrade($this->getUpgrade()-$upgrade);
	}
	
	public function increaseUpgrade($upgrade)
	{
		if($upgrade<0)
			throw new Exception('Cannot increase negative upgrade.');
		$this->setUpgrade($this->getUpgrade()+$upgrade);
	}
	
	public function setLevel($level)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot update a cached port!');
		if($this->level == $level)
			return;
		$this->level = $level;
		$this->hasChanged=true;
	}
	
	public function increaseLevel($level)
	{
		if($level<0)
			throw new Exception('Cannot increase negative level.');
		$this->setLevel($this->getLevel()+$level);
	}
	
	public function decreaseLevel($level)
	{
		if($level<0)
			throw new Exception('Cannot increase negative level.');
		$this->setLevel($this->getLevel()-$level);
	}
	
	public function setExperience($experience)
	{
		if($this->isCachedVersion())
			throw new Exception('Cannot update a cached port!');
		if($this->experience == $experience)
			return;
		$this->experience = $experience;
		$this->hasChanged=true;
	}
	
	public function increaseExperience($experience)
	{
		if($experience<0)
			throw new Exception('Cannot increase negative experience.');
		$this->setExperience($this->getExperience()+$experience);
	}
	
	public function getGameID()
	{
		return $this->gameID;
	}
	
	public function getSectorID()
	{
		return $this->sectorID;
	}
	
	public function &getSector()
	{
		return SmrSector::getSector($this->getGameID(),$this->getSectorID());
	}
	
	public function getRaceID()
	{
		return $this->raceID;
	}
	
	public function setRaceID($raceID)
	{
		if($this->raceID == $raceID)
			return;
		$this->raceID = $raceID;
		$this->hasChanged=true;
	}
	
	public function getRaceName()
	{
		return Globals::getRaceName($this->getRaceID());
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
		for($i=0;$i<self::NUMBER_OF_PORT_WEAPONS;++$i)
			$weapons[$i] =& SmrWeapon::getWeapon(Globals::getGameType($this->getGameID()),WEAPON_PORT_TURRET);
		return $weapons;
	}
	
	public function getAccuracy($targetLevel)
	{
		return $this->getBaseAccuracy() - $targetLevel / 1.5;
	}

	function getUpgradePercent()
	{
		return min(1,max(0,$this->upgrade / $this->getUpgradeRequirement()));
	}
	
	function getCreditsPercent()
	{
		return min(1,max(0,$this->credits / 32000000));
	}
	
	function getReinforcePercent()
	{
		if(!$this->isUnderAttack())
			return 0;
		return min(1,max(0,($this->getReinforceTime() - TIME) / ($this->getReinforceTime() - $this->getAttackStarted())));
	}
	
	function getReinforceTime()
	{
		return $this->reinforceTime;
	}
	
	function setReinforceTime($reinforceTime)
	{
		$this->reinforceTime=$reinforceTime;
	}
	
	function getAttackStarted()
	{
		return $this->attackStarted;
	}
	
	function updateAttackStarted()
	{
		$this->setAttackStarted(TIME);
	}
	
	public function setAttackStarted($time)
	{
		if($this->attackStarted==$time)
			return;
		$this->attackStarted=TIME;
		$this->hasChanged=true;
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
		return $this->isNew===false||$this->hasChanged===true;
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
	
	public function decreaseCDs($number)
	{
		$this->setCDs($this->getCDs() - $number);
	}
	
	public function decreaseArmour($number)
	{
		$this->setArmour($this->getArmour() - $number);
	}
	
	public function getIdealPrice($goodID, $transactionType, $numGoods, $relations)
	{
//		echo 'Good:'.$goodID.',Dist::'.$dist.',Type:'.$transactionType.',NumGoods:'.$numGoods.',Relations:'.$relations.',';
		if($relations>1000)
			$relations=1000;
		$goods = $this->getGoods();
		$good = $goods['All Goods'][$goodID];
		$base = $good['BasePrice'];
		$maxSupply = $good['Max'];
		$supply = $good['Amount'];
		$dist = $this->getGoodDistance($goodID);
	
//		$relations_factor_buy = 2-(($relations + 500) / 1500);
//		$relations_factor_sell = ($relations + 500) / 1500;
		if($transactionType == 'Sell')
			$idealPrice = round(.75 * $numGoods * $base * 0.6 * (pow($dist + .5, 1.8)) * (2.5-($supply / $maxSupply)) * (($relations + 350) / 8415) );
		else if($transactionType == 'Buy')
			$idealPrice = round($numGoods * $base * 0.5 * pow($dist, 1.84) * (2.5-($supply / $maxSupply)) * (2-($relations + 50) / 850) * (($relations + 350) / 1500) / 38.56 * (1 + (10 - $this->getLevel()) / 50));
		return $idealPrice;
	}
	
	public function getOfferPrice($idealPrice, $relations, $transactionType)
	{
		$moneyRelations=max(0,min(2000,$relations));
		$expRelations=max(0,min(1000,$relations));
		$relationsEffect = pow(1+($expRelations-1000)/10000,1.2-$expRelations/1000);
		$relationsEffect -= max(0,min(self::BARGAIN_LENIENCY_PERCENT,self::BARGAIN_LENIENCY_PERCENT*(2000-$moneyRelations)/1000)); //Gradual increase getting closer and closer to actual ideal price as relations increase to 2000 (will only be an increase of self::BARGAIN_LENIENCY_PERCENT percent extra cash)
		 
		 if($transactionType == 'Buy')
		 {
			$relationsEffect = 2-$relationsEffect;
			return max($idealPrice,floor($idealPrice * $relationsEffect));
		 }
		 else
			return min($idealPrice,ceil($idealPrice * $relationsEffect));
//		$range = .11 - .095;
//		$rand = .095 + $range * mt_rand(0, 32767)/32767;
//		
//		if($transactionType == 'Buy')
//			$offeredPrice = round( $idealPrice * (($relations - 1250) / 10000 + 1 * (pow($relations / 500 + 1, $rand)) - ($relations / 9500)) );
//		else if($transactionType == 'Sell')
//			$offeredPrice = round( $idealPrice * (($relations - 1500) / 10000 + 1 * (pow($relations / 500 + 1, $rand)) - ($relations / 2450) + .35) );
//		return $offeredPrice;
	}
	
	public function calculateExperiencePercent($idealPrice,$offerPrice,$bargainPrice,$transactionType)
	{
		if(($transactionType == 'Sell' && $bargainPrice < $offerPrice) || ($transactionType == 'Buy' && $bargainPrice > $offerPrice))
			return 0;
		if ($bargainPrice == $idealPrice)
			return 1;
		$bargainLeniency = min($idealPrice*self::BARGAIN_LENIENCY_PERCENT,abs($idealPrice-$bargainPrice));
		if ($transactionType == 'Sell')
			$bargainLeniency = -$bargainLeniency;
		$bargainPrice -= $bargainLeniency;
		
		$val = ($idealPrice-$bargainPrice)/($idealPrice-$this->getOfferPrice($idealPrice,0,$transactionType));
//			if ($transactionType == 'Sell' && $val>0)
//				$val=1;
//			else if($transactionType == 'Buy' && $val<0)
//				$val=1;
		$val=abs($val);
		if($val>1)
			$val=1;
		$expPercent = pow(1-$val,15);
		if($expPercent>1)
			$expPercent=1;
		else if($expPercent<0)
			$expPercent=0;
		return $expPercent;
	}
	
	public function getTradeHREF()
	{
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'shop_goods.php';
		return SmrSession::get_new_href($container);
	}
	
	public function getRaidWarningHREF()
	{
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'port_attack_warning.php';
		return SmrSession::get_new_href($container);
	}
	
	public function getAttackHREF()
	{
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'port_attack_processing.php';
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
	protected function setCachedTime($cachedTime)
	{
		return $this->cachedTime=$cachedTime;
	}
	public function addCachePort($accountID)
	{
		$this->addCachePorts(array($accountID));
	}
	public function addCachePorts(array $accountIDs)
	{
		if ($this->exists())
		{
			if(count($accountIDs)==0)
				throw new Exception('Cannot add cached port for no accounts');
			$cachedVersion = $this->cachedVersion;
			$this->getGoods();
			$goods = array();
			foreach($this->goods['All Goods'] as $key => &$good)
			{
				$goods[$key] = $good;
			} unset($good);
			$cache = $this->db->escapeBinary(gzcompress(serialize($this)));
			$cacheHash = $this->db->escapeString(md5($cache));
			//give them the port info
			$query = 'INSERT IGNORE INTO player_visited_port ' .
						'(account_id, game_id, sector_id, visited, port_info_hash) ' .
						'VALUES ';
			foreach($accountIDs as $accountID)
			{
				$query .= '('.$accountID.', '.$this->getGameID().', '.$this->getSectorID().', 0, \'\'),';
			}
			$query = substr($query,0,-1);
			$this->db->query($query);
			
			$this->db->query('INSERT IGNORE INTO port_info_cache ' .
						'(game_id, sector_id, port_info_hash, port_info) ' .
						'VALUES ('.$this->getGameID().', '.$this->getSectorID().', '.$cacheHash.', '.$cache.')');

			$this->db->query('UPDATE player_visited_port SET visited='.$this->getCachedTime().', port_info_hash='.$cacheHash.' WHERE visited<='.$this->getCachedTime().' AND account_id IN ('.$this->db->escapeArray($accountIDs).') AND sector_id='.$this->getSectorID().' AND game_id='.$this->getGameID().' LIMIT '.count($accountIDs));
			$this->cachedVersion = $cachedVersion; //Serializing this would have designated it as cached, this version might not be.

			foreach($goods as $key => &$good)
			{
				$this->goods['All Goods'][$key] =& $good;
			} unset($good); //Serializing this would have screwed up stock counts
			unset($cache);
		}
	}
	public static function &getCachedPort($gameID,$sectorID,$accountID,$forceUpdate=false)
	{
		if($forceUpdate || !isset(self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID]))
		{
			$db = new SmrMySqlDatabase();
			$db->query('SELECT visited, port_info FROM player_visited_port JOIN port_info_cache USING (game_id,sector_id,port_info_hash) ' .
							  'WHERE account_id = '.$accountID.' AND ' .
							  		'game_id = '.$gameID.' AND ' .
							  		'sector_id = '.$sectorID.' LIMIT 1');
			
			if($db->nextRecord())
			{
				self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID] = unserialize(gzuncompress($db->getField('port_info')));
				self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID]->setCachedTime($db->getInt('visited'));
			}
			else
				self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID] = false;
		}
		return self::$CACHE_CACHED_PORTS[$gameID][$sectorID][$accountID];
	}
	
	public function __sleep()
	{
		$this->getGoods(); //have to make sure goods have been loaded
		foreach($this->goods['All Goods'] as &$good)
		{
			$good['Amount'] = 1;
			unset($good['Distance']);
		} unset($good);
		$this->cachedVersion = true;
        return array('gameID', 'sectorID', 'raceID', 'level', 'cachedVersion', 'goods');
	}
	
	public function __wakeup()
	{
		$this->db = new SmrMySqlDatabase();
	}
	
	function update()
	{
		if(!$this->exists())
			return;
		if($this->hasChanged)
		{
			if($this->isNew===false)
			{
				$this->db->query('UPDATE port SET experience = '.$this->getExperience().', ' .
								'last_update = '.$this->getLastUpdate().', ' .
								'shields = '.$this->getShields().', ' .
								'armour = '.$this->getArmour().', ' .
								'combat_drones = '.$this->getCDs().', ' .
								'level = '.$this->getLevel().', ' .
								'credits = '.$this->getCredits().', ' .
								'upgrade = '.$this->getUpgrade().', ' .
								'reinforce_time = '.$this->getReinforceTime().', ' .
								'attack_started = '.$this->getAttackStarted().', ' .
								'race_id = '.$this->getRaceID().
							' WHERE game_id = '.$this->getGameID().' AND ' .
								'sector_id = '.$this->getSectorID().' LIMIT 1');
			}
			else
			{
				$this->db->query('INSERT INTO port (game_id,sector_id,experience,last_update,shields,armour,combat_drones,level,credits,upgrade,reinforce_time,attack_started,race_id)' .
								'values' .
								'('.$this->getGameID().
								','.$this->getSectorID() .
								','.$this->getExperience().
								','.$this->getLastUpdate().
								','.$this->getShields().
								','.$this->getArmour().
								','.$this->getCDs().
								','.$this->getLevel().
								','.$this->getCredits().
								','.$this->getUpgrade().
								','.$this->getReinforceTime().
								','.$this->getAttackStarted().
								','.$this->getRaceID().')');
				$this->isNew=false;
			}
			$this->hasChanged=false;
		}
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
		$results = array('Port' => &$this, 'TotalDamage' => 0, $results['TotalDamagePerTargetPlayer'] = array());
		foreach($targetPlayers as &$targetPlayer)
		{
			$results['TotalDamagePerTargetPlayer'][$targetPlayer->getAccountID()] = 0;
		} unset($targetPlayer);
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
			{
				$results['TotalDamage'] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
				$results['TotalDamagePerTargetPlayer'][$results['Weapons'][$orderID]['TargetPlayer']->getAccountID()] += $results['Weapons'][$orderID]['ActualDamage']['TotalDamage'];
			}
		}
		if($this->hasCDs())
		{
			$thisCDs = new SmrCombatDrones($this->getGameID(),$this->getCDs(),true);
			$results['Drones'] =& $thisCDs->shootPlayerAsPort($this, $targetPlayers[array_rand($targetPlayers)]);
			$results['TotalDamage'] += $results['Drones']['ActualDamage']['TotalDamage'];
			$results['TotalDamagePerTargetPlayer'][$results['Drones']['TargetPlayer']->getAccountID()] += $results['Drones']['ActualDamage']['TotalDamage'];
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
			if($damage['Shield'] || !$this->hasShields())
			{
				$shieldDamage = $this->doShieldDamage(min($damage['MaxDamage'],$damage['Shield']));
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
			else //hit drones behind shields
			{
				$cdDamage = $this->doCDDamage(floor(min($damage['MaxDamage'],$damage['Armour'])*DRONES_BEHIND_SHIELDS_DAMAGE_PERCENT));
			}
		}

		$return = array(
						'KillingShot' => !$alreadyDead && $this->isDestroyed(),
						'TargetAlreadyDead' => $alreadyDead,
						'Shield' => $shieldDamage,
						'HasShields' => $this->hasShields(),
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
			$currPlayer->increaseHOF($this->db->getField('level'),array('Combat','Port','Levels Raided'), HOF_PUBLIC);
			$currPlayer->increaseHOF(1,array('Combat','Port','Total Raided'), HOF_PUBLIC);
		}
		$this->db->query('DELETE FROM player_attacks_port WHERE sector_id = '.$this->getSectorID().' AND game_id = '.$this->getGameID().' LIMIT '.$numToDelete);
	}
	
	public function &killPortByPlayer(AbstractSmrPlayer &$killer)
	{
		$return = array();
		$return['KillerCredits'] = $this->getCredits();
		$killer->increaseCredits($return['KillerCredits']);
		$killer->increaseHOF($return['KillerCredits'],array('Combat','Port','Money','Gained'), HOF_PUBLIC);
		$this->setCredits(0);
		
		$this->creditCurrentAttackersForKill();
		
		// News Entry
		$news = $this->getDisplayName() . ' has been successfully raided by ';
		if ($killer->hasAlliance()) $news .= 'the members of <span class="yellow">' . $killer->getAllianceBBLink() . '</span>';
		else $news .= $killer->getBBLink();
		$this->db->query('INSERT INTO news (game_id, time, news_message, type,killer_id,killer_alliance,dead_id) VALUES ('.$this->getGameID().', ' . TIME . ', '.$this->db->escapeString($news).', \'REGULAR\','.$killer->getAccountID().','.$killer->getAllianceID().','.ACCOUNT_ID_PORT.')');
		// Trigger gets an alignment change and a bounty if port is taken
		$return['KillerBounty'] = $killer->getExperience() * $this->getLevel();
		$killer->increaseCurrentBountyAmount('HQ',$return['KillerBounty']);
		$killer->increaseHOF($return['KillerBounty'],array('Combat','Port','Bounties','Gained'), HOF_PUBLIC);
		
		if($this->getRaceID() > 1)
		{
			$return['KillerRelations'] = 45;
			$killer->decreaseRelations($return['KillerRelations'],$this->getRaceID());
			$killer->increaseHOF($return['KillerRelations'],array('Combat','Port','Relation','Loss'), HOF_PUBLIC);
		}
		// also we change alignment
		$relations = Globals::getRaceRelations($killer->getGameID(),$killer->getRaceID());
		if ($relations[$this->getRaceID()] < -299)
		{
			$return['KillerAlign'] = $this->getLevel() * 2;
			$killer->increaseAlignment($return['KillerAlign']);
			$killer->increaseHOF($return['KillerAlign'],array('Combat','Port','Alignment','Gain'), HOF_PUBLIC);
		}
		else
		{
			$return['KillerAlign'] = -$this->getLevel() * 2;
			$killer->decreaseAlignment(-$return['KillerAlign']);
			$killer->increaseHOF(-$return['KillerAlign'],array('Combat','Port','Alignment','Loss'), HOF_PUBLIC);
		}
		
		return $return;
	}

	public function hasX(/*Object*/ &$x)
	{
		if (is_array($x) && $x['Type']=='Good') // instanceof Good) - No Good class yet, so array is best we can do
		{
			if(isset($x['ID']))
				return $this->hasGood($x['ID'],isset($x['TransactionType'])?$x['TransactionType']:false);
		}
		return false;
	}
}
?>