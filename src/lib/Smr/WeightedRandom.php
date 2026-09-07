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

	/** @var array<int, array<string, array<int, self>>> */
	protected static array $CACHE_RANDOMS = [];

	protected const int WEIGHTING_CHANGE = 50; // as a percent

	protected float $weighting;

	protected bool $hasChanged = false;

	/**
	 * @param 'Weapon'|'PortWeapon'|'PlanetWeapon' $type
	 */
	public static function getWeightedRandom(int $playerID, string $type, int $typeID, bool $forceUpdate = false): self {
		if ($forceUpdate || !isset(self::$CACHE_RANDOMS[$playerID][$type][$typeID])) {
			self::$CACHE_RANDOMS[$playerID][$type][$typeID] = new self($playerID, $type, $typeID);
		}
		return self::$CACHE_RANDOMS[$playerID][$type][$typeID];
	}

	/**
	 * @param 'Weapon'|'PlanetWeapon'|'PortWeapon' $type
	 */
	public static function getWeightedRandomForPlayer(Player $player, string $type, int $typeID, bool $forceUpdate = false): self {
		return self::getWeightedRandom(
			playerID: $player->getPlayerID(),
			type: $type,
			typeID: $typeID,
			forceUpdate: $forceUpdate,
		);
	}

	public static function saveWeightedRandoms(): void {
		foreach (self::$CACHE_RANDOMS as $playerRandoms) {
			foreach ($playerRandoms as $typeRandoms) {
				foreach ($typeRandoms as $random) {
					$random->update();
				}
			}
		}
	}

	protected function __construct(
		protected readonly int $playerID,
		protected readonly string $type,
		protected readonly int $typeID,
	) {
		$db = Database::getInstance();
		$dbResult = $db->select(
			'weighted_random',
			[
				'player_id' => $playerID,
				'type' => $type,
				'type_id' => $typeID,
			],
			['weighting'],
		);
		if ($dbResult->hasRecord()) {
			$this->weighting = $dbResult->record()->getFloat('weighting');
		} else {
			$this->weighting = 0;
		}
	}

	public function getPlayerID(): int {
		return $this->playerID;
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
		$success = flip_coin($successChance - $this->weighting);

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
				'player_id' => $this->getPlayerID(),
				'game_id' => Player::getPlayer($this->getPlayerID())->getGameID(),
				'type' => $this->getType(),
				'type_id' => $this->getTypeID(),
				'weighting' => $this->getWeighting(),
			]);
			$this->hasChanged = false;
		}
	}

}
