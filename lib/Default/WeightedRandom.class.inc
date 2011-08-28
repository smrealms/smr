<?php

class WeightedRandom
{
	protected static $CACHE_RANDOMS = array();
	
	const WEIGHTING_CHANGE = 50;
	
	protected $db;
	
	protected $gameID;
	protected $accountID;
	protected $type;
	protected $typeID;
	protected $weighting;
	
	protected $hasChanged=false;

	public static function &getWeightedRandom($gameID, $accountID, $type, $typeID, $forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_RANDOMS[$gameID][$accountID][$type][$typeID]))
		{
			self::$CACHE_RANDOMS[$gameID][$accountID][$type][$typeID] = new Random($gameID, $accountID, $type, $typeID);
		}
		return self::$CACHE_RANDOMS[$gameID][$accountID][$type][$typeID];
	}

	public static function &getWeightedRandomForPlayer(AbstractSmrPlayer &$player, $type, $typeID, $forceUpdate = false)
	{
		return self::getWeightedRandom($player->getGameID(), $player->getAccountID(), $type, $typeID, $forceUpdate);
	}
	
	public static function saveWeightedRandoms()
	{
		foreach(self::$CACHE_RANDOMS as &$gameRandoms)
		{
			foreach($gameRandoms as &$accountRandoms)
			{
				foreach($accountRandoms as &$typeRandoms)
				{
					foreach($typeRandoms as &$random)
					{
						$random->update();
					}
				}
			}
		}
	}
	
	protected function __construct($gameID, $accountID, $type, $typeID)
	{
		$this->gameID = $gameID;
		$this->accountID = $accountID;
		$this->type = $type;
		$this->typeID = $typeID;
		
		$this->db = new SmrMySqlDatabase();
		$this->db->query('SELECT weighting FROM weighted_random WHERE game_id = '.$this->db->escapeNumber($gameID).' AND account_id = '.$this->db->escapeNumber($accountID).' AND type = '.$this->db->escapeNumber($type).' AND type_id = '.$this->db->escapeNumber($typeID).' LIMIT 1');
		if ($this->db->nextRecord())
		{
			$this->weighting = $this->db->getField('weighting');
		}
		else
		{
			$this->weighting = 0;
		}
	}
	
	public function getGameID()
	{
		return $this->gameID;
	}
	
	public function getAccountID()
	{
		return $this->accountID;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function getTypeID()
	{
		return $this->typeID;
	}
	
	public function getWeighting()
	{
		return $this->weighting;
	}
	
	public function percentFailed($percent)
	{
		$failed = mt_rand(0,99)+$this->weighting >= $percent;
		$this->adjustWeightByPercent(!$failed, $percent);
		return $failed;
	}
	
	public function adjustWeightByPercent($success, $percent)
	{
		$percent = $percent/100;
		if($success)
		{
			$percent = -1 + $percent;
		}
		$this->weighting -= WEIGHTING_CHANGE*$percent;
		$this->hasChanged = true;
	}
	
	public function update()
	{
		if($this->hasChanged===true)
		{
			$this->db->query('REPLACE INTO port (game_id,account_id,type,type_id,weighting)' .
							'values' .
							'('.$this->getGameID().
							','.$this->getAccountID() .
							','.$this->getType() .
							','.$this->getTypeID() .
							','.$this->getWeighting().')');
			$this->hasChanged=false;
		}
    }
}
?>