<?php declare(strict_types=1);

namespace Smr;

class HardwareType {

	/** @var array<int, self> */
	private static array $CACHE_HARDWARE_TYPES = [];

	public static function clearCache(): void {
		self::$CACHE_HARDWARE_TYPES = [];
	}

	/**
	 * @return array<int, self>
	 */
	public static function getAll(): array {
		if (empty(self::$CACHE_HARDWARE_TYPES)) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM hardware_type');
			foreach ($dbResult->records() as $dbRecord) {
				$typeID = $dbRecord->getInt('hardware_type_id');
				self::$CACHE_HARDWARE_TYPES[$typeID] = new self(
					typeID: $typeID,
					name: $dbRecord->getString('hardware_name'),
					cost: $dbRecord->getInt('cost'),
				);
			}
		}
		return self::$CACHE_HARDWARE_TYPES;
	}

	public static function get(int $hardwareTypeID): self {
		return self::getAll()[$hardwareTypeID];
	}

	public function __construct(
		public readonly int $typeID,
		public readonly string $name,
		public readonly int $cost,
	) {}

}
