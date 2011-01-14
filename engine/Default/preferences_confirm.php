<?php
$db2 = new SmrMySqlDatabase();
if(isset($_REQUEST['amount']))
	SmrSession::updateVar('amount',$_REQUEST['amount']);
if(isset($_REQUEST['account_id']))
	SmrSession::updateVar('account_id',$_REQUEST['account_id']);
$amount = $var['amount'];
$account_id = $var['account_id'];
if (!is_numeric($amount))
	create_error('Numbers only please');
$amount = round($amount);
if ($amount <= 0)
	create_error('You can only tranfer a positive amount');

if ($amount > $account->getSmrCredits())
	create_error('You can\'t transfer more than you have!');

$template->assign('PageTopic','Confirmation');

$PHP_OUTPUT.=('Are you sure you want to transfer '.$amount.' credits to<br />');

$db->query('SELECT hof_name FROM account WHERE account_id = '.$account_id);
if ($db->nextRecord())
	$hofName = $db->getField('hof_name');

//$db->query('SELECT * FROM player WHERE account_id = '.$account_id);
//if ($db->getNumRows())
//{
//	while ($db->nextRecord())
//	{
//	    $player_name = stripslashes($db->getField('player_name'));
//    	$game_id = $db->getField('game_id');
//
//	    $db2->query('SELECT * FROM game WHERE game_id = '.$game_id);
//    	if ($db2->nextRecord())
//			$game_name = $db2->getField('game_name');
//
//		$PHP_OUTPUT.=($player_name.' in game '.$game_name.'('.$game_id.')<br />');
//	}
//}
//else
	$PHP_OUTPUT.=('Player with HoF name '.$hofName.'?<br />');

$PHP_OUTPUT.=('<br/><h3>Please make sure this is definitely the correct person before confirming.</h3>');
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