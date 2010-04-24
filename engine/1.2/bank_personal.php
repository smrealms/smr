<?php

// ********************************
// *
// * V a l i d a t e d ?
// *
// ********************************

// is account validated?
if ($account->validated == 'FALSE') {
	print_error('You are not validated so you can\'t use banks.');
	return;
}

print_topic('bank');

include(get_file_loc('menue.inc'));
print_bank_menue();

echo 'Hello ';
echo $player->player_name;
echo '<br><br>';

echo 'Balance: <b>';
echo number_format($player->bank);
echo '</b><br><br><h2>Make transaction</h2><br>';

$container=array();
$container['url'] = 'bank_personal_processing.php';
$container['body'] = '';
$actions = array();
$actions[] = array('Deposit','Deposit');
$actions[] = array('Withdraw','Withdraw');
$form = create_form($container,$actions);

echo $form['form'];
echo 'Amount:&nbsp;<input class="text" type="text" name="amount" size="10" value="0"><br><br>';

echo $form['submit']['Deposit'];
echo '&nbsp;&nbsp;';
echo $form['submit']['Withdraw'];

echo '</form>';

?>