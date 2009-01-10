<?

//now we offer them a drink
$PHP_OUTPUT.= '<h2>Drinks</h2><br />
Wanna buy a drink? I got some good stuff! Just what you need after a hard day\'s hunting.<br /><br />';

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'bar_main.php';
$container['script'] = 'bar_buy_drink.php';
$container['action'] = 'drink';
$PHP_OUTPUT.=create_button($container,'Buy a drink ($10)');
$PHP_OUTPUT.= '&nbsp;&nbsp;&nbsp;&nbsp;';
$container['action'] = 'water';
$PHP_OUTPUT.=create_button($container,'Buy some water ($10)');

$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'bar_talk_bartender.php'));
$PHP_OUTPUT.=create_submit('Talk to bartender');
$PHP_OUTPUT.=('</form>');

$PHP_OUTPUT.= '<br /><br /><h2>Gambling</h2><br />So you\'re not the drinking type huh? Well how about some good ole gambling?<br /><br />';

//check for winner
$db->query('SELECT prize FROM player_has_ticket WHERE game_id=' . $player->getGameID() . ' AND account_id=' . $player->getAccountID() . ' AND time = 0 LIMIT 1');
if ($db->next_record()) {
	$PHP_OUTPUT.= 'Congratulations. You have a winning lotto ticket.<br /><br />';
	$container['script'] = 'bar_lotto_claim.php';
	$container['action'] = 'lotto_claim';
	$PHP_OUTPUT.=create_button($container,'Claim Your Prize (' . number_format($db->f('prize')) . ' Cr)');
	$PHP_OUTPUT.= '<br /><br />';
}

$container['script'] = 'bar_gambling.php';
$container['action'] = 'lotto';
$PHP_OUTPUT.=create_button($container,'Play the Galactic Lotto');

$PHP_OUTPUT.= '&nbsp;&nbsp;&nbsp;&nbsp;';

$container['script'] = 'bar_gambling_bet.php';
$container['action'] = 'blackjack';
$PHP_OUTPUT.=create_button($container,'Play Some Blackjack');

$PHP_OUTPUT.= '<br /><br /><h2>Ship</h2><br />Well...of course you could always pay our painters to customize your ship name, or even spray on your favorite logo!<br /><br />';
$container['script'] = 'bar_ship_name.php';
$container['action'] = 'customize';
$PHP_OUTPUT.=create_button($container,'Customize Ship Name (1-3 SMR Credit(s))');

$PHP_OUTPUT.= '<br /><br /><h2>Systems</h2><br />We just got in a new system that can send information from your scout drones or recent news directly to your main screen!  It only costs 1 SMR credit for 5 days!  Or you can buy a system to block these messages.<br /><br />';
$container['script'] = 'bar_ticker_buy.php';
$container['action'] = 'system';
$PHP_OUTPUT.=create_button($container,'Buy System (1 SMR Credit)');

$PHP_OUTPUT.= '<br /><br /><h2>Maps</h2><br />New intelligence has just come in!  We now have full maps of EVERY galaxy!  We are willing to sell you the newest maps of each galaxy for 2 SMR credits each!<br /><br />';

$container['script'] = 'bar_galmap_buy.php';
$container['action'] = 'map';
$PHP_OUTPUT.=create_button($container,'Buy a Galaxy Map (2 SMR Credits)');

?>