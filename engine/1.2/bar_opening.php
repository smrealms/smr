<?php

//now we offer them a drink
echo '<h2>Drinks</h2><br>
Wanna buy a drink? I got some good stuff! Just what you need after a hard day\'s hunting.<br><br>';

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'bar_main.php';
$container['script'] = 'bar_buy_drink.php';
$container['action'] = 'drink';
print_button($container,'Buy a drink ($10)');
echo '&nbsp;&nbsp;&nbsp;&nbsp;';
$container['action'] = 'water';
print_button($container,'Buy some water ($10)');

/*print_form(create_container("skeleton.php", "bar_talk_bartender.php"));
print_submit("Talk to bartender");
print("</form>");*/

echo '<br><br><h2>Gambling</h2><br>So you\'re not the drinking type huh? Well how about some good ole gambling?<br><br>';

//check for winner
$db->query('SELECT prize FROM player_has_ticket WHERE game_id=' . $player->game_id . ' AND account_id=' . $player->account_id . ' AND time = 0 LIMIT 1');
if ($db->next_record()) {
	echo 'Congratulations. You have a winning lotto ticket.<br><br>';
	$container['script'] = 'bar_lotto_claim.php';
	$container['action'] = 'lotto_claim';
	print_button($container,'Claim Your Prize (' . number_format($db->f('prize')) . ' Cr)');
	echo '<br><br>';
}

$container['script'] = 'bar_gambling.php';
$container['action'] = 'lotto';
print_button($container,'Play the Galactic Lotto');

echo '&nbsp;&nbsp;&nbsp;&nbsp;';

$container['script'] = 'bar_gambling_bet.php';
$container['action'] = 'blackjack';
print_button($container,'Play Some Blackjack');

echo '<br><br><h2>Ship</h2><br>Well...of course you could always pay our painters to customize your ship name, or even spray on your favorite logo!<br><br>';
$container['script'] = 'bar_ship_name.php';
$container['action'] = 'customize';
print_button($container,'Customize Ship Name (1-3 SMR Credit(s))');

echo '<br><br><h2>Systems</h2><br>We just got in a new system that can send information from your scout drones or recent news directly to your main screen!  It only costs 1 SMR credit for 5 days!  Or you can buy a system to block these messages.<br><br>';
$container['script'] = 'bar_ticker_buy.php';
$container['action'] = 'system';
print_button($container,'Buy System (1 SMR Credit)');

echo '<br><br><h2>Maps</h2><br>New intelligence has just come in!  We now have full maps of EVERY galaxy!  We are willing to sell you the newest maps of each galaxy for 2 SMR credits each!<br><br>';

$container['script'] = 'bar_galmap_buy.php';
$container['action'] = 'map';
print_button($container,'Buy a Galaxy Map (2 SMR Credits)');

?>