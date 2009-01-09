<?
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);

// get request variables
$amount = $_REQUEST['amount'];
$account_id = $_REQUEST['account_id'];
if ($account_id == 0) {
	
	create_error('Uhhh...who is [Please Select]?');
	return;
	
}
if (!is_numeric($amount)) {

	create_error('Numbers only please');
	return;

}
$amount = round($amount);
if ($player->getCredits() < $amount) {

	create_error('You dont have that much money.');
	return;

}

if ($amount <= 0) {

	create_error('You must enter an amount greater than 0');
	return;

}

if (empty($amount) || empty($account_id)) {

	create_error('Dont you want to place bounty?');
	return;

}

$smarty->assign('PageTopic','Placing a bounty');

include(ENGINE . 'global/menue.inc');
if ($sector->has_hq()) $PHP_OUTPUT.=create_hq_menue();
else $PHP_OUTPUT.=create_ug_menue();

// get this guy from db
$bounty_guy =& SmrPlayer::getPlayer($account_id, $player->getGameID());

$PHP_OUTPUT.=('Are you sure you want to place a <span style="color:yellow;">' . number_format($amount) .
	  '</span> bounty on <span style="color:yellow;">'.$bounty_guy->getPlayerName().'</span>?');

$container = array();
$container['url'] = 'bounty_place_processing.php';
$container['account_id'] = $bounty_guy->getAccountID();
$container['amount'] = $amount;

$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=create_submit('Yes');
$PHP_OUTPUT.=('&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('No');
$PHP_OUTPUT.=('</form>');

?>