<?
require_once('SmrMySqlDatabase.class.inc');

require_once('SmrGalaxy.class.inc');
class SmrGame
{
	protected static $CACHE_GAMES = array();
	
	protected $db;

	protected $gameID;
	protected $name;
	protected $description;
	protected $startDate;
	protected $endDate;
	protected $maxPlayers;
	protected $gameType;
	protected $creditsNeeded;
	protected $gameSpeed;
	protected $enabled;
	
	protected $hasChanged = false;
	protected $isNew = false;

	public static function &getGame($gameID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_GAMES[$gameID]))
		{
			$g = new SmrGame($gameID);
			self::$CACHE_GAMES[$gameID] =& $g;
		}
		return self::$CACHE_GAMES[$gameID];
	}
	
	public static function saveGames()
	{
		foreach(self::$CACHE_GAMES as &$game)
		{
			$game->save();
		}
	}
	
	public static function &createGame($gameID)
	{
		if(!isset(self::$CACHE_GAMES[$gameID]))
		{
			$g = new SmrGame($gameID,true);
			self::$CACHE_GAMES[$gameID] =& $g;
		}
		return self::$CACHE_GAMES[$gameID];
	}
	
	protected function __construct($gameID, $create=false)
	{
		$this->db = new SmrMySqlDatabase();

		$this->db->query('SELECT * FROM game WHERE game_id = '.$gameID.' LIMIT 1');
		if($this->db->nextRecord())
		{
			$this->gameID		= (int)$this->db->getField('game_id');
			$this->name			= $this->db->getField('game_name');
			$this->description	= $this->db->getField('game_description');
			$this->startDate	= (int)$this->db->getField('start_date');
			$this->endDate		= (int)$this->db->getField('end_date');
			$this->maxPlayers	= (int)$this->db->getField('max_players');
			$this->gameType		= $this->db->getField('game_type');
			$this->creditsNeeded= (int)$this->db->getField('credits_needed');
			$this->gameSpeed	= (int)$this->db->getField('game_speed');
			$this->enabled		= $this->db->getField('enabled')=='TRUE';
		}
		else if($create===true)
		{
			$this->gameID		= (int)$gameID;
			$this->isNew		= true;
			return;
		}
		else
		{
			throw new Exception('No such game: '.$gameID);
		}
	}

	public function save()
	{
		if($this->hasChanged)
		{
			if($this->isNew==false)
			{
				$this->db->query('UPDATE game SET game_name = '.$this->db->escapeString($this->getName()).
										' game_description = '.$this->db->escapeString($this->getDescription()).
										' start_date = '.$this->getStartDate().
										' end_date = '.$this->getEndDate().
										' max_players = '.$this->getMaxPlayers().
										' game_type = '.$this->db->escapeString($this->getGameType()).
										' credits_needed = '.$this->getCreditsNeeded().
										' game_speed = '.$this->getGameSpeed().
										' enabled = '.$this->escapeBoolean($this->getEnabled()).
								   ' WHERE game_id = '.$this->getGameID().' LIMIT 1');
			}
			else
			{
				$this->db->query('INSERT INTO game (game_id,game_name,game_description,start_date,end_date,max_players,game_type,credits_needed,game_speed,enabled)'.
									'values'.
									'('.$this->getGameID().
										','.$this->db->escapeString($this->getName()).
										','.$this->db->escapeString($this->getDescription()).
										','.$this->getStartDate().
										','.$this->getEndDate().
										','.$this->getMaxPlayers().
										','.$this->db->escapeString($this->getGameType()).
										','.$this->getCreditsNeeded().
										','.$this->getGameSpeed().
										','.$this->escapeBoolean($this->getEnabled()). ')');
			}
		}
	}
	
	public function getGameID()
	{
		return $this->gameID;
	}
	
	public function getName()
	{
		return $this->name;
	}
	
	public function setName($name)
	{
		if($this->name==$name)
			return;
		$this->name=$name;
		$this->hasChanged=true;
	}
	
	public function getDescription()
	{
		return $this->description;
	}
	
	public function setDescription($description)
	{
		if($this->description==$description)
			return;
		$this->description=$description;
		$this->hasChanged=true;
	}
	
	public function getStartDate()
	{
		return $this->startDate;
	}
	
	public function setStartDate($startDate)
	{
		if($this->startDate==$startDate)
			return;
		$this->startDate=$startDate;
		$this->hasChanged=true;
	}
	
	public function getEndDate()
	{
		return $this->endDate;
	}
	
	public function setEndDate($endDate)
	{
		if($this->endDate==$endDate)
			return;
		$this->endDate=$endDate;
		$this->hasChanged=true;
	}
	
	public function getMaxPlayers()
	{
		return $this->maxPlayers;
	}
	
	public function setMaxPlayers($maxPlayers)
	{
		if($this->maxPlayers==$maxPlayers)
			return;
		$this->maxPlayers=$maxPlayers;
		$this->hasChanged=true;
	}
	
	public function getGameType()
	{
		return $this->gameType;
	}
	
	public function setGameType($gameType)
	{
		if($this->gameType==$gameType)
			return;
		$this->gameType=$gameType;
		$this->hasChanged=true;
	}
	
	public function getCreditsNeeded()
	{
		return $this->creditsNeeded;
	}
	
	public function setCreditsNeeded($creditsNeeded)
	{
		if($this->creditsNeeded==$creditsNeeded)
			return;
		$this->creditsNeeded=$creditsNeeded;
		$this->hasChanged=true;
	}
	
	public function getGameSpeed()
	{
		return $this->gameSpeed;
	}
	
	public function setGameSpeed($gameSpeed)
	{
		if($this->gameSpeed==$gameSpeed)
			return;
		$this->gameSpeed=$gameSpeed;
		$this->hasChanged=true;
	}
	
	public function getEnabled()
	{
		return $this->gameSpeed;
	}
	
	public function setEnabled($bool)
	{
		if($this->enabled==$bool)
			return;
		$this->enabled=$bool;
		$this->hasChanged=true;
	}
	
	public function getNumberOfGalaxies()
	{
		return count(SmrGalaxy::getGameGalaxies($this->getGameID()));
	}
	
	function equals(SmrGame $otherGame)
	{
		return $otherGame->getGameID()==$this->getGameID();
	}
}

?>