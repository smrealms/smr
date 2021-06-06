<?php declare(strict_types=1);

class Mission {

	public static function getAcceptHREF(int $missionID) : string {
		return Page::create('mission_accept_processing.php', '', ['MissionID' => $missionID])->href();
	}

	public static function getDeclineHREF(int $missionID) : string {
		return Page::create('mission_decline_processing.php', '', ['MissionID' => $missionID])->href();
	}

	public static function getAbandonHREF(int $missionID) : string {
		return Page::create('mission_abandon_processing.php', '', ['MissionID' => $missionID])->href();
	}

	public static function getClaimRewardHREF(int $missionID) : string {
		return Page::create('mission_claim_processing.php', '', ['MissionID' => $missionID])->href();
	}

}
