<?

if (isset($var['game_id'])) $game_id = $var['game_id'];
$base = array();

if (empty($game_id))
{
	$topic = 'All Time Hall of Fame';

}
else
{
	$topic = Globals::getGameName($game_id).' Hall of Fame';
}
$smarty->assign('PageTopic',$topic);
$PHP_OUTPUT.=('<div align=center>');

$PHP_OUTPUT.=('Welcome to the Hall of Fame ' . stripslashes($account->HoF_name) . '!<br />The Hall of Fame is a comprehensive ');
$PHP_OUTPUT.=('list of player accomplishments.  Here you can view how players rank in many different ');
$PHP_OUTPUT.=('aspects of the game rather than just kills, deaths, and experience with the rankings system.<br /><br />');

$db->query('SELECT DISTINCT type FROM player_hof' . (isset($var['game_id']) ? ' WHERE game_id='.$var['game_id'] : '').' ORDER BY type');
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
$container['body'] = 'hall_of_fame_new.php';
if (isset($var['game_id'])) $container['game_id'] = $var['game_id'];
$viewing= '<span style="font-weight:bold;">Currently viewing: </span>'.create_link($container,isset($var['game_id'])?'Current HoF':'Global HoF');
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
		$container['body'] = 'hall_of_fame_new.php';
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
	$container['body'] = 'hall_of_fame_new.php';
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
		$container['body'] = 'hall_of_fame_new.php';
		if (isset($var['type']))
			$container['type'] = $var['type'];
		else
			$container['type'] = array();
		$container['type'][] = $type;
		if (isset($var['game_id']))
			$container['game_id'] = $var['game_id'];
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
	
	$rank=1;
	$foundMe=false;
	$viewType = $var['type'];
	$viewType[] = $var['view'];
	if($var['view'] == $DONATION_NAME)
		$db->query('SELECT account_id, sum(amount) as amount FROM account_donated ' .
				'GROUP BY account_id ORDER BY amount DESC LIMIT 25');
	else
		$db->query('SELECT account_id,amount FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false).(isset($var['game_id']) ? ' AND game_id=' . $var['game_id'] : ' GROUP BY type') .' ORDER BY amount DESC LIMIT 25');
	while($db->nextRecord())
	{
		if($db->getField('account_id') == $account->account_id)
			$foundMe = true;
		$PHP_OUTPUT .= displayHOFRow($rank++,$db->getField('account_id'),$db->getField('amount') );
	}
	if(!$foundMe)
	{
		if($var['view'] == $DONATION_NAME)
			$db->query('SELECT account_id, sum(amount) as amount FROM account_donated WHERE account_id='.$account->account_id.' LIMIT 1');
		else
			$db->query('SELECT account_id,amount FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false).(isset($var['game_id']) ? ' AND game_id=' . $var['game_id'] : ' GROUP BY type') .' AND account_id='.$account->account_id.' LIMIT 1');
		$amount = 0;
		if($db->nextRecord())
			$amount = $db->getField('amount');
		if($var['view'] == $DONATION_NAME)
			$db->query('SELECT count(account_id) as rank, sum(amount) AS amount FROM account_donated WHERE amount>' . $amount .
					' GROUP BY account_id LIMIT 1');
		else
			$db->query('SELECT count(account_id) as rank FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false).(isset($var['game_id']) ? ' AND game_id=' . $var['game_id'] : ' GROUP BY type') .' AND amount>'.$amount.' LIMIT 1');
		$rank = 1;
		if($db->nextRecord())
			$rank = $db->getField('rank') + 1;
		$PHP_OUTPUT .= displayHOFRow($rank,$account->account_id,$amount);
	}
}

$PHP_OUTPUT.=('</table></div>');

function displayHOFRow($rank,$accountID,$amount)
{
	global $account,$player,$var;
	$hofAccount =& SmrAccount::getAccount($accountID);
	if ($hofAccount->account_id == $account->account_id)
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
	$container['acc_id'] = $accountID;
	
	if (isset($var['game_id']))
	{
		$container['game_id'] = $var['game_id'];
		$container['sending_page'] = 'current_hof';
	}
	else
	{
		$container['game_id'] = $player->getGameID();
		$container['sending_page'] = 'hof';
	}
	$return.=('<td align=center'.$bold.'>'.create_link($container, $hofAccount->HoF_name) .'</td>');
	$return.=('<td align=center'.$bold.'>' . $amount . '</td>');
	$return.=('</tr>');
	return $return;
}

?>