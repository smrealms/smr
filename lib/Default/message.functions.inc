<?php

function &getMessagePlayer($accountID, $gameID, $messageType = false)
{
	if($accountID==ACCOUNT_ID_PORT)
		$return = '<span class="yellow">Port Defenses</span>';
	else if($accountID==ACCOUNT_ID_ADMIN)
		$return = '<span class="admin">Administrator</span>';
	else if($accountID==ACCOUNT_ID_PLANET)
		$return = '<span class="yellow">Planetary Defenses</span>';
	else if($accountID==ACCOUNT_ID_ALLIANCE_AMBASSADOR)
		$return = '<span class="green">Alliance Ambassador</span>';
	else if($accountID==ACCOUNT_ID_CASINO)
		$return = '<span class="yellow">Casino</span>';
	else
	{
		$RACES =& Globals::getRaces();
		foreach($RACES as $raceID => &$raceInfo)
		{
			if($accountID==ACCOUNT_ID_GROUP_RACES+$raceID)
			{
				$return = '<span class="yellow">'.$raceInfo['Race Name'].'</span>';
				return $return;
			}
		}
		if(!empty($accountID))
			$return = SmrPlayer::getPlayer($accountID, $gameID);
		else
		{
			switch($messageType)
			{
				case MSG_ADMIN:
					$return = '<span class="admin">Administrator</span>';
				break;
					
				case MSG_ALLIANCE:
					$return = '<span class="green">Alliance Ambassador</span>';
				break;
							
				case MSG_PLAYER:
					$return = '<span class="yellow">Port Defenses</span>';
				break;
							
				default:
					$return = 'Unknown';
			}
		}
	}
	return $return;
}

?>