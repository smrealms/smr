<?php declare(strict_types=1);

namespace Smr;

use Exception;

class PlayerLevel {

	/** @var array<int, self> */
	private static array $CACHE_LEVELS = [];

	public static function clearCache(): void {
		self::$CACHE_LEVELS = [];
	}

	/**
	 * @return array<int, self>
	 */
	public static function getAll(): array {
		if (empty(self::$CACHE_LEVELS)) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM level');
			foreach ($dbResult->records() as $dbRecord) {
				$levelID = $dbRecord->getInt('level_id');
				self::$CACHE_LEVELS[$levelID] = new self(
					id: $levelID,
					name: $dbRecord->getString('level_name'),
					expRequired: $dbRecord->getInt('requirement'),
				);
			}
		}
		return self::$CACHE_LEVELS;
	}

	public static function get(int $exp): self {
		foreach (array_reverse(self::getAll()) as $level) {
			if ($exp >= $level->expRequired) {
				return $level;
			}
		}
		throw new Exception('Failed to properly determine level from exp: ' . $exp);
	}

	public static function getMax(): int {
		$levels = self::getAll();
		if (count($levels) === 0) {
			throw new Exception('Cannot get the max level, no levels were found');
		}
		return max(array_keys($levels));
	}

	public function __construct(
		public readonly int $id,
		public readonly string $name,
		public readonly int $expRequired,
	) {}

	public function next(): self {
		// Return current level if on the last level
		return self::getAll()[$this->id + 1] ?? $this;
	}

}
