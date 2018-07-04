<?php

/**
 * Returns a list of all bounties for given location $type.
 */
function getBounties($type) {
	$db = new SmrMySqlDatabase();
	$db->query('SELECT * FROM bounty WHERE game_id = '.$db->escapeNumber(SmrSession::$game_id).' AND type ='.$db->escapeString($type).' AND claimer_id = 0 ORDER BY amount DESC');
	$bounties = [];
	while ($db->nextRecord()) {
		$bounties[] = [
			'player' => SmrPlayer::getPlayer($db->getInt('account_id'), SmrSession::$game_id),
			'credits' => $db->getInt('amount'),
			'smr_credits' => $db->getInt('smr_credits')
		];
	}
	return $bounties;
}
