<?

if (isset($var['game_id'])) $game_id = $var['game_id'];
$base = array();

if (empty($game_id))
{
//	$base[] = 'Overall';
//	$base[] = 'Per Game, / games_joined';
	$topic = 'All Time Hall of Fame';

}
else
{
//	$base[] = 'Total';
	$db->query('SELECT * FROM game WHERE game_id = '.$game_id);
	if ($db->nextRecord())
	{
		$name = $db->getField('game_name');
		$topic = $name.' Hall of Fame';
	}
	else
		$topic = 'Somegame Hall of Fame';
	
}
$smarty->assign('PageTopic',$topic);
$PHP_OUTPUT.=('<div align=center>');

$PHP_OUTPUT.=('Welcome to the Hall of Fame ' . stripslashes($account->HoF_name) . '!<br />The Hall of Fame is a comprehensive ');
$PHP_OUTPUT.=('list of player accomplishments.  Here you can view how players rank in many different ');
$PHP_OUTPUT.=('aspects of the game rather than just kills, deaths, and experience with the rankings system.<br /><br />');

$db->query('SELECT DISTINCT type FROM player_hof' . (isset($var['game_id']) ? ' WHERE game_id='.$var['game_id'] : '').' ORDER BY type');
$hofTypes = array();
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
	
	$viewType = $var['type'];
	$viewType[] = $var['view'];
	
	$rank=1;
	$foundMe=false;
	$db->query('SELECT account_id,amount FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false).(isset($var['game_id']) ? ' AND game_id=' . $var['game_id'] : ' GROUP BY type') .' ORDER BY amount DESC LIMIT 25');
	while($db->nextRecord())
	{
		if($db->getField('account_id') == $account->account_id)
			$foundMe = true;
		$PHP_OUTPUT .= displayHOFRow($rank++,$db->getField('account_id'),$db->getField('amount') );
	}
	if(!$foundMe)
	{
		$db->query('SELECT account_id,amount FROM player_hof WHERE type='.$db->escapeArray($viewType,true,':',false).(isset($var['game_id']) ? ' AND game_id=' . $var['game_id'] : ' GROUP BY type') .' ORDER BY amount DESC LIMIT 25');
		$amount = 0;
		if($db->nextRecord())
			$amount = $db->getField('amount');
		$PHP_OUTPUT .= displayHOFRow('?',$account->account_id,$amount);
	}
}

$PHP_OUTPUT.=('</table></div>');

function displayHOFRow($rank,$accountID,$amount)
{
	global $account,$player;
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
	
	if (isset($game_id))
	{
		$container['game_id'] = $game_id;
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

//category(Display,Array containing subcategories (info after , is the info for sql),stat name in db)
//if (empty($game_id))
//	category('<b>Money Donated to SMR</b>', array('Overall'), 'Not Needed',$PHP_OUTPUT);
//category('Kills', array_merge($base,array('Per Death, / deaths')), 'kills',$PHP_OUTPUT);
//category('Deaths', $base, 'deaths',$PHP_OUTPUT);
//category('Planet Busts', $base, 'planet_busts',$PHP_OUTPUT);
//category('Planet Levels Busted', $base, 'planet_bust_levels',$PHP_OUTPUT);
//category('Damage Done to Planets', array_merge($base,array('Experience Gained, / 4')), 'planet_damage',$PHP_OUTPUT);
//category('Port Raids', $base, 'port_raids',$PHP_OUTPUT);
//category('Port Levels Raided', $base, 'port_raid_levels',$PHP_OUTPUT);
//category('Damage Done to Ports', array_merge($base,array('Experience Gained, / 20')), 'port_damage',$PHP_OUTPUT);
//category('Sectors Explored', $base, 'sectors_explored',$PHP_OUTPUT);
//category('Goods Traded', $base, 'goods_traded',$PHP_OUTPUT);
//category('Trade Profit', array_merge($base,array('Per Good Traded, / goods_traded', 'Per Experience Traded, / experience_traded')), 'trade_profit',$PHP_OUTPUT);
//category('Trade Sales', array_merge($base,array('Per Good Traded, / goods_traded', 'Per Experience Traded, / experience_traded')), 'trade_sales',$PHP_OUTPUT);
//category('Experience Traded', array_merge($base,array('Per Good Traded, / goods_traded')), 'experience_traded',$PHP_OUTPUT);
//category('Bounties Collected', $base, 'bounties_claimed',$PHP_OUTPUT);
//category('Credits from Bounties Collected', array_merge($base,array('Per Bounty Claimed, / bounties_claimed')), 'bounty_amount_claimed',$PHP_OUTPUT);
//category('Bounties Place on Player', $base, 'bounty_amount_on',$PHP_OUTPUT);
//category('Military Payment Claimed', $base, 'military_claimed',$PHP_OUTPUT);
//category('Damage Done to Other Players', array_merge($base,array('Per Kill, / kills','Experience Gained, / 4')), 'player_damage',$PHP_OUTPUT);
//category('Experience Gained from Killing', array_merge($base,array('Per Kill, / kills')), 'kill_exp',$PHP_OUTPUT);
//category('Money Gained from Killing', $base, 'money_gained',$PHP_OUTPUT);
//category('Experience of Players Killed', array_merge($base,array('Per Kill, / kills')), 'traders_killed_exp',$PHP_OUTPUT);
//category('Cost of Ships Killed', $base, 'killed_ships',$PHP_OUTPUT);
//category('Cost of Ships Died In', $base, 'died_ships',$PHP_OUTPUT);
//category('Mines Bought', $base, 'mines',$PHP_OUTPUT);
//category('Combat Drones Bought', $base, 'combat_drones',$PHP_OUTPUT);
//category('Scout Drones', $base, 'scout_drones',$PHP_OUTPUT);
//category('Forces Bought', $base, 'mines + combat_drones + scout_drones',$PHP_OUTPUT);
//category('Blackjack Winnings', array_merge($base,array('To Losings, / blackjack_lose')), 'blackjack_win',$PHP_OUTPUT);
//category('Blackjack Loses', $base, 'blackjack_lose',$PHP_OUTPUT);
//category('Lotto Winnings', $base, 'lotto',$PHP_OUTPUT);
//category('Drinks at Bars', $base, 'drinks',$PHP_OUTPUT);
//category('Turns Since Last Death', $base, 'turns_used',$PHP_OUTPUT);


?>