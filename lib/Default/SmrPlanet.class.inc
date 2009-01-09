<?
require_once('SmrMySqlDatabase.class.inc');

class SmrPlanet
{
	protected static $CACHE_PLANETS = array();
	
	protected $db;
	
	var $sector_id;
	var $game_id;
	var $planet_name;
	var $owner_id;
	var $password;
	var $shields;
	var $drones;
	var $credits;
	var $bonds;
	var $maturity;
	var $stockpile;
	var $construction;
	var $max_construction;
	var $inhabitable_time;
	var $level;
	var $current_building_id;
	var $current_building_name;
	var $time_left;
	
	protected $examineHREF;

	public static function &getPlanet($gameID,$sectorID,$forceUpdate = false)
	{
		if($forceUpdate || !isset(self::$CACHE_PLANETS[$gameID . ':' . $sectorID]))
		{
			$p = new SmrPlanet($gameID,$sectorID);
			return self::$CACHE_PLANETS[$gameID . ':' . $sectorID] =& $p;
		}
		return self::$CACHE_PLANETS[$gameID . ':' . $sectorID];
	}

	protected function __construct($game_id,$curr_sector_id)
	{
		$this->db = new SmrMySqlDatabase();


		$this->db->query('SELECT * FROM planet WHERE game_id = '.$game_id.' AND ' .
												'sector_id = '.$curr_sector_id);
		if ($this->db->nextRecord())
		{
			$this->sector_id		= $this->db->getField('sector_id');
			$this->game_id			= $this->db->getField('game_id');
			$this->planet_name		= stripslashes($this->db->getField('planet_name'));
			$this->owner_id			= $this->db->getField('owner_id');
			$this->password			= $this->db->getField('password');
			$this->shields			= $this->db->getField('shields');
			$this->drones			= $this->db->getField('drones');
			$this->credits			= $this->db->getField('credits');
			$this->bonds			= $this->db->getField('bonds');
			$this->maturity			= $this->db->getField('maturity');
			$this->inhabitable_time = $this->db->getField('inhabitable_time');

			// initialize cargo array
			$this->stockpile = array();
			$this->level = $this->level();
			// get supplies from db
			$this->db->query('SELECT * FROM planet_has_cargo, good WHERE planet_has_cargo.good_id = good.good_id AND ' .
																	'game_id = '.$this->game_id.' AND ' .
																	'sector_id = '.$this->sector_id);
			// adding cargo and amount to array
			while ($this->db->nextRecord())
				$this->stockpile[$this->db->getField('good_id')] = $this->db->getField('amount');
	
			// initialize empty cargo
			$this->db->query('SELECT * FROM good');
			while ($this->db->nextRecord()) {
	
				$good_id = $this->db->getField('good_id');
				if (empty($this->stockpile[$good_id]))
					$this->stockpile[$good_id] = 0;
	
			}
	
			$this->construction = array();
	
			// get constructions from db
			$this->db->query('SELECT * FROM planet_has_construction WHERE game_id = '.$this->game_id.' AND ' .
																	 'sector_id = '.$this->sector_id);
			// adding building and amount to array
			while ($this->db->nextRecord())
				$this->construction[$this->db->getField('construction_id')] = $this->db->getField('amount');
	
			$this->max_construction = array();
	
			// get constructions from db
			$this->db->query('SELECT * FROM planet_construction');
			while ($this->db->nextRecord()) {
	
				$construction_id	= $this->db->getField('construction_id');
				$max_construction	= $this->db->getField('max_construction');
	
				$this->max_construction[$construction_id] = $max_construction;
	
				if (empty($this->max_construction[$construction_id]))
					$this->max_construction[$construction_id] = 0;
				if (empty($this->construction[$construction_id]))
				$this->construction[$construction_id] = 0;

			}

		}
	}
	
	public function getGameID()
	{
		return $this->game_id;
	}
	
	public function getSectorID()
	{
		return $this->sector_id;
	}
	
	public function exists()
	{
		return $this->getGameID()!=null && $this->getSectorID()!==null;
	}

	function update() {

		$this->db->query('UPDATE planet SET owner_id = '.$this->owner_id.', ' .
										'password = '.$this->db->escapeString($this->password).', ' .
										'planet_name = ' . $this->db->escapeString($this->planet_name) . ', ' .
										'shields = '.$this->shields.', ' .
										'drones = '.$this->drones.', ' .
										'credits = '.$this->credits.', ' .
										'bonds = '.$this->bonds.', ' .
										'maturity = '.$this->maturity.' ' .
									'WHERE game_id = '.$this->game_id.' AND ' .
										  'sector_id = '.$this->sector_id);

		// write stockpile info
		foreach ($this->stockpile as $id => $amount)
		{

			if ($amount != 0)
				$this->db->query('REPLACE INTO planet_has_cargo (game_id, sector_id, good_id, amount) ' .
													 'VALUES('.$this->game_id.', '.$this->sector_id.', '.$id.', '.$amount.')');
			else
				$this->db->query('DELETE FROM planet_has_cargo WHERE game_id = '.$this->game_id.' AND ' .
																'sector_id = '.$this->sector_id.' AND ' .
																'good_id = '.$id);
		}

		// write building info
		foreach ($this->construction as $id => $amount) {

			if ($amount != 0)
				$this->db->query('REPLACE INTO planet_has_construction (game_id, sector_id, construction_id, amount) ' .
															'VALUES('.$this->game_id.', '.$this->sector_id.', '.$id.', '.$amount.')');
			else
				$this->db->query('DELETE FROM planet_has_construction WHERE game_id = '.$this->game_id.' AND ' .
																	   'sector_id = '.$this->sector_id.' AND ' .
																	   'construction_id = '.$id);
		}
	}

	function level() {

		return number_format(($this->construction[1] +
							  $this->construction[2] +
							  $this->construction[3]) / 3, 2);

	}

	function accuracy() {

		return round(25 + $this->level());

	}

	function stockpile_left($id) {

		return 600 - $this->stockpile[$id];

	}

	/**
	 * Returns true if there is a construction in building
	 */
	function build() {

		$this->db->query('SELECT * FROM planet_build_construction, planet_construction ' .
							 'WHERE planet_build_construction.construction_id = planet_construction.construction_id AND ' .
							 	   'game_id = '.$this->game_id.' AND ' .
								   'sector_id = '.$this->sector_id);
		if ($this->db->nextRecord()) {

			$this->current_building_id		= $this->db->getField('construction_id');
			$this->current_building_name	= $this->db->getField('construction_name');
			$this->time_left				= $this->db->getField('time_complete') - time();

			// is the construction ready
			if ($this->time_left < 0) {

				$this->db->query('DELETE FROM planet_build_construction WHERE game_id = '.$this->game_id.' AND ' .
																		 'sector_id = '.$this->sector_id);
				$this->construction[$this->current_building_id] += 1;
				$this->update();
				return false;

			}

			return true;

		} else
			return false;

	}

	function start_construction($construction_id) {

		// take the goods that are needed
		$this->db->query('SELECT * FROM planet_cost_good, good ' .
							  'WHERE planet_cost_good.good_id = good.good_id AND ' .
									'construction_id = '.$construction_id.' ' .
							  'ORDER BY good.good_id');
		while ($this->db->nextRecord()) {

			$good_id	= $this->db->getField('good_id');
			$amount		= $this->db->getField('amount');

			$this->stockpile[$good_id] -= $amount;
		}

		$this->update();

		// get game speed
		$this->db->query('SELECT * FROM game WHERE game_id = '.$this->game_id);
		if ($this->db->nextRecord())
			$game_speed = $this->db->getField('game_speed');

		// gets the time for the construction
		$this->db->query('SELECT * FROM planet_cost_time WHERE construction_id = '.$construction_id);
		if ($this->db->nextRecord())
			$time_complete = time() + round($this->db->getField('amount') / $game_speed);

		$this->db->query('INSERT INTO planet_build_construction (game_id, sector_id, construction_id, time_complete) ' .
													'VALUES ('.$this->game_id.', '.$this->sector_id.', '.$construction_id.', '.$time_complete.')');

	}
	
	public function getName()
	{
		return $this->planet_name;
	}
	
	public function isInhabitable()
	{
		return $this->inhabitable_time<= TIME;
	}
	
	public function isClaimed()
	{
		return $this->owner_id>0;
	}
	
	public function getExamineHREF()
	{
		if(!isset($this->examineHREF))
		{
			$container = array();
			$container['url'] = 'skeleton.php';
			$container['body'] = 'planet_examine.php';
			$this->examineHREF = SmrSession::get_new_href($container);
		}
		return $this->examineHREF;
	}
}

?>