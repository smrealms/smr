<?php declare(strict_types=1);

namespace Smr;

use Smr\Exceptions\MissionStepNotFound;
use Smr\MissionActions\ClaimReward;

class MissionState {

	/** @var array<int, array<int, array<int, self>>> */
	private static array $CACHE = [];

	public static function saveMissionStates(): void {
		foreach (self::$CACHE as $gameMissionStates) {
			foreach ($gameMissionStates as $playerMissionStates) {
				foreach ($playerMissionStates as $missionState) {
					$missionState->update();
				}
			}
		}
	}

	public static function clearCache(): void {
		self::$CACHE = [];
	}

	/**
	 * Get existing missions for the given player.
	 *
	 * @return array<int, self>
	 */
	public static function getPlayerMissionStates(AbstractPlayer $player): array {
		$gameID = $player->getGameID();
		$accountID = $player->getAccountID();
		if (!isset(self::$CACHE[$gameID][$accountID])) {
			$db = Database::getInstance();
			$dbResult = $db->select('player_has_mission', $player->SQLID);
			$missionStates = [];
			foreach ($dbResult->records() as $dbRecord) {
				$missionID = $dbRecord->getInt('mission_id');
				$missionStates[$missionID] = self::getFromRecord($dbRecord);
			}
			self::$CACHE[$gameID][$accountID] = $missionStates;
		}
		return self::$CACHE[$gameID][$accountID];
	}

	/**
	 * Add a new mission for the given player.
	 */
	public static function addPlayerMission(AbstractPlayer $player, Mission $mission): self {
		$gameID = $player->getGameID();
		$accountID = $player->getAccountID();
		$missionID = $mission->getMissionID();
		$missionState = new self(
			accountID: $accountID,
			gameID: $gameID,
			missionID: $missionID,
			onStep: 0,
			unread: false,
			expires: Epoch::time() + 86400, // 1 day
			complete: false,
			mission: $mission,
			hasChanged: true,
		);
		self::$CACHE[$gameID][$accountID][$missionID] = $missionState;
		return $missionState;
	}

	private static function getFromRecord(DatabaseRecord $record): self {
		return new self(
			accountID: $record->getInt('account_id'),
			gameID: $record->getInt('game_id'),
			missionID: $record->getInt('mission_id'),
			onStep: $record->getInt('on_step'),
			unread: $record->getBoolean('unread'),
			expires: $record->getInt('expires'),
			complete: $record->getBoolean('complete'),
			mission: $record->getClass('mission', Mission::class),
			hasChanged: false,
		);
	}

	private function __construct(
		public readonly int $accountID,
		public readonly int $gameID,
		public readonly int $missionID,
		public readonly Mission $mission,
		private int $onStep,
		private bool $unread,
		private readonly int $expires, // currently unused
		private bool $complete,
		private bool $hasChanged,
	) {}

	private function update(): bool {
		if (!$this->hasChanged) {
			return false;
		}
		$db = Database::getInstance();
		$db->replace('player_has_mission', [
			'account_id' => $this->accountID,
			'game_id' => $this->gameID,
			'mission_id' => $this->missionID,
			'on_step' => $this->onStep,
			'unread' => $db->escapeBoolean($this->unread),
			'expires' => $this->expires,
			'complete' => $db->escapeBoolean($this->complete),
			'mission' => $db->escapeObject($this->mission),
		]);
		$this->hasChanged = false;
		return true;
	}

	public function delete(): void {
		$db = Database::getInstance();
		$db->delete('player_has_mission', [
			'mission_id' => $this->missionID,
			'account_id' => $this->accountID,
			'game_id' => $this->gameID,
		]);
		$this->hasChanged = false; // to avoid re-inserting into database
		unset(self::$CACHE[$this->gameID][$this->accountID][$this->missionID]);
	}

	public function markComplete(): void {
		$this->complete = true;
		$this->hasChanged = true;
	}

	public function markRead(): void {
		$this->unread = false;
		$this->hasChanged = true;
	}

	private function advanceToNextStep(): void {
		$this->onStep++;
		$this->unread = true;
		$this->hasChanged = true;
	}

	public function hasClaimableReward(int $sectorID): bool {
		return $this->isRequirementMet(new ClaimReward($sectorID));
	}

	public function isComplete(): bool {
		return $this->complete;
	}

	public function getUnreadMessage(): ?string {
		if (!$this->unread) {
			return null;
		}
		$this->markRead();
		return $this->getStep()->message;
	}

	public function getTask(): string {
		return $this->getStep()->task;
	}

	private function getStep(): MissionStep {
		return $this->mission->getStep($this->onStep);
	}

	private function isRequirementMet(MissionAction $action): bool {
		return objects_equal($this->getStep()->requirement, $action);
	}

	/**
	 * Check if an action satisfies the requirement for the current step,
	 * and if it does, advance to the next step.
	 */
	public function checkAction(MissionAction $action): void {
		if ($this->isRequirementMet($action)) {
			// Requirements for this step are met, so go to next step
			$this->advanceToNextStep();
			// Check to see if we just completed the last step
			try {
				$this->getStep();
			} catch (MissionStepNotFound) {
				$this->markComplete();
			}
		}
	}

}
