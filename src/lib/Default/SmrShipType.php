<?php declare(strict_types=1);

/**
 * Defines the base ship types
 */
class SmrShipType {

	use Traits\RaceID;

	private static array $CACHE_SHIP_TYPES = [];

	private readonly string $name;
	private readonly int $typeID;
	private readonly int $classID;
	private readonly int $hardpoints;
	private readonly int $speed;
	private readonly int $cost;
	private readonly int $restriction;
	private readonly int $levelNeeded;

	private readonly int $maxPower;
	private readonly array $maxHardware;
	private readonly int $baseManeuverability;

	public static function clearCache(): void {
		self::$CACHE_SHIP_TYPES = [];
	}

	public static function get(int $shipTypeID, Smr\DatabaseRecord $dbRecord = null): self {
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

	public static function getAll(): array {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT * FROM ship_type ORDER BY ship_type_id ASC');
		foreach ($dbResult->records() as $dbRecord) {
			// populate the cache
			self::get($dbRecord->getInt('ship_type_id'), $dbRecord);
		}
		return self::$CACHE_SHIP_TYPES;
	}

	protected function __construct(Smr\DatabaseRecord $dbRecord) {
		$this->name = $dbRecord->getString('ship_name');
		$this->typeID = $dbRecord->getInt('ship_type_id');
		$this->classID = $dbRecord->getInt('ship_class_id');
		$this->raceID = $dbRecord->getInt('race_id');
		$this->hardpoints = $dbRecord->getInt('hardpoint');
		$this->speed = $dbRecord->getInt('speed');
		$this->cost = $dbRecord->getInt('cost');
		$this->restriction = $dbRecord->getInt('buyer_restriction');
		$this->levelNeeded = $dbRecord->getInt('lvl_needed');

		// Power is calculated by summing the allotment for each hardpoint.
		// P5x1, P4x2, P3x3, P2x4, P1x(infinity)
		$this->maxPower = match ($this->hardpoints) {
			0 => 0,
			1 => 5,
			2 => 9, // 5+4
			3 => 13, // 5+4+4
			4 => 16, // 5+4+4+3
			5 => 19, // 5+4+4+3+3
			6 => 22, // 5+4+4+3+3+3
			7 => 24, // 5+4+4+3+3+3+2
			8 => 26, // 5+4+4+3+3+3+2+2
			9 => 28, // 5+4+4+3+3+3+2+2+2
			10 => 30, // 5+4+4+3+3+3+2+2+2+2
			default => 30 + ($this->hardpoints - 10),
		};

		// get supported hardware from db
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT hardware_type_id, max_amount FROM ship_type_support_hardware ' .
			'WHERE ship_type_id = ' . $db->escapeNumber($this->typeID) . ' ORDER BY hardware_type_id');

		$maxHardware = [];
		foreach ($dbResult->records() as $dbRecord2) {
			// adding hardware to array
			$maxHardware[$dbRecord2->getInt('hardware_type_id')] = $dbRecord2->getInt('max_amount');
		}
		$this->maxHardware = $maxHardware;

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

	public function getTypeID(): int {
		return $this->typeID;
	}

	public function getClassID(): int {
		return $this->classID;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getCost(): int {
		return $this->cost;
	}

	public function getRestriction(): int {
		return $this->restriction;
	}

	/**
	 * Return the base ship speed (unmodified by the game speed)
	 */
	public function getSpeed(): int {
		return $this->speed;
	}

	public function getHardpoints(): int {
		return $this->hardpoints;
	}

	/**
	 * Return the maximum weapon power
	 */
	public function getMaxPower(): int {
		return $this->maxPower;
	}

	public function getMaxHardware(int $hardwareTypeID): int {
		return $this->maxHardware[$hardwareTypeID];
	}

	public function getAllMaxHardware(): array {
		return $this->maxHardware;
	}

	public function canHaveJump(): bool {
		return $this->getMaxHardware(HARDWARE_JUMP) > 0;
	}

	public function canHaveDCS(): bool {
		return $this->getMaxHardware(HARDWARE_DCS) > 0;
	}

	public function canHaveScanner(): bool {
		return $this->getMaxHardware(HARDWARE_SCANNER) > 0;
	}

	public function canHaveCloak(): bool {
		return $this->getMaxHardware(HARDWARE_CLOAK) > 0;
	}

	public function canHaveIllusion(): bool {
		return $this->getMaxHardware(HARDWARE_ILLUSION) > 0;
	}

}
