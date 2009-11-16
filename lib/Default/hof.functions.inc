<?php

function getHofRank($view,$viewType,$accountID,$gameID,&$db)
{
	$rank = array('Amount'=>0,'Rank'=>0);
	if($view == DONATION_NAME)
		$db->query('SELECT account_id, sum(amount) as amount FROM account_donated WHERE account_id='.$accountID.' GROUP BY account_id LIMIT 1');
	else if($view == USER_SCORE_NAME)
	{
		$statements = SmrAccount::getUserScoreCaseStatement($db);
		$query = 'SELECT account_id, '.$statements['CASE'].' amount FROM (SELECT account_id, type, SUM(amount) amount FROM player_hof WHERE type IN ('.$statements['IN'].') AND account_id='.$accountID.(isset($gameID) ? ' AND game_id=' . $gameID : '') .' GROUP BY account_id,type) x GROUP BY account_id ORDER BY amount DESC LIMIT 25';
		$db->query($query);
	}
	else
	{
		$db->query('SELECT type FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false).' LIMIT 1');
		if(!$db->nextRecord())
			return $rank;
		$db->query('SELECT account_id,SUM(amount) amount FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false) .' AND account_id='.$accountID.(isset($gameID) ? ' AND game_id=' . $gameID : '').' GROUP BY account_id LIMIT 1');
	}
	if($db->nextRecord())
		if($db->getField('amount')!=null)
			$rank['Amount'] = $db->getField('amount');
	if($view == DONATION_NAME)
		$db->query('SELECT COUNT(account_id) rank FROM (SELECT account_id FROM account_donated GROUP BY account_id HAVING SUM(amount)>' . $rank['Amount'] . ') x');
	else if($view == USER_SCORE_NAME)
	{
		$query = 'SELECT COUNT(account_id) rank FROM (SELECT account_id FROM player_hof WHERE type IN ('.$statements['IN'].')'.(isset($gameID) ? ' AND game_id=' . $gameID : '') .' GROUP BY account_id HAVING '.$statements['CASE'].'>' . $rank['Amount'] . ') x';
		$db->query($query);
	}
	else
		$db->query('SELECT COUNT(account_id) rank FROM (SELECT account_id FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false) .(isset($gameID) ? ' AND game_id=' . $gameID : '').' GROUP BY account_id HAVING SUM(amount)>'.$rank['Amount'].') x');
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
		$return.=('<td align=center'.$bold.'>'.create_link($container, $hofAccount->getHofName()) .'</td>');
	else
		$return.=('<td align=center'.$bold.'>Unknown</td>');
	$return.=('<td align=center'.$bold.'>' . $amount . '</td>');
	$return.=('</tr>');
	return $return;
}
?>