<?php declare(strict_types=1);

/**
 * Defines the base ship types
 */
class SmrShipType {
	use Traits\RaceID;

	private static array $CACHE_SHIP_TYPES = [];

	private string $name;
	private int $typeID;
	private int $classID;
	private int $hardpoints;
	private int $speed;
	private int $cost;
	private int $restriction;
	private int $levelNeeded;

	private int $maxPower = 0;
	private array $maxHardware = [];
	private int $baseManeuverability;

	public static function get(int $shipTypeID, Smr\Database $db = null) : self {
		if (!isset(self::$CACHE_SHIP_TYPES[$shipTypeID])) {
			if ($db === null) {
				$db = Smr\Database::getInstance();
				$db->query('SELECT * FROM ship_type WHERE ship_type_id = ' . $db->escapeNumber($shipTypeID));
				$db->requireRecord();
			} elseif ($shipTypeID !== $db->getInt('ship_type_id')) {
				throw new Exception('Database result mismatch');
			}
			self::$CACHE_SHIP_TYPES[$shipTypeID] = new self($db);
		}
		return self::$CACHE_SHIP_TYPES[$shipTypeID];
	}

	public static function getAll() : array {
		$db = Smr\Database::getInstance();
		$db->query('SELECT * FROM ship_type ORDER BY ship_type_id ASC');
		while ($db->nextRecord()) {
			// populate the cache
			self::get($db->getInt('ship_type_id'), $db);
		}
		return self::$CACHE_SHIP_TYPES;
	}

	protected function __construct(Smr\Database $db) {
		$this->name = $db->getField('ship_name');
		$this->typeID = $db->getInt('ship_type_id');
		$this->classID = $db->getInt('ship_class_id');
		$this->raceID = $db->getInt('race_id');
		$this->hardpoints = $db->getInt('hardpoint');
		$this->speed = $db->getInt('speed');
		$this->cost = $db->getInt('cost');
		$this->restriction = $db->getInt('buyer_restriction');
		$this->levelNeeded = $db->getInt('lvl_needed');

		$maxPower = 0;
		switch ($this->hardpoints) {
			default:
				$maxPower += 1 * $this->hardpoints - 10;
			case 10:
				$maxPower += 2;
			case 9:
				$maxPower += 2;
			case 8:
				$maxPower += 2;
			case 7:
				$maxPower += 2;
			case 6:
				$maxPower += 3;
			case 5:
				$maxPower += 3;
			case 4:
				$maxPower += 3;
			case 3:
				$maxPower += 4;
			case 2:
				$maxPower += 4;
			case 1:
				$maxPower += 5;
			case 0:
				$maxPower += 0;
		}
		$this->maxPower = $maxPower;


		// get supported hardware from db
		$db2 = Smr\Database::getInstance();
		$db2->query('SELECT hardware_type_id, max_amount FROM ship_type_support_hardware ' .
			'WHERE ship_type_id = ' . $db2->escapeNumber($this->typeID) . ' ORDER BY hardware_type_id');

		while ($db2->nextRecord()) {
			// adding hardware to array
			$this->maxHardware[$db2->getInt('hardware_type_id')] = $db2->getInt('max_amount');
		}

		$this->baseManeuverability = IRound(
								700 -
								(
									(
										$this->maxHardware[HARDWARE_SHIELDS]
										+ $this->maxHardware[HARDWARE_ARMOUR]
										+ $this->maxHardware[HARDWARE_COMBAT] * 3
									) / 25
									+ $this->maxHardware[HARDWARE_CARGO] / 100
									- $this->speed * 5
									+ $this->hardpoints * 5
									+ $this->maxHardware[HARDWARE_COMBAT] / 5
								)
							);
	}

	public function getTypeID() : int {
		return $this->typeID;
	}

	public function getClassID() : int {
		return $this->classID;
	}

	public function getName() : string {
		return $this->name;
	}

	public function getCost() : int {
		return $this->cost;
	}

	public function getRestriction() : int {
		return $this->restriction;
	}

	/**
	 * Return the base ship speed (unmodified by the game speed)
	 */
	public function getSpeed() : int {
		return $this->speed;
	}

	public function getHardpoints() : int {
		return $this->hardpoints;
	}

	/**
	 * Return the maximum weapon power
	 */
	public function getMaxPower() : int {
		return $this->maxPower;
	}

	public function getMaxHardware(int $hardwareTypeID) : int {
		return $this->maxHardware[$hardwareTypeID];
	}

	public function getAllMaxHardware() : array {
		return $this->maxHardware;
	}

	public function canHaveJump() : bool {
		return $this->getMaxHardware(HARDWARE_JUMP) > 0;
	}

	public function canHaveDCS() : bool {
		return $this->getMaxHardware(HARDWARE_DCS) > 0;
	}

	public function canHaveScanner() : bool {
		return $this->getMaxHardware(HARDWARE_SCANNER) > 0;
	}

	public function canHaveCloak() : bool {
		return $this->getMaxHardware(HARDWARE_CLOAK) > 0;
	}

	public function canHaveIllusion() : bool {
		return $this->getMaxHardware(HARDWARE_ILLUSION) > 0;
	}

}
