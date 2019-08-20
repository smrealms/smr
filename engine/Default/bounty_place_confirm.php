<?php declare(strict_types=1);

// get request variables
$amount = SmrSession::getRequestVar('amount');
$smrCredits = SmrSession::getRequestVar('smrcredits');
$playerID = SmrSession::getRequestVar('player_id');

if ($playerID == '0') {
	create_error('Uhhh...who is [Please Select]?');
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

$template->assign('PageTopic', 'Placing a bounty');

require_once(get_file_loc('menu_hq.inc'));
if ($sector->hasHQ()) {
	create_hq_menu();
} else {
	create_ug_menu();
}

// get this guy from db
$bounty_guy = SmrPlayer::getPlayerByPlayerID($playerID, $player->getGameID());

$template->assign('Amount', number_format($amount));
$template->assign('SmrCredits', number_format($smrCredits));
$template->assign('BountyPlayer', $bounty_guy->getLinkedDisplayName());

$container = create_container('bounty_place_processing.php');
$container['account_id'] = $bounty_guy->getAccountID();
$container['amount'] = $amount;
$container['SmrCredits'] = $smrCredits;
transfer('LocationID');
$template->assign('ProcessingHREF', SmrSession::getNewHREF($container));
