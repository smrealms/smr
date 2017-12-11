<?php

$template->assign('PageTopic','Bounties');

require_once(get_file_loc('menu.inc'));
create_trader_menu();

function getBountyList($player, $type) {
	$results = array();
	$db = new SmrMySqlDatabase();
	$db->query('SELECT * FROM bounty WHERE claimer_id=' . $db->escapeNumber($player->getAccountID()) . ' AND game_id=' . $db->escapeNumber($player->getGameID()) . ' AND type=' . $db->escapeString($type));
	while ($db->nextRecord()) {
		$bountyPlayer =& SmrPlayer::getPlayer($db->getInt('account_id'),$player->getGameID());
		$results[] = array(
			'name' => $bountyPlayer->getLinkedDisplayName(),
			'credits' => number_format($db->getInt('amount')),
			'smr_credits' => number_format($db->getInt('smr_credits')),
		);
	}
	return $results;
}

$template->assign('AllClaims', array(getBountyList($player, 'HQ'), getBountyList($player, 'UG')));

?>
