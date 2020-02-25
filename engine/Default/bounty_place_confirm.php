<?php declare(strict_types=1);

$template->assign('PageTopic', 'Place a Bounty');

require_once(get_file_loc('menu_hq.inc'));
if ($sector->hasHQ()) {
	create_hq_menu();
} else {
	create_ug_menu();
}

// get this guy from db
$bountyPlayer = SmrPlayer::getPlayerByPlayerID($var['player_id'], $player->getGameID());

$template->assign('Amount', number_format($var['amount']));
$template->assign('SmrCredits', number_format($var['SmrCredits']));
$template->assign('BountyPlayer', $bountyPlayer->getLinkedDisplayName());

$container = create_container('bounty_place_confirm_processing.php');
$container['account_id'] = $bountyPlayer->getAccountID();
transfer('amount');
transfer('SmrCredits');
transfer('LocationID');
$template->assign('ProcessingHREF', SmrSession::getNewHREF($container));
