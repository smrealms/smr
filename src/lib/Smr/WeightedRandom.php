<?php declare(strict_types=1);

namespace Smr;

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

	/** @var array<int, array<int, array<string, array<int, self>>>> */
	protected static array $CACHE_RANDOMS = [];

	protected const WEIGHTING_CHANGE = 50; // as a percent

	protected float $weighting;

	protected bool $hasChanged = false;

	public static function getWeightedRandom(int $gameID, int $accountID, string $type, int $typeID, bool $forceUpdate = false): self {
		if ($forceUpdate || !isset(self::$CACHE_RANDOMS[$gameID][$accountID][$type][$typeID])) {
			self::$CACHE_RANDOMS[$gameID][$accountID][$type][$typeID] = new self($gameID, $accountID, $type, $typeID);
		}
		return self::$CACHE_RANDOMS[$gameID][$accountID][$type][$typeID];
	}

	public static function getWeightedRandomForPlayer(AbstractPlayer $player, string $type, int $typeID, bool $forceUpdate = false): self {
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

	protected function __construct(
		protected readonly int $gameID,
		protected readonly int $accountID,
		protected readonly string $type,
		protected readonly int $typeID
	) {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT weighting FROM weighted_random WHERE game_id = :game_id AND account_id = :account_id AND type = :type AND type_id = :type_id', [
			'game_id' => $db->escapeNumber($gameID),
			'account_id' => $db->escapeNumber($accountID),
			'type' => $db->escapeString($type),
			'type_id' => $db->escapeNumber($typeID),
		]);
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
			$db = Database::getInstance();
			$db->replace('weighted_random', [
				'game_id' => $this->getGameID(),
				'account_id' => $this->getAccountID(),
				'type' => $this->getType(),
				'type_id' => $this->getTypeID(),
				'weighting' => $this->getWeighting(),
			]);
			$this->hasChanged = false;
		}
	}

}
