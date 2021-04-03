<?php declare(strict_types=1);

class Mission {

	public static function getAcceptHREF($missionID) {
		return Page::create('mission_accept_processing.php', '', ['MissionID' => $missionID])->href();
	}

	public static function getDeclineHREF($missionID) {
		return Page::create('mission_decline_processing.php', '', ['MissionID' => $missionID])->href();
	}

	public static function getAbandonHREF($missionID) {
		return Page::create('mission_abandon_processing.php', '', ['MissionID' => $missionID])->href();
	}

	public static function getClaimRewardHREF($missionID) {
		return Page::create('mission_claim_processing.php', '', ['MissionID' => $missionID])->href();
	}

}
