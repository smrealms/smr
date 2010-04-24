<?php

//bar gambling...
//get action and vars
$action = $var['action'];
$time = TIME;

//do we need to process a ticket?
if ($action == 'process')
{
	if ($player->getCredits() < 1000000)
	{
		create_error('There once was a man with less than $1,000,000...wait...thats you!');
		return;
	}
	while (true)
	{
		//stop double entries...250,000 usecs at a time so as to not slow them down too much.
		$db->query('SELECT * FROM player_has_ticket WHERE game_id = '.$player->getGameID().' AND ' .
					'account_id = '.$player->getAccountID().' AND time = '.$time);
		if ($db->getNumRows())
		{
			usleep(250000);
			$time = time();
		} else break;
	}
	$db->query('INSERT INTO player_has_ticket (game_id, account_id, time) VALUES (' .
				$player->getGameID().', '.$player->getAccountID().', '.$time.')');
	$player->decreaseCredits(1000000);
	$player->increaseHOF(1000000,array('Bar','Lotto', 'Money', 'Spent'));
	$player->increaseHOF(1,array('Bar','Lotto', 'Tickets Bought'));
	$db->query('SELECT count(*) as num FROM player_has_ticket WHERE game_id = '.$player->getGameID() .
				' AND account_id = '.$player->getAccountID().' AND time > 0 GROUP BY account_id');
	$db->nextRecord();
	$num = $db->getField('num');
	$message=('<div align=center>Thanks for your purchase and good luck!!!  You currently');
	$message.=(' own '.$num.' tickets!</div><br />');
	
	$container=create_container('skeleton.php','bar_main.php');
	$container['script']='bar_opening.php';
	$container['message'] = $message;
	forward($container);
	
}
elseif ($action == 'blackjack')
{
	$message='';
	//num of decks and cards
	$decks = 1;
	$max_cards = 52 * $decks;
	//commonly used functions for bj
	function draw_card($decks,$curr_cards)
	{
		//get a card to give this person
		//get real values of $curr_cards (1-52)
		$real_cards = array();
		//find the values of the currently used cards of the deck
		foreach ($curr_cards as $key => $value)
		{
			list($first, $second, $third) = explode('-', $value);
			if ($first == 'A') $first = 1;
			elseif ($first == 'J') $first = 11;
			elseif ($first == 'Q') $first = 12;
			elseif ($first == 'K') $first = 13;
			if ($second == 'hearts') $second = 1;
			elseif ($second == 'clubs') $second = 2;
			elseif ($second == 'diamonds') $second = 3;
			elseif ($second == 'spades') $second = 4;
			if (empty($third)) $third = 1;
			$real_cards[] = ($first + (13 * ($second - 1))) * $third;
		}
				
		$max = 52 * $decks;
		//1=ace of H, 13=king of H, 14=ace of C, 26=king of C
		//27=ace of D, 39=king of D, 40=ace of S, 52=king of S
		$result = mt_rand(1,$max);
		//no cards twice
		while (in_array($result, $real_cards)) $result = mt_rand(1,$max);
		$down = 1;
		while ($result > 52)
		{
			$result -= 52;
			$down += 1;
		}

		//get it in the format we want it.
		$suit = ceil($result / 13);
		$result -= (($suit - 1) * 13);
		if ($suit == 1) $suit = 'hearts';
		elseif ($suit == 2) $suit = 'clubs';
		elseif ($suit == 3) $suit = 'diamonds';
		elseif ($suit == 4) $suit = 'spades';
		if ($result == 1) $result = 'A';
		elseif ($result == 11) $result = 'J';
		elseif ($result == 12) $result = 'Q';
		elseif ($result == 13) $result = 'K';
		$result = $result.'-'.$suit.'-'.$down;
		return $result;
	}
	
	function get_value($deck)
	{
		//this function used to find the value of a player's/bank's cards
		//if this is just one card push it into an array so we can run the func
		if (!is_array($deck)) $deck = array($deck);
		$curr_aces = 0;
		$return_val = 0;
		foreach ($deck as $key => $card_val)
		{
			//get total value of cards
			list($first, $second) = explode('-', $card_val);
			if ($first == 'A')
			{
				$first = 11;
				$curr_aces += 1;
			} elseif ($first == 'J') $first = 10;
			elseif ($first == 'Q') $first = 10;
			elseif ($first == 'K') $first = 10;
			$return_val += $first;
		}
		while ($return_val > 21 && $curr_aces > 0)
		{
			//if we have aces and > 21 score we subtract to make it a 1 instead of 11
			$return_val -= 10;
			$curr_aces -= 1;
		}
		return $return_val;
	}
	
	function create_card($card, $show)
	{
		//picture directory
		$dir = URL . '/images';
		//only display what the card really is if they want to
		$card_height = 100;
		$card_width = 125;
		list($first, $second) = explode('-', $card);
		$return=('<td>');
		//lets try and echo cards
		$return.=('<table style="border:1px solid green"><tr><td><table><tr><td valign=top align=left height='.$card_height.' width='.$card_width.'>');
		if ($show) $return.=('<h1>'.$first.'<img src="'.$dir.'/'.$second.'.gif"></h1></td></tr>');
		else $return.=('</td></tr>');
		$return.=('<tr><td valign=bottom align=right height='.$card_height.' width='.$card_width.'>');
		if ($show) $return.=('<h1><img src="'.$dir.'/'.$second.'.gif">'.$first.'</h1></td></tr></table>');
		else $return.=('</td></tr></table>');
		$return.=('</td></tr></table></td>');
		return $return;
	}
	
	function check_for_win($comp, $play)
	{
		//TODO: Hack to prevent an error, fix it
		global $player_card; 
		//does the player win
		if (sizeof($player_card) == 2 && get_value($player_card) == 21) return 'bj';
		elseif ($play > $comp && $comp <= 21 && $play <= 21) return 'yes';
		elseif ($play == $comp && $comp <= 21) return 'tie';
		elseif ($comp > 21) return 'yes';
		else return 'no';
	}

	if (isset($var['player_does'])) $do = $var['player_does'];
	else $do = 'nothing';
	//new game if $do == nothing
	if ($do == 'nothing')
	{
		if (isset($var['bet'])) $bet = $var['bet'];	
		else $bet = $_REQUEST['bet'];
		if (!is_numeric($bet))
			create_error('Only Numbers Please');
		$bet = round($bet);
		if ($player->getCredits() < $bet)
			create_error('Not even enough to play BlackJack...you need to trade!');
		if ($bet == 0)
			create_error('We don\'t want you here if you don\'t want to play with cash!');
		if ($bet > 100 && $player->getNewbieTurns() > 0)
			create_error('Sorry.  According to Galactic Laws we can only play with up to 100 credits while under newbie protection.');
		if ($bet > 10000)
			create_error('Sorry.  According to Galactic Laws we can only play with up to 10,000 credits');
		if ($bet < 0)
			create_error('Yeah...we are gonna give you money to play us! GREAT IDEA!!');
		$player->decreaseCredits($bet);
		$player->update();
		
		//first we deal some cards...player,ai,player,ai
		$ai_aces = 0;
		if (isset($var['cards'])) $cards = $var['cards'];
		if (empty($cards)) $cards = array();
		$player_curr_card = draw_card($decks,$cards);
		$player_card[] = $player_curr_card;
		$cards[] = $player_curr_card;
		if (sizeof($cards) >= $max_cards) $cards = array();
		$ai_curr_card = draw_card($decks,$cards);
		$ai_card[] = $ai_curr_card;
		$cards[] = $ai_curr_card;
		if (sizeof($cards) >= $max_cards) $cards = array();
		$player_curr_card = draw_card($decks,$cards);
		$player_card[] = $player_curr_card;
		$cards[] = $player_curr_card;
		if (sizeof($cards) >= $max_cards) $cards = array();
		$ai_curr_card = draw_card($decks,$cards);
		$ai_card[] = $ai_curr_card;
		$cards[] = $ai_curr_card;
		if (sizeof($cards) >= $max_cards) $cards = array();
		//find a play_val variable in case they get bj first hand...lucky
		$play_val = get_value($player_card);
	}
	if (isset($var['cards']) && !isset($cards)) $cards = $var['cards'];
	if (isset($var['bet'])) $bet = $var['bet'];
	if (isset($var['player_card']))
	{
		$player_card = $var['player_card'];
		$ai_card = $var['ai_card'];
		$play_val = $var['player_val'];
	}
	if ($do == 'HIT')
	{
		$player_curr_card = draw_card($decks,$cards);
		$player_card[] = $player_curr_card;
		$cards[] = $player_curr_card;
		if (sizeof($cards) >= $max_cards) $cards = array();
		$play_val = get_value($player_card);
	}
	
	//only display if we wont display later..
	if ($do != 'STAY' && get_value($player_card) != 21)
	{
		//heres the AIs cards
		$i = 1;
		if (get_value($ai_card) == 21 && sizeof($ai_card) == 2)
			$message.=('<div align=center><h1><font color=red>Bank Wins</font></h1></div>');
		$message.=('<div align=center>Bank\'s Cards are</div><br /><table align=center><tr>');
		foreach ($ai_card as $key => $value)
		{
			if ($key == 0)
			{
				//do we need a new row?
				if ($i == 4 || $i == 7 || $i == 10) $message.=('</tr><tr>');
				$message.=create_card($value, TRUE);
				$curr_ai_card = array();
				$curr_ai_card[] = $value;
				//get curr val of this card...for the at least part
				$ai_val = get_value($curr_ai_card);
				$i++;
			}
			else
			{
				//lets try and echo cards
				//new row?			
				if ($i == 4 || $i == 7 || $i == 10) $message.=('</tr><tr>');
				if (get_value($ai_card) == 21 || get_value($player_card) >= 21) $message.=create_card($value, TRUE);
				else $message.=create_card($value, FALSE);
				$i++;						
			}
		}

		$message.=('</td></tr></table>');
		if (get_value($ai_card) == 21 && sizeof($ai_card) == 2)
		{
			$message.=('<div align=center>Bank has BLACKJACK!</div><br />');
			$win = 'no';
		}
		elseif (get_value($player_card) >= 21) $message.=('<div align=center>Bank has ' . get_value($ai_card) . '</div><br /><br />');
		else $message.=('<div align=center>Bank has at least '.$ai_val.'</div><br />');
	}
	
	if ($do == 'STAY' || get_value($player_card) == 21)
	{
		$db->query('SELECT * FROM blackjack WHERE game_id = '.$player->getGameID().' AND ' .
					'account_id = '.$player->getAccountID());
		if ($db->nextRecord()) $old_card = unserialize($db->getField('last_hand'));
		if ($old_card == $player_card)
			create_error('You can\'t keep the same cards twice! Note:Next time your account will be logged!');
		$db->query('REPLACE INTO blackjack (game_id, account_id, last_hand) VALUES ' .
					'('.$player->getGameID().', '.$player->getAccountID().', ' . $db->escape_string(serialize($player_card)) . ')');
		//heres the Banks cards
		$i = 1;

		if (!(sizeof($player_card) == 2 && get_value($player_card) == 21))
			while (get_value($ai_card) < 17)
			{
				$ai_curr_card = draw_card($decks,$cards);
				$ai_card[] = $ai_curr_card;
				$cards[] = $ai_curr_card;
				if (sizeof($cards) >= $max_cards) $cards = array();
				
			}
		$win = check_for_win(get_value($ai_card), get_value($player_card));
		if ($win == 'yes' || $win == 'bj') $message.=('<div align=center><h1><font color=red>You Win</font></h1></div>');
		elseif ($win == 'tie') $message.=('<div align=center><h1><font color=red>TIE Game</font></h1></div>');
		else $message.=('<div align=center><h1><font color=red>Bank Wins</font></h1></div>');
		$message.=('<div align=center>Bank\'s Cards are</div><br /><table align=center><tr>');
		foreach ($ai_card as $key => $value)
		{
			//now row?			
			if ($i == 4 || $i == 7 || $i == 10) $message.=('</tr><tr>');
			$message.=create_card($value, TRUE);
			$i++;
		}
		$message.=('</td></tr></table><div align=center>');
		if (get_value($ai_card) > 21) $message.=('Bank <font color=red><b>BUSTED</b></font><br /><br />');
		else $message.=('Bank has ' . get_value($ai_card) . '<br /><br />');
		$message.=('</div>');
	}
	$message.=('<hr style="border:1px solid green;width:50%" noshade>');
	$i = 1;

	$val1 = get_value($player_card);

	$message.=('<div align=center>Your Cards are</div><br /><table align=center><tr>');
	foreach ($player_card as $key => $value)
	{
		if ($i == 4 || $i == 7 || $i == 10) $message.=('</tr><tr>');
		$message.=create_card($value, TRUE);
		$i++;
	}
	$message.=('</td></tr></table>');

	$message.=('<div align=center>You have a total of ' . get_value($player_card) . ' </div><br />');
	//check for win
	if ($do == 'STAY')
	{
		$win = check_for_win(get_value($ai_card), get_value($player_card));
	}
	$message.=('<div align=center>');
	if (get_value($player_card) > 21)
	{
		$message.=('You have <font color=red><b>BUSTED</b></font>');
		$bet = $var['bet'];
		$player->increaseHOF($bet,array('Blackjack','Money','Lost'));
		$player->increaseHOF(1,array('Blackjack','Results','Lost'));
		$container = create_container('skeleton.php','bar_main.php');
		$container['script'] = 'bar_gambling_processing.php';
		$container['cards'] = $cards;
		$container['action'] = 'blackjack';
		$container['bet'] = $bet;
		$message.=create_echo_form($container);
		$message.=create_submit('Play Some More ($'.$bet.')');
		$message.=('</form>');
		$message.=('</div>');
	}
	else if(!isset($win) && get_value($player_card) < 21)
	{
		$container = create_container('skeleton.php','bar_main.php');
		$container['script'] = 'bar_gambling_processing.php';
		$container['cards'] = $cards;
		$container['player_card'] = $player_card;
		$container['action'] = 'blackjack';
		$container['player_does'] = 'HIT';
		$container['ai_card'] = $ai_card;
		$container['ai_aces'] = $ai_aces;
		$container['bet'] = $bet;
		$container['player_val'] = $val1;
		$message.=create_echo_form($container);
		$message.=create_submit('HIT');
		$message.=('<br /><small><br /></small></form>');
		$container['player_does'] = 'STAY';
		$message.=create_echo_form($container);
		$message.=create_submit('STAY');
		$message.=('</form></div>');
	}
	else if(isset($win))
	{
		//we have a winner...but who!
		if (empty($bet)) $bet = $var['bet'];
		if ($win == 'bj')
		{
			$player->increaseCredits($bet * 2.5);
			$stat = ($bet * 2.5) - $bet;
			$player->update();
			$player->increaseHOF($stat, array('Blackjack','Money','Won'));
			$player->increaseHOF(1, array('Blackjack','Results','Won'));
			$message.=('You have won $' . number_format($bet * 2.5) . ' credits!');
		}
		elseif ($win == 'yes')
		{
			$player->increaseCredits($bet * 2);
			$stat = ($bet * 2) - $bet;
			$player->update();
			$player->increaseHOF($stat,array('Blackjack','Money','Won'));
			$player->increaseHOF(1, array('Blackjack','Results','Won'));
			$message.=('You have won $' . number_format($bet * 2) . ' credits!');
		}
		elseif ($win == 'tie')
		{
			$player->increaseCredits($bet);
			$player->update();
			$player->increaseHOF(1, array('Blackjack','Results','Draw'));
			$message.=('You have won back your $' . number_format($bet) . ' credits.');
		}
		else
		{
			$player->increaseHOF($bet,array('Blackjack','Money','Lost'));
			$player->increaseHOF(1,array('Blackjack','Results','Lost'));
		}
		$container = create_container('skeleton.php','bar_main.php');
		$container['script'] = 'bar_gambling_processing.php';
		$container['action'] = 'blackjack';
		$container['cards'] = $cards;
		$container['bet'] = $bet;
		$message.=create_echo_form($container);
		$message.=create_submit('Play Some More ($'.$bet.')');
		$message.=('</form>');
		$message.=('</div>');
	}
	elseif ($val1 == 21)
	{
		if (get_value($ai_card) != 21)
		{
			if (sizeof($player_card) == 2) $winnings = 2.5;
			else $winnings = 2;
			if (empty($bet)) $bet = $var['bet'];
			$player->increaseCredits($bet * $winnings);
			$stat = ($bet * $winnings) - $bet;
			$player->update();
			$player->increaseHOF($stat,array('Blackjack','Money','Win'));
			$player->increaseHOF(1,array('Blackjack','Results','Win'));
			$message.=('You have won $' . number_format($bet * $winnings) . ' credits!');
		}
		else if(sizeof($ai_card) > 2)
		{
			if (empty($bet)) $bet = $var['bet'];
			$winnings = 1;
			$player->increaseCredits($bet * $winnings);
			$stat = ($bet * $winnings) - $bet;
			$player->update();
			$player->increaseHOF($stat,array('Blackjack','Money','Win'));
			$player->increaseHOF(1,array('Blackjack','Results','Win'));
			$message.=('You have won back your $' . number_format($bet * $winnings) . ' credits!');
		}
		else
		{
			//AI has BJ already...sorry
			if (empty($bet)) $bet = $var['bet'];
			$player->increaseHOF($bet,array('Blackjack','Money','Lost'));
			$player->increaseHOF(1,array('Blackjack','Results','Lost'));
		}
		$container = create_container('skeleton.php','bar_main.php');
		$container['script'] = 'bar_gambling_processing.php';
		$container['action'] = 'blackjack';
		$container['cards'] = $cards;
		$container['bet'] = $bet;
		$message.=create_echo_form($container);
		$message.=create_submit('Play Some More ($'.$bet.')');
		$message.=('</form>');
		$message.=('</div>');
	}
	$container=create_container('skeleton.php','bar_main.php');
	$container['script']='bar_gambling_bet.php';
	$container['message'] = $message;
	$container['DisableAjax'] = true;
	forward($container);
}
?>