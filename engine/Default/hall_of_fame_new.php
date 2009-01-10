<?

function category($name, $options, $row,&$PHP_OUTPUT) {

	global $var;
	$i = 0;
	//table name thing goes here
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align=center>'.$name.'</td>');
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'hall_of_fame_new_detail.php';
	$container['category'] = $name;
	$container['row'] = $row;
	if (isset($var['game_id']))
		$container['game_id'] = $var['game_id'];
	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<td align=center valign=middle>');
	foreach($options as $echo) {
		
		$i++;
		@list($one, $two) = split (',', $echo);
		if (isset($two)) $PHP_OUTPUT.=('<input type=hidden name=mod[] value="'.$echo.'">');
		$PHP_OUTPUT.=create_submit($one);
		$PHP_OUTPUT.=('&nbsp;');
		if ($i % 3 == 0) $PHP_OUTPUT.=('<br />');
		//unset vars for next sub cat
		unset($one, $two);
		
	}
	$PHP_OUTPUT.=('</td></form></tr>');

}
if (isset($var['game_id'])) $game_id = $var['game_id'];
$base = array();

if (empty($game_id)) {
	
	$base[] = 'Overall';
	$base[] = 'Per Game, / games_joined';
	$topic = 'All Time Hall of Fame';

} else {
	
	$base[] = 'Total';
	$db->query('SELECT * FROM game WHERE game_id = '.$game_id);
	if ($db->next_record()) {
		
		$name = $db->f('game_name');
		$topic = $name.' Hall of Fame';
		
	} else $topic = 'Somegame Hall of Fame';
	
}
$PHP_OUTPUT.=('<div align=center>');

$smarty->assign('PageTopic',$topic);

$PHP_OUTPUT.=('Welcome to the Hall of Fame ' . stripslashes($account->HoF_name) . '!<br />The Hall of Fame is a comprehensive ');
$PHP_OUTPUT.=('list of player accomplishments.  Here you can view how players rank in many different ');
$PHP_OUTPUT.=('aspects of the game rather than just kills, deaths, and experience with the rankings system.<br />');
$PHP_OUTPUT.=('The Hall of Fame is updated only once every 24 hours on midnight.<br />');

$PHP_OUTPUT.= create_table();
$PHP_OUTPUT.=('<tr><th align=center>Category</th><th align=center width=60%>Subcategory</th></tr>');

//category(Display,Array containing subcategories (info after , is the info for sql),stat name in db)
if (empty($game_id))
	category('<b>Money Donated to SMR</b>', array('Overall'), 'Not Needed',$PHP_OUTPUT);
category('Kills', array_merge($base,array('Per Death, / deaths')), 'kills',$PHP_OUTPUT);
category('Deaths', $base, 'deaths',$PHP_OUTPUT);
category('Planet Busts', $base, 'planet_busts',$PHP_OUTPUT);
category('Planet Levels Busted', $base, 'planet_bust_levels',$PHP_OUTPUT);
category('Damage Done to Planets', array_merge($base,array('Experience Gained, / 4')), 'planet_damage',$PHP_OUTPUT);
category('Port Raids', $base, 'port_raids',$PHP_OUTPUT);
category('Port Levels Raided', $base, 'port_raid_levels',$PHP_OUTPUT);
category('Damage Done to Ports', array_merge($base,array('Experience Gained, / 20')), 'port_damage',$PHP_OUTPUT);
category('Sectors Explored', $base, 'sectors_explored',$PHP_OUTPUT);
category('Goods Traded', $base, 'goods_traded',$PHP_OUTPUT);
category('Trade Profit', array_merge($base,array('Per Good Traded, / goods_traded', 'Per Experience Traded, / experience_traded')), 'trade_profit',$PHP_OUTPUT);
category('Trade Sales', array_merge($base,array('Per Good Traded, / goods_traded', 'Per Experience Traded, / experience_traded')), 'trade_sales',$PHP_OUTPUT);
category('Experience Traded', array_merge($base,array('Per Good Traded, / goods_traded')), 'experience_traded',$PHP_OUTPUT);
category('Bounties Collected', $base, 'bounties_claimed',$PHP_OUTPUT);
category('Credits from Bounties Collected', array_merge($base,array('Per Bounty Claimed, / bounties_claimed')), 'bounty_amount_claimed',$PHP_OUTPUT);
category('Bounties Place on Player', $base, 'bounty_amount_on',$PHP_OUTPUT);
category('Military Payment Claimed', $base, 'military_claimed',$PHP_OUTPUT);
category('Damage Done to Other Players', array_merge($base,array('Per Kill, / kills','Experience Gained, / 4')), 'player_damage',$PHP_OUTPUT);
category('Experience Gained from Killing', array_merge($base,array('Per Kill, / kills')), 'kill_exp',$PHP_OUTPUT);
category('Money Gained from Killing', $base, 'money_gained',$PHP_OUTPUT);
category('Experience of Players Killed', array_merge($base,array('Per Kill, / kills')), 'traders_killed_exp',$PHP_OUTPUT);
category('Cost of Ships Killed', $base, 'killed_ships',$PHP_OUTPUT);
category('Cost of Ships Died In', $base, 'died_ships',$PHP_OUTPUT);
category('Mines Bought', $base, 'mines',$PHP_OUTPUT);
category('Combat Drones Bought', $base, 'combat_drones',$PHP_OUTPUT);
category('Scout Drones', $base, 'scout_drones',$PHP_OUTPUT);
category('Forces Bought', $base, 'mines + combat_drones + scout_drones',$PHP_OUTPUT);
category('Blackjack Winnings', array_merge($base,array('To Losings, / blackjack_lose')), 'blackjack_win',$PHP_OUTPUT);
category('Blackjack Loses', $base, 'blackjack_lose',$PHP_OUTPUT);
category('Lotto Winnings', $base, 'lotto',$PHP_OUTPUT);
category('Drinks at Bars', $base, 'drinks',$PHP_OUTPUT);
category('Turns Since Last Death', $base, 'turns_used',$PHP_OUTPUT);

$PHP_OUTPUT.=('</table></div>');

?>