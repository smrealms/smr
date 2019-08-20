<?php declare(strict_types=1);

class Mission {

	public static function getAcceptHREF($missionID) {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'mission_accept_processing.php', ['MissionID' => $missionID]));
	}

	public static function getDeclineHREF($missionID) {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'mission_decline_processing.php', ['MissionID' => $missionID]));
	}

	public static function getAbandonHREF($missionID) {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'mission_abandon_processing.php', ['MissionID' => $missionID]));
	}

	public static function getClaimRewardHREF($missionID) {
		return SmrSession::getNewHREF(create_container('skeleton.php', 'mission_claim_processing.php', ['MissionID' => $missionID]));
	}

}
