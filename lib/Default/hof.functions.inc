<?php

function getHofRank($view,$viewType,$accountID,$gameID,&$db)
{
	if($view == DONATION_NAME)
		$db->query('SELECT account_id, sum(amount) as amount FROM account_donated WHERE account_id='.$accountID.' LIMIT 1');
	else
		$db->query('SELECT account_id,amount FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false) .' AND account_id='.$accountID.(isset($gameID) ? ' AND game_id=' . $gameID : '').' GROUP BY type LIMIT 1');
	$rank['Amount'] = 0;
	if($db->nextRecord())
		if($db->getField('amount')!=null)
			$amount = $db->getField('amount');
	if($view == DONATION_NAME)
		$db->query('SELECT count(account_id) as rank, sum(amount) AS amount FROM account_donated WHERE amount>' . $rank['Amount'] .
				' GROUP BY account_id LIMIT 1');
	else
		$db->query('SELECT count(account_id) as rank FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false) .' AND amount>'.$rank['Amount'].(isset($gameID) ? ' AND game_id=' . $gameID : '').' GROUP BY type LIMIT 1');
	$rank['Rank'] = 0;
	if($db->nextRecord())
		$rank['Rank'] = $db->getField('rank') + 1;
	return $rank;
}

function displayHOFRow($rank,$accountID,$amount)
{
	global $account,$player,$var;
	if(isset($var['game_id']) && Globals::getGameInfo($var['game_id'])!==false)
	{
		try
		{
			$hofPlayer =& SmrPlayer::getPlayer($accountID,$var['game_id']);
		}
		catch(Exception $e)
		{
			$hofAccount =& SmrAccount::getAccount($accountID);
		}
	}
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
	if(is_object($hofPlayer))
		$return.=('<td align=center'.$bold.'>'.create_link($container, $hofPlayer->getPlayerName()) .'</td>');
	else if(is_object($hofAccount))
		$return.=('<td align=center'.$bold.'>Unknown</td>');
	else
		$return.=('<td align=center'.$bold.'>'.create_link($container, $hofAccount->getHofName()) .'</td>');
	$return.=('<td align=center'.$bold.'>' . $amount . '</td>');
	$return.=('</tr>');
	return $return;
}
?>