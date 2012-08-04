<?php

// ********************************
// *
// * V a l i d a t e d ?
// *
// ********************************

// is account validated?
if (!$account->isValidated()) {
	create_error('You are not validated so you cannot use banks.');
}

$template->assign('PageTopic','Bank');

require_once(get_file_loc('menu.inc'));
create_bank_menu();

$PHP_OUTPUT.= 'Hello ' . $player->getPlayerName();
$PHP_OUTPUT.= '<br /><br />';

$PHP_OUTPUT.= 'Balance: <b>';
$PHP_OUTPUT.= number_format($player->getBank());
$PHP_OUTPUT.= '</b><br /><br /><h2>Make transaction</h2><br />';

$container=create_container('bank_personal_processing.php');
$actions = array(
	array('Deposit','Deposit'),
	array('Withdraw','Withdraw')
);
$form = create_form($container,$actions);

$PHP_OUTPUT.= $form['form'];
$PHP_OUTPUT.= 'Amount:&nbsp;<input type="number" step="100000" name="amount" size="10" value="0"><br /><br />';

$PHP_OUTPUT.= $form['submit']['Deposit'];
$PHP_OUTPUT.= '&nbsp;&nbsp;';
$PHP_OUTPUT.= $form['submit']['Withdraw'];

$PHP_OUTPUT.= '</form>';

?>