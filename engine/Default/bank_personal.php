<?

// ********************************
// *
// * V a l i d a t e d ?
// *
// ********************************

// is account validated?
if ($account->validated == 'FALSE') {
	create_error('You are not validated so you cannot use banks.');
	return;
}

$smarty->assign('PageTopic','bank');

include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_bank_menue();

$PHP_OUTPUT.= 'Hello ';
$PHP_OUTPUT.= $player->getPlayerName();
$PHP_OUTPUT.= '<br /><br />';

$PHP_OUTPUT.= 'Balance: <b>';
$PHP_OUTPUT.= number_format($player->getBank());
$PHP_OUTPUT.= '</b><br /><br /><h2>Make transaction</h2><br />';

$container=array();
$container['url'] = 'bank_personal_processing.php';
$container['body'] = '';
$actions = array();
$actions[] = array('Deposit','Deposit');
$actions[] = array('Withdraw','Withdraw');
$form = create_form($container,$actions);

$PHP_OUTPUT.= $form['form'];
$PHP_OUTPUT.= 'Amount:&nbsp;<input class="text" type="text" name="amount" size="10" value="0"><br /><br />';

$PHP_OUTPUT.= $form['submit']['Deposit'];
$PHP_OUTPUT.= '&nbsp;&nbsp;';
$PHP_OUTPUT.= $form['submit']['Withdraw'];

$PHP_OUTPUT.= '</form>';

?>