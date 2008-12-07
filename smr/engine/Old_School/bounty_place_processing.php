<?
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);

$container = array();
$container['url'] = 'skeleton.php';
if ($sector->has_hq()) {

	$container['body'] = 'government.php';
	$type = 'HQ';

} else {

	$container['body'] = 'underground.php';
	$type = 'UG';

}
$action = $_REQUEST['action'];
// if we don't have a yes we leave immediatly
if ($action != 'Yes')
	forward($container);

// get values from container
$amount = $var['amount'];
$account_id = $var['account_id'];
if (!$amount)
	create_error('You must enter an amount');
if ($amount < 0)
	create_error('You must enter a positive amount');
// take the bounty from the cash
$player->decreaseCredits($amount);
$player->update();

$db2 = new SMR_DB();

$db->query('SELECT * FROM bounty ' .
		   'WHERE game_id = '.$player->getGameID().' AND ' .
				 'account_id = '.$account_id.' AND ' .
				 'claimer_id = 0 AND ' .
				 'type = '.$db->escapeString($type).' LIMIT 1');
if ($db->nf()) {

	$db->next_record();
	//$days = (TIME - $db->f('time')) / 60 / 60 / 24;
	//$curr_amount = $db->f('amount') * pow(1.05,$days);
	$curr_amount = $db->f('amount');
	$new_amount = $curr_amount + $amount;
	$db2->query('UPDATE bounty SET amount = '.$new_amount.', time = '.TIME.' WHERE game_id = '.$player->getGameID().' AND account_id = '.$account_id.' AND claimer_id = 0 AND type = '.$db->escapeString($type));
	//$PHP_OUTPUT.=('Added bounty....$curr_amount + $amount<br>UPDATE bounty SET amount = $new_amount, time = TIME WHERE game_id = '.$player->getGameID().' AND account_id = '.$account_id.' AND type = '.$db->escapeString($type'');

} else {

	$db->query('INSERT INTO bounty (account_id, game_id, bounty_id, type, claimer_id, amount, time) VALUES ('.$account_id.', '.$player->getGameID().', NULL, '.$db->escapeString($type).' , 0, '.$amount.', '.TIME.')');
	//$PHP_OUTPUT.=('First<br>INSERT INTO bounty (account_id, game_id, bounty_id, type, claimer_id, amount, time) VALUES ($account_id, $player->getGameID(), $bounty_id, '.$db->escapeString($type' , 0, $amount, TIME)');

}

$placed =& SmrPlayer::getPlayer($account_id, $player->getGameID());
$placed->update_stat('bounty_amount_on', $amount);

forward($container);

?>
