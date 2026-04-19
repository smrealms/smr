<?php declare(strict_types=1);

namespace Smr;

use Exception;
use Smr\MissionActions\ClaimReward;
use Smr\Missions\DrunkGuy;

abstract readonly class Mission {

	// NOTE: array keys are the mission ID and should not be changed!
	public const array MISSIONS = [
		0 => DrunkGuy::class,
	];

	/**
	 * Look up the mission ID for this Mission instance based on the class.
	 */
	public function getMissionID(): int {
		foreach (static::MISSIONS as $missionID => $missionClass) {
			if ($missionClass === static::class) {
				return $missionID;
			}
		}
		throw new Exception('Unmapped Mission child class: ' . static::class);
	}

	abstract public function __construct(Player $player);

	/**
	 * Bestows the reward to the player upon completion.
	 */
	public function claimReward(Player $player): string {
		$rewardText = $this->reward($player);
		$player->actionTaken(new ClaimReward($player->getSectorID()));
		return $rewardText;
	}

	/**
	 * Get the prompt to display if the Mission is available to the player.
	 */
	public function getFirstMessage(): string {
		return $this->getStep(0)->message;
	}

	abstract protected function reward(Player $player): string;

	/**
	 * Is the player eligible to accept the mission in their current state?
	 */
	abstract public static function isAvailableToPlayer(Player $player): bool;

	/**
	 * Get the details of the current Mission step.
	 */
	abstract public function getStep(int $step): MissionStep;

}
