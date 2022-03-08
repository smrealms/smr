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
	protected static array $CACHE_RANDOMS = [];

	protected const WEIGHTING_CHANGE = 50; // as a percent

	protected Smr\Database $db;

	protected int $gameID;
	protected int $accountID;
	protected string $type;
	protected int $typeID;
	protected float $weighting;

	protected bool $hasChanged = false;

	public static function getWeightedRandom(int $gameID, int $accountID, string $type, int $typeID, bool $forceUpdate = false): self {
		if ($forceUpdate || !isset(self::$CACHE_RANDOMS[$gameID][$accountID][$type][$typeID])) {
			self::$CACHE_RANDOMS[$gameID][$accountID][$type][$typeID] = new self($gameID, $accountID, $type, $typeID);
		}
		return self::$CACHE_RANDOMS[$gameID][$accountID][$type][$typeID];
	}

	public static function getWeightedRandomForPlayer(AbstractSmrPlayer $player, string $type, int $typeID, bool $forceUpdate = false): self {
		return self::getWeightedRandom($player->getGameID(), $player->getAccountID(), $type, $typeID, $forceUpdate);
	}

	public static function saveWeightedRandoms(): void {
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

	protected function __construct(int $gameID, int $accountID, string $type, int $typeID) {
		$this->gameID = $gameID;
		$this->accountID = $accountID;
		$this->type = $type;
		$this->typeID = $typeID;

		$this->db = Smr\Database::getInstance();
		$dbResult = $this->db->read('SELECT weighting FROM weighted_random WHERE game_id = ' . $this->db->escapeNumber($gameID) . ' AND account_id = ' . $this->db->escapeNumber($accountID) . ' AND type = ' . $this->db->escapeString($type) . ' AND type_id = ' . $this->db->escapeNumber($typeID) . ' LIMIT 1');
		if ($dbResult->hasRecord()) {
			$this->weighting = $dbResult->record()->getInt('weighting');
		} else {
			$this->weighting = 0;
		}
	}

	public function getGameID(): int {
		return $this->gameID;
	}

	public function getAccountID(): int {
		return $this->accountID;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getTypeID(): int {
		return $this->typeID;
	}

	public function getWeighting(): float {
		return $this->weighting;
	}

	/**
	 * Given $successChance as the base percent chance that an event happens,
	 * reduce that chance by the current weighting, and then check the result.
	 */
	public function flipWeightedCoin(float $successChance): bool {
		// The weighting update formulas below only work in the range [0, 100].
		$successChance = min(100, max(0, $successChance));

		// Check if the event was successful.
		$success = rand(1, 100) + $this->weighting <= $successChance;

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

	public function update(): void {
		if ($this->hasChanged === true) {
			$this->db->write('REPLACE INTO weighted_random (game_id,account_id,type,type_id,weighting)
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
