<?php declare(strict_types=1);

/**
 * Returns a list of all active (not claimable) bounties for given location $type.
 */
function getBounties($type) {
	$db = new SmrMySqlDatabase();
	$db->query('SELECT * FROM bounty WHERE game_id = ' . $db->escapeNumber(SmrSession::getGameID()) . ' AND type =' . $db->escapeString($type) . ' AND claimer_id = 0 ORDER BY amount DESC');
	$bounties = [];
	while ($db->nextRecord()) {
		$bounties[] = [
			'player' => SmrPlayer::getPlayer($db->getInt('account_id'), SmrSession::getGameID()),
			'credits' => $db->getInt('amount'),
			'smr_credits' => $db->getInt('smr_credits')
		];
	}
	return $bounties;
}
