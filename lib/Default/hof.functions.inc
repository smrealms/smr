<?php

function getHofRank($view,$viewType,$accountID,$gameID,&$db)
{
	$gameIDSql = ' AND game_id '.(isset($gameID) ? '= ' . $gameID : 'IN (SELECT game_id FROM game WHERE ignore_stats = '.$db->escapeBoolean(false).')');

	$rank = array('Amount'=>0,'Rank'=>0);
	if($view == DONATION_NAME)
		$db->query('SELECT SUM(amount) as amount FROM account_donated WHERE account_id='.$accountID.' GROUP BY account_id LIMIT 1');
	else if($view == USER_SCORE_NAME)
	{
		$statements = SmrAccount::getUserScoreCaseStatement($db);
		$query = 'SELECT '.$statements['CASE'].' amount FROM (SELECT account_id, type, SUM(amount) amount FROM player_hof WHERE type IN ('.$statements['IN'].') AND account_id='.$accountID.$gameIDSql.' GROUP BY account_id,type) x GROUP BY account_id ORDER BY amount DESC LIMIT 25';
		$db->query($query);
	}
	else
	{
		$db->query('SELECT type FROM player_hof WHERE type='.$db->escapeArray($viewType,false,true,':',false).' LIMIT 1');
		if(!$db->nextRecord())
			return $rank;
		$db->query('SELECT SUM(amount) amount FROM player_hof WHERE type='.$db->escapeArray($viewType,false,true,':',false) .' AND account_id='.$accountID.$gameIDSql.' GROUP BY account_id LIMIT 1');
	}
	if($db->nextRecord())
		if($db->getField('amount')!=null)
			$rank['Amount'] = $db->getField('amount');
	if($view == DONATION_NAME)
		$db->query('SELECT COUNT(account_id) rank FROM (SELECT account_id FROM account_donated GROUP BY account_id HAVING SUM(amount)>' . $rank['Amount'] . ') x');
	else if($view == USER_SCORE_NAME)
	{
		$query = 'SELECT COUNT(account_id) rank FROM (SELECT account_id FROM player_hof WHERE type IN ('.$statements['IN'].')'.$gameIDSql.' GROUP BY account_id HAVING '.$statements['CASE'].'>' . $rank['Amount'] . ') x';
		$db->query($query);
	}
	else
		$db->query('SELECT COUNT(account_id) rank FROM (SELECT account_id FROM player_hof WHERE type='.$db->escapeArray($viewType,false,true,':',false).$gameIDSql.' GROUP BY account_id HAVING SUM(amount)>'.$rank['Amount'].') x');
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
		$bold = ' class="bold"';
	}
	else $bold = '';
	$return=('<tr>');
	$return.=('<td align="center"'.$bold.'>' . $rank . '</td>');
	
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
		$return.=('<td align="center"'.$bold.'>'.create_link($container, $hofPlayer->getPlayerName()) .'</td>');
	else if(is_object($hofAccount))
		$return.=('<td align="center"'.$bold.'>'.create_link($container, $hofAccount->getHofName()) .'</td>');
	else
		$return.=('<td align="center"'.$bold.'>Unknown</td>');
	$return.=('<td align="center"'.$bold.'>' . $amount . '</td>');
	$return.=('</tr>');
	return $return;
}

function buildBreadcrumb(&$var,&$hofTypes,$hofName)
{
	$container = $var;
	unset($container['type']);
	unset($container['view']);
	$viewing= '<span class="bold">Currently viewing: </span>'.create_link($container,$hofName);
	$typeList = array();
	if(isset($var['type']))
	{
		foreach($var['type'] as $type)
		{
			if(!is_array($hofTypes[$type]))
			{
				$var['type'] = $typeList;
				$var['view'] = $type;
				break;
			}
			else
				$typeList[] = $type;
			$viewing .= ' -&gt; ';
			$container = $var;
			$container['type'] = $typeList;
			unset($container['view']);
			$viewing.= create_link($container,$type);
			
			$hofTypes = $hofTypes[$type];
		}
	}
	if(isset($var['view']))
	{
		$viewing .= ' -&gt; ';
		if(is_array($hofTypes[$var['view']]))
		{
			$typeList[] = $var['view'];
			$var['type'] = $typeList;
		}
		$container = $var;
		$viewing .= create_link($container,$var['view']);
		
		if(is_array($hofTypes[$var['view']]))
		{
			$hofTypes = $hofTypes[$var['view']];
			unset($var['view']);
		}
	}
	$viewing.= '<br /><br />';
	return $viewing;
}
?>