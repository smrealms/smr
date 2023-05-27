<?php declare(strict_types=1);

namespace Smr;

class TradeGood {

	/** @var array<int, self> */
	private static array $CACHE_GOODS = [];

	public static function clearCache(): void {
		self::$CACHE_GOODS = [];
	}

	/**
	 * @return array<int, self>
	 */
	public static function getAll(): array {
		if (count(self::$CACHE_GOODS) === 0) {
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM good');
			foreach ($dbResult->records() as $dbRecord) {
				$goodID = $dbRecord->getInt('good_id');
				self::$CACHE_GOODS[$goodID] = new self(
					id: $goodID,
					name: $dbRecord->getString('good_name'),
					maxPortAmount: $dbRecord->getInt('max_amount'),
					basePrice: $dbRecord->getInt('base_price'),
					class: $dbRecord->getInt('good_class'),
					alignRestriction: $dbRecord->getInt('align_restriction'),
				);
			}
		}
		return self::$CACHE_GOODS;
	}

	/**
	 * @return array<int>
	 */
	public static function getAllIDs(): array {
		return array_keys(self::getAll());
	}

	public static function get(int $goodID): self {
		return self::getAll()[$goodID];
	}

	public function __construct(
		public readonly int $id,
		public readonly string $name,
		public readonly int $maxPortAmount,
		public readonly int $basePrice,
		public readonly int $class,
		public readonly int $alignRestriction,
	) {}

	public function getImageHTML(): string {
		return '<img class="bottom" src="images/port/' . $this->id . '.png" width="13" height="16" title="' . $this->name . '" alt="' . $this->name . '" />';
	}

}
