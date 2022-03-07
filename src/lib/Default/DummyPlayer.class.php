<?php declare(strict_types=1);

class DummyPlayer extends AbstractSmrPlayer {

	public function __construct(string $playerName, int $experience = 1000, int $shipTypeID = 60) {
		$this->playerName = $playerName;
		$this->experience = $experience;
		$this->shipID = $shipTypeID;
		$this->setConstantProperties();
	}

	/**
	 * Sets properties that are needed for combat, but do not need to
	 * be stored in the database.
	 */
	protected function setConstantProperties() : void {
		$this->gameID = 0;
		$this->accountID = 0;
		$this->playerID = 0;
		$this->alignment = 0;
		$this->underAttack = false;
		$this->npc = true;
		$this->dead = false;
	}

	public function __sleep() {
		return ['playerName', 'experience', 'shipID'];
	}

	public function __wakeup() {
		$this->setConstantProperties();
	}

	public function increaseHOF(float $amount, array $typeList, string $visibility) : void {}

	public function killPlayerByPlayer(AbstractSmrPlayer $killer) : array {
		$this->dead = true;
		return ['DeadExp' => 0, 'KillerCredits' => 0, 'KillerExp' => 0];
	}

	public function getShip(bool $forceUpdate = false) : AbstractSmrShip {
		return DummyShip::getCachedDummyShip($this);
	}

	public function cacheDummyPlayer() : void {
		$this->getShip()->cacheDummyShip();
		$db = Smr\Database::getInstance();
		$db->write('REPLACE INTO cached_dummys ' .
					'(type, id, info) ' .
					'VALUES (\'DummyPlayer\', ' . $db->escapeString($this->getPlayerName()) . ', ' . $db->escapeObject($this) . ')');
	}

	public static function getCachedDummyPlayer(string $name) : self {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT info FROM cached_dummys
					WHERE type = \'DummyPlayer\'
						AND id = ' . $db->escapeString($name) . ' LIMIT 1');
		if ($dbResult->hasRecord()) {
			return $dbResult->record()->getObject('info');
		} else {
			return new DummyPlayer($name);
		}
	}

	public static function getDummyPlayerNames() : array {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT id FROM cached_dummys
					WHERE type = \'DummyPlayer\'');
		$dummyNames = [];
		foreach ($dbResult->records() as $dbRecord) {
			$dummyNames[] = $dbRecord->getField('id');
		}
		return $dummyNames;
	}

}
