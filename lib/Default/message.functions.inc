<?php

function &getMessagePlayer($accountID,$gameID)
{
	if($accountID==ACCOUNT_ID_PORT)
		$return = '<span class="yellow">Port Defenses</span>';
	else if($accountID==ACCOUNT_ID_ADMIN)
		$return = '<span class="admin">Administrator</span>';
	else if($accountID==ACCOUNT_ID_PLANET)
		$return = '<span class="yellow">Planetary Defenses</span>';
	else if(!empty($accountID))
		$return =  SmrPlayer::getPlayer($accountID, $gameID);
	return $return;
}

?>