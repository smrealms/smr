<?php declare(strict_types=1);

/**
 * Weighted random number generator used to make events achieve their expected
 * rate of success faster than a pure random number generator.
 *
 * The weighting is added to a random number (higher weight means that success
 * is less likely). Therefore, when the weighted coin flip is successful, the
 * weight is increased so that future events are less likely; similarly, when
 * the weighted coin flip is unsuccessful, the weight is decreased so that
 * future events are more likely.
 */
class WeightedRandom {
	protected static $CACHE_RANDOMS = array();
	
	const WEIGHTING_CHANGE = 50; // as a percent
	
	protected $db;
	
	protected $gameID;
	protected $accountID;
	protected $type;
	protected $typeID;
	protected $weighting;
	
	protected $hasChanged = false;

	public static function &getWeightedRandom($gameID, $accountID, $type, $typeID, $forceUpdate = false) {
		if ($forceUpdate || !isset(self::$CACHE_RANDOMS[$gameID][$accountID][$type][$typeID])) {
			self::$CACHE_RANDOMS[$gameID][$accountID][$type][$typeID] = new WeightedRandom($gameID, $accountID, $type, $typeID);
		}
		return self::$CACHE_RANDOMS[$gameID][$accountID][$type][$typeID];
	}

	public static function &getWeightedRandomForPlayer(AbstractSmrPlayer $player, $type, $typeID, $forceUpdate = false) {
		return self::getWeightedRandom($player->getGameID(), $player->getAccountID(), $type, $typeID, $forceUpdate);
	}
	
	public static function saveWeightedRandoms() {
		foreach (self::$CACHE_RANDOMS as $gameRandoms) {
			foreach ($gameRandoms as $accountRandoms) {
				foreach ($accountRandoms as $typeRandoms) {
					foreach ($typeRandoms as $random) {
						$random->update();
					}
				}
			}
		}
	}
	
	protected function __construct($gameID, $accountID, $type, $typeID) {
		$this->gameID = $gameID;
		$this->accountID = $accountID;
		$this->type = $type;
		$this->typeID = $typeID;
		
		$this->db = new SmrMySqlDatabase();
		$this->db->query('SELECT weighting FROM weighted_random WHERE game_id = ' . $this->db->escapeNumber($gameID) . ' AND account_id = ' . $this->db->escapeNumber($accountID) . ' AND type = ' . $this->db->escapeString($type) . ' AND type_id = ' . $this->db->escapeNumber($typeID) . ' LIMIT 1');
		if ($this->db->nextRecord()) {
			$this->weighting = $this->db->getInt('weighting');
		} else {
			$this->weighting = 0;
		}
	}
	
	public function getGameID() {
		return $this->gameID;
	}
	
	public function getAccountID() {
		return $this->accountID;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function getTypeID() {
		return $this->typeID;
	}
	
	public function getWeighting() {
		return $this->weighting;
	}

	/**
	 * Given $successChance as the base percent chance that an event happens,
	 * reduce that chance by the current weighting, and then check the result.
	 */
	public function flipWeightedCoin($successChance) : bool {
		// The weighting update formulas below only work in the range [0, 100].
		$successChance = min(100, max(0, $successChance));

		// Check if the event was successful.
		$success = mt_rand(1, 100) + $this->weighting <= $successChance;

		// Now update the weighting (the extra factor is needed to achieve the
		// base success chance on average).
		if ($success) {
			$weightChangeFactor = (100 - $successChance) / 100;
		} else {
			$weightChangeFactor = -$successChance / 100;
		}
		$this->weighting += self::WEIGHTING_CHANGE * $weightChangeFactor;
		$this->hasChanged = true;
		return $success;
	}
	
	public function update() {
		if ($this->hasChanged === true) {
			$this->db->query('REPLACE INTO weighted_random (game_id,account_id,type,type_id,weighting)
							values
							(' . $this->db->escapeNumber($this->getGameID()) .
							',' . $this->db->escapeNumber($this->getAccountID()) .
							',' . $this->db->escapeString($this->getType()) .
							',' . $this->db->escapeNumber($this->getTypeID()) .
							',' . $this->db->escapeNumber($this->getWeighting()) . ')');
			$this->hasChanged = false;
		}
	}
}
