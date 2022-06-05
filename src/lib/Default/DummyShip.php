<?php declare(strict_types=1);

class DummyShip extends AbstractSmrShip {

	protected static array $CACHED_DUMMY_SHIPS;

	public static function saveDummyShips(): void {
		foreach (self::$CACHED_DUMMY_SHIPS as $ship) {
			$ship->cacheDummyShip();
		}
	}

	public function __construct(AbstractSmrPlayer $player) {
		parent::__construct($player);
		$this->setHardwareToMax();
	}

	public function cacheDummyShip(): void {
		$db = Smr\Database::getInstance();
		$db->replace('cached_dummys', [
			'type' => $db->escapeString('DummyShip'),
			'id' => $db->escapeString($this->getPlayer()->getPlayerName()),
			'info' => $db->escapeObject($this),
		]);
	}

	public static function getCachedDummyShip(string $dummyName): self {
		if (!isset(self::$CACHED_DUMMY_SHIPS[$dummyName])) {
			// Load ship from the dummy database cache, if available
			$db = Smr\Database::getInstance();
			$dbResult = $db->read('SELECT info FROM cached_dummys WHERE type = \'DummyShip\'
						AND id = ' . $db->escapeString($dummyName));
			if ($dbResult->hasRecord()) {
				$ship = $dbResult->record()->getObject('info');
			} else {
				$player = new DummyPlayer($dummyName);
				$ship = new self($player);
			}
			self::$CACHED_DUMMY_SHIPS[$dummyName] = $ship;
		}
		return self::$CACHED_DUMMY_SHIPS[$dummyName];
	}

	public static function getDummyNames(): array {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT id FROM cached_dummys');
		$dummyNames = [];
		foreach ($dbResult->records() as $dbRecord) {
			$dummyNames[] = $dbRecord->getField('id');
		}
		return $dummyNames;
	}

	public function __sleep() {
		return ['weapons', 'hardware', 'shipType', 'player'];
	}

}
