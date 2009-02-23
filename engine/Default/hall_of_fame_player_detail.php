<?
if(empty($var['game_id']))
	create_error('No game ID specified');
if (isset($var['account_id']))
	$account_id = $var['account_id'];
else 
	$account_id = $account->account_id;
$base = array();

$hofPlayer =& SmrPlayer::getPlayer($account_id,$var['game_id']);
$smarty->assign('PageTopic',$hofPlayer->getPlayerName().'Time Hall of Fame');
$PHP_OUTPUT.=('<div align=center>');

$db->query('SELECT DISTINCT type FROM player_hof WHERE game_id='.$var['game_id'].' AND account_id='.$account_id.' ORDER BY type');
$DONATION_NAME = 'Money Donated To SMR';
$hofTypes = array($DONATION_NAME=>true);
while($db->nextRecord())
{
	$hof =& $hofTypes;
	$typeList = explode(':',$db->getField('type'));
	foreach($typeList as $type)
	{
		if(!isset($hof[$type]))
		{
			$hof[$type] = array();
		}
		$hof =& $hof[$type];
	}
	$hof = true;
}
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'hall_of_fame_player_detail.php';
if (isset($var['game_id'])) $container['game_id'] = $var['game_id'];
$viewing= '<span style="font-weight:bold;">Currently viewing: </span>'.create_link($container,'Personal HoF');
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
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'hall_of_fame_player_detail.php';
		$container['type'] = $typeList;
		if (isset($var['game_id'])) $container['game_id'] = $var['game_id'];
		$viewing.= create_link($container,$type);
		
		$hofTypes =& $hofTypes[$type];
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
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'hall_of_fame_player_detail.php';
	$container['type'] = $typeList;
	if(isset($var['view'])) $container['view'] = $var['view'];
	if (isset($var['game_id'])) $container['game_id'] = $var['game_id'];
	$viewing .= create_link($container,$var['view']);
	
	if(is_array($hofTypes[$var['view']]))
	{
		$hofTypes =& $hofTypes[$var['view']];
		unset($var['view']);
	}
}
$viewing.= '<br /><br />';

$PHP_OUTPUT.= $viewing;
$PHP_OUTPUT.= create_table();


if(!isset($var['view']))
{
	$PHP_OUTPUT.=('<tr><th align=center>Category</th><th align=center width=60%>Subcategory</th></tr>');
	
	foreach($hofTypes as $type => $value)
	{
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align=center>'.$type.'</td>');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'hall_of_fame_player_detail.php';
		if (isset($var['type']))
			$container['type'] = $var['type'];
		else
			$container['type'] = array();
		$container['type'][] = $type;
		$container['game_id'] = $var['game_id'];
		$container['account_id'] = $account_id;
		$PHP_OUTPUT.=('<td align=center valign=middle>');
		$i=0;
		if(is_array($value))
		{
			foreach($value as $subType => $subTypeValue)
			{
				++$i;
				$container['view'] = $subType;
				$PHP_OUTPUT.=create_submit_link($container,$subType);
				$PHP_OUTPUT.=('&nbsp;');
				if ($i % 3 == 0) $PHP_OUTPUT.=('<br />');
			}
		}
		else
		{
			unset($container['view']);
			$PHP_OUTPUT.=create_submit_link($container,'View');
		}
		$PHP_OUTPUT.=('</td></tr>');
	}
}
else
{
	$PHP_OUTPUT.=('<tr><th align="center">Rank</th><th align="center">Player</th><th align="center">Total</th></tr>');
	
	$viewType = $var['type'];
	$viewType[] = $var['view'];
	
	if($var['view'] == $DONATION_NAME)
		$db->query('SELECT account_id, sum(amount) as amount FROM account_donated WHERE account_id='.$account_id.' LIMIT 1');
	else
		$db->query('SELECT account_id,amount FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false).(isset($var['game_id']) ? ' AND game_id=' . $var['game_id'] : ' GROUP BY type') .' AND account_id='.$account_id.' LIMIT 1');
	$hofAmount = 0;
	if($db->nextRecord())
		if($db->getField('amount')!=null)
			$hofAmount = $db->getField('amount');
	if($var['view'] == $DONATION_NAME)
		$db->query('SELECT count(account_id) as rank, sum(amount) AS amount FROM account_donated WHERE amount>' . $hofAmount .
				' GROUP BY account_id LIMIT 1');
	else
		$db->query('SELECT count(account_id) as rank FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false).(isset($var['game_id']) ? ' AND game_id=' . $var['game_id'] : ' GROUP BY type') .' AND amount>'.$hofAmount.' LIMIT 1');
	$hofRank = 1;
	if($db->nextRecord())
		$hofRank = $db->getField('rank') + 1;
	
	if(!$player->equals($hofPlayer))
	{
		//current player's score.
		if($var['view'] == $DONATION_NAME)
			$db->query('SELECT account_id, sum(amount) as amount FROM account_donated WHERE account_id='.$player->getAccountID().' LIMIT 1');
		else
			$db->query('SELECT account_id,amount FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false).(isset($var['game_id']) ? ' AND game_id=' . $var['game_id'] : ' GROUP BY type') .' AND account_id='.$player->getAccountID().' LIMIT 1');
		$playerAmount = 0;
		if($db->nextRecord())
			if($db->getField('amount')!=null)
				$playerAmount = $db->getField('amount');
		if($var['view'] == $DONATION_NAME)
			$db->query('SELECT count(account_id) as rank, sum(amount) AS amount FROM account_donated WHERE amount>' . $playerAmount .
					' GROUP BY account_id LIMIT 1');
		else
			$db->query('SELECT count(account_id) as rank FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false).(isset($var['game_id']) ? ' AND game_id=' . $var['game_id'] : ' GROUP BY type') .' AND amount>'.$playerAmount.' LIMIT 1');
		$playerRank = 1;
		if($db->nextRecord())
			$playerRank = $db->getField('rank') + 1;
		
		//display in order
		if($playerRank>$hofRank)
			$PHP_OUTPUT .= displayHOFRow($playerRank,$player,$playerAmount);
		else
			$PHP_OUTPUT .= displayHOFRow($hofRank,$hofPlayer,$hofAmount);
		
		if($playerRank<$hofRank)
			$PHP_OUTPUT .= displayHOFRow($playerRank,$player,$playerAmount);
		else
			$PHP_OUTPUT .= displayHOFRow($hofRank,$hofPlayer,$hofAmount);
	}
	else
		$PHP_OUTPUT .= displayHOFRow($hofRank,$hofPlayer,$hofAmount);
}

$PHP_OUTPUT.=('</table></div>');

function displayHOFRow($rank,$hofPlayer,$amount)
{
	global $account,$player,$var;
	$return=('<tr>');
	$return.=('<td align=center>' . $rank . '</td>');
	
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'hall_of_fame_player_detail.php';
	$container['account_id'] = $hofPlayer->getAccountID();
	$container['game_id'] = $var['game_id'];
	$return.=('<td align=center>'.create_link($container, $hofPlayer->getPlayerName()) .'</td>');
	$return.=('<td align=center>' . $amount . '</td>');
	$return.=('</tr>');
	return $return;
}

?>