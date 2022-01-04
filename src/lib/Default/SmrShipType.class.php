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

	public static function get(int $shipTypeID, Smr\DatabaseRecord $dbRecord = null) : self {
		if (!isset(self::$CACHE_SHIP_TYPES[$shipTypeID])) {
			if ($dbRecord === null) {
				$db = Smr\Database::getInstance();
				$dbResult = $db->read('SELECT * FROM ship_type WHERE ship_type_id = ' . $db->escapeNumber($shipTypeID));
				$dbRecord = $dbResult->record();
			} elseif ($shipTypeID !== $dbRecord->getInt('ship_type_id')) {
				throw new Exception('Database result mismatch');
			}
			self::$CACHE_SHIP_TYPES[$shipTypeID] = new self($dbRecord);
		}
		return self::$CACHE_SHIP_TYPES[$shipTypeID];
	}

	public static function getAll() : array {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT * FROM ship_type ORDER BY ship_type_id ASC');
		foreach ($dbResult->records() as $dbRecord) {
			// populate the cache
			self::get($dbRecord->getInt('ship_type_id'), $dbRecord);
		}
		return self::$CACHE_SHIP_TYPES;
	}

	protected function __construct(Smr\DatabaseRecord $dbRecord) {
		$this->name = $dbRecord->getField('ship_name');
		$this->typeID = $dbRecord->getInt('ship_type_id');
		$this->classID = $dbRecord->getInt('ship_class_id');
		$this->raceID = $dbRecord->getInt('race_id');
		$this->hardpoints = $dbRecord->getInt('hardpoint');
		$this->speed = $dbRecord->getInt('speed');
		$this->cost = $dbRecord->getInt('cost');
		$this->restriction = $dbRecord->getInt('buyer_restriction');
		$this->levelNeeded = $dbRecord->getInt('lvl_needed');

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
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT hardware_type_id, max_amount FROM ship_type_support_hardware ' .
			'WHERE ship_type_id = ' . $db->escapeNumber($this->typeID) . ' ORDER BY hardware_type_id');

		foreach ($dbResult->records() as $dbRecord2) {
			// adding hardware to array
			$this->maxHardware[$dbRecord2->getInt('hardware_type_id')] = $dbRecord2->getInt('max_amount');
		}

		$this->baseManeuverability = IRound(
								700 -
								(
									(
										$this->maxHardware[HARDWARE_SHIELDS]
										+ $this->maxHardware[HARDWARE_ARMOUR]
										+ $this->maxHardware[HARDWARE_COMBAT] * CD_ARMOUR
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
