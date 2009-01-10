<?
$db2 = new SmrMySqlDatabase();
$amount = $_REQUEST['amount'];
$account_id = $_REQUEST['account_id'];
if (!is_numeric($amount)) {

	create_error('Numbers only please');
	return;

}
$amount = round($amount);
if ($amount <= 0) {

	create_error('You can only tranfer a positive amount');
	return;

}

if ($amount > $account->get_credits()) {

	create_error('You can\'t transfer more than you have!');
	return;

}

$smarty->assign('PageTopic','Confirmation');

$PHP_OUTPUT.=('Are you sure you want to transfer '.$amount.' credits to<br />');

$db->query('SELECT * FROM account WHERE account_id = '.$account_id);
if ($db->next_record())
	$login = $db->f('login');

$db->query('SELECT * FROM player WHERE account_id = '.$account_id);
if ($db->nf()) {

	while ($db->next_record()) {

	    $player_name = stripslashes($db->f('player_name'));
    	$game_id = $db->f('game_id');

	    $db2->query('SELECT * FROM game WHERE game_id = '.$game_id);
    	if ($db2->next_record())
			$game_name = $db2->f('game_name');

		$PHP_OUTPUT.=($player_name.' in game '.$game_name.'('.$game_id.')<br />');

	}

} else
	$PHP_OUTPUT.=('Player with login name '.$login.'?<br />');

$PHP_OUTPUT.=('<p>&nbsp;</p>');

$container = array();
$container['url'] = 'preferences_processing.php';
$container['account_id'] = $account_id;
$container['amount'] = $amount;
$PHP_OUTPUT.=create_echo_form($container);

$PHP_OUTPUT.=create_submit('Yes');
$PHP_OUTPUT.=('&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('No');
$PHP_OUTPUT.=('</form>');

?>