<?php

function displayHOFRow($rank,$accountID,$amount)
{
	global $account,$player,$var;
	if(isset($var['game_id']) && Globals::getGameInfo($var['game_id'])!==false)
		$hofPlayer =& SmrPlayer::getPlayer($accountID,$var['game_id']);
	else
		$hofAccount =& SmrAccount::getAccount($accountID);
	if ($accountID == $account->account_id)
	{
		$foundMe = true;
		$bold = ' style="font-weight:bold;"';
	}
	else $bold = '';
	$return=('<tr>');
	$return.=('<td align=center'.$bold.'>' . $rank . '</td>');
	
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'hall_of_fame_player_detail.php';
	$container['account_id'] = $accountID;
	
	if (isset($var['game_id']))
	{
		$container['game_id'] = $var['game_id'];
		$container['sending_page'] = 'personal_current_hof';
	}
	else
	{
		$container['game_id'] = $player->getGameID();
		$container['sending_page'] = 'personal_hof';
	}
	if(isset($var['game_id']))
		$return.=('<td align=center'.$bold.'>'.create_link($container, $hofPlayer->getPlayerName()) .'</td>');
	else
		$return.=('<td align=center'.$bold.'>'.create_link($container, $hofAccount->getHofName()) .'</td>');
	$return.=('<td align=center'.$bold.'>' . $amount . '</td>');
	$return.=('</tr>');
	return $return;
}
?>