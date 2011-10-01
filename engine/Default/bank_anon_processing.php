<?php
$action = $_REQUEST['action'];
if (!isset($action) || ($action != 'Deposit' && $action != 'Withdraw'))
    create_error('You must choose if you want to deposit or withdraw.');
$amount = $_REQUEST['amount'];
// only whole numbers allowed
$amount = floor($amount);
$account_num = $var['AccountNumber'];
// no negative amounts are allowed
if ($amount <= 0)
    create_error('You must actually enter an amount > 0!');

if ($action == 'Deposit') {

    if ($player->getCredits() < $amount)
        create_error('You don\'t own that much money!');

    $player->decreaseCredits($amount);
    $db->query('SELECT * FROM anon_bank_transactions WHERE game_id = '.$player->getGameID().' AND anon_id = '.$account_num.' ORDER BY transaction_id DESC LIMIT 1');
    if ($db->nextRecord())
        $trans_id = $db->getField('transaction_id') + 1;
    else
        $trans_id = 1;
    $db->query('INSERT INTO anon_bank_transactions (account_id, game_id, anon_id, transaction_id, transaction, amount, time) ' .
                            'VALUES ('.$player->getAccountID().', '.$player->getGameID().', '.$account_num.', '.$trans_id.', \'Deposit\', '.$amount.', '.TIME.')');
    $db->query('UPDATE anon_bank SET amount = amount + '.$amount.' WHERE game_id = '.$player->getGameID().' AND anon_id = '.$account_num);
    $db->query('SELECT * FROM anon_bank WHERE game_id = '.$player->getGameID().' AND anon_id = '.$account_num);
    $db->nextRecord();
    $total = $db->getField('amount');
    //too much money?
//	if ($total > 4294967295) {
//		
//		$overflow = $total - 4294967295;
//		$db->query('UPDATE anon_bank SET amount = amount - '.$overflow.' WHERE game_id = '.$player->getGameID().' AND anon_id = '.$account_num);
//		$player->increaseCredits($overflow);
//		
//	}
    $player->update();

	// log action
	$account->log(4, 'Deposits '.$amount.' credits in anonymous account #'.$account_num, $player->getSectorID());

} else {

    $db->query('SELECT * FROM anon_bank WHERE anon_id = '.$account_num.' AND game_id = '.$player->getGameID());
    $db->nextRecord();
    if ($db->getField('amount') < $amount)
        create_error('You don\'t have that much money on your account!');
    $db->query('SELECT * FROM anon_bank_transactions WHERE game_id = '.$player->getGameID().' AND anon_id = '.$account_num.' ORDER BY transaction_id DESC LIMIT 1');
    if ($db->nextRecord())
        $trans_id = $db->getField('transaction_id') + 1;
    else
        $trans_id = 1;
    $db->query('INSERT INTO anon_bank_transactions (account_id, game_id, anon_id, transaction_id, transaction, amount, time) ' .
                            'VALUES ('.$player->getAccountID().', '.$player->getGameID().', '.$account_num.', '.$trans_id.', \'Payment\', '.$amount.', '.TIME.')');
    $db->query('UPDATE anon_bank SET amount = amount - '.$amount.' WHERE game_id = '.$player->getGameID().' AND anon_id = '.$account_num);
    $player->increaseCredits($amount);
    $db->query('SELECT * FROM anon_bank WHERE game_id = '.$player->getGameID().' AND anon_id = '.$account_num);
    $db->nextRecord();
    $total = $db->getField('amount');
    //too much money?
//	if ($player->getCredits() > 4294967295) {
//		
//		$overflow = $player->getCredits() - 4294967295;
//		$db->query('UPDATE anon_bank SET amount = amount + '.$overflow.' WHERE game_id = '.$player->getGameID().' AND anon_id = '.$account_num);
//		$player->decreaseCredits($overflow);
//		
//	}
    $player->update();

	// log action
	$account->log(4, 'Takes '.$amount.' credits from anonymous account #'.$account_num, $player->getSectorID());

}

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'bank_anon.php';
$container['account_num'] = $account_num;
$container['allowed'] = 'yes';
transfer($password);
forward($container);

?>