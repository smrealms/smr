<?php
$sector =& $player->getSector();

// get request variables
if(isset($_REQUEST['amount'])) {
	SmrSession::updateVar('BountyAmount',empty($_REQUEST['amount'])?0:$_REQUEST['amount']);
}
if(isset($_REQUEST['smrcredits'])) {
	SmrSession::updateVar('BountySmrCredits',empty($_REQUEST['smrcredits'])?0:$_REQUEST['smrcredits']);
}
if(isset($_REQUEST['player_id'])) {
	SmrSession::updateVar('BountyPlayerID',$_REQUEST['player_id']);
}

$amount = $var['BountyAmount'];
$smrCredits = $var['BountySmrCredits'];
$playerID = $var['BountyPlayerID'];

if ($playerID == '0') {
	create_error('Uhhh...who is [Please Select]?');
}

if (!is_numeric($amount)||!is_numeric($smrCredits)) {
	create_error('Numbers only please!');
}

$amount = round($amount);
if ($player->getCredits() < $amount) {
	create_error('You dont have that much money.');
}

$smrCredits = round($smrCredits);
if ($account->getSmrCredits() < $smrCredits) {
	create_error('You dont have that many SMR credits.');
}

if ($amount <= 0 && $smrCredits <= 0) {
	create_error('You must enter an amount greater than 0!');
}

if ((empty($amount) && empty($smrCredits)) || empty($playerID)) {
	create_error('Don\'t you want to place bounty?');
}

$template->assign('PageTopic','Placing a bounty');

require_once(get_file_loc('menu.inc'));
if ($sector->hasHQ()) {
	create_hq_menu();
}
else {
	create_ug_menu();
}

// get this guy from db
$bounty_guy =& SmrPlayer::getPlayerByPlayerID($playerID, $player->getGameID());

$PHP_OUTPUT.=('Are you sure you want to place a <span class="creds">' . number_format($amount) .
	'</span> credits and <span class="yellow">' . number_format($smrCredits) .
	'</span> SMR credits bounty on '.$bounty_guy->getLinkedDisplayName().'?');

$container = create_container('bounty_place_processing.php');
$container['account_id'] = $bounty_guy->getAccountID();
$container['amount'] = $amount;
$container['SmrCredits'] = $smrCredits;
transfer('LocationID');

$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=create_submit('Yes');
$PHP_OUTPUT.=('&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('No');
$PHP_OUTPUT.=('</form>');

?>