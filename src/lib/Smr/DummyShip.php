<?php declare(strict_types=1);

namespace Smr;

class DummyShip extends AbstractShip {

	/** @var array<string, self> */
	protected static array $CACHED_DUMMY_SHIPS;

	public static function saveDummyShips(): void {
		foreach (self::$CACHED_DUMMY_SHIPS as $ship) {
			$ship->cacheDummyShip();
		}
	}

	public function __construct(AbstractPlayer $player) {
		parent::__construct($player);
		$this->setHardwareToMax();
	}

	public function cacheDummyShip(): void {
		$db = Database::getInstance();
		$db->replace('cached_dummys', [
			'type' => 'DummyShip',
			'id' => $this->getPlayer()->getPlayerName(),
			'info' => $db->escapeObject($this),
		]);
	}

	public static function getCachedDummyShip(string $dummyName): self {
		if (!isset(self::$CACHED_DUMMY_SHIPS[$dummyName])) {
			// Load ship from the dummy database cache, if available
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT info FROM cached_dummys WHERE type = \'DummyShip\'
						AND id = :dummy_name', [
				'dummy_name' => $db->escapeString($dummyName),
			]);
			if ($dbResult->hasRecord()) {
				$ship = $dbResult->record()->getClass('info', self::class);
			} else {
				$player = new DummyPlayer($dummyName);
				$ship = new self($player);
			}
			self::$CACHED_DUMMY_SHIPS[$dummyName] = $ship;
		}
		return self::$CACHED_DUMMY_SHIPS[$dummyName];
	}

	/**
	 * @return array<string>
	 */
	public static function getDummyNames(): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT id FROM cached_dummys');
		$dummyNames = [];
		foreach ($dbResult->records() as $dbRecord) {
			$dummyNames[] = $dbRecord->getString('id');
		}
		return $dummyNames;
	}

	public function __sleep() {
		return ['weapons', 'hardware', 'shipType', 'player'];
	}

}
