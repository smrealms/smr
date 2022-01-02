<?php declare(strict_types=1);

	/**
	 * Returns a list of all active (not claimable) bounties for given location $type.
	 */
	function getBounties(string $type) : array {
		$db = Smr\Database::getInstance();
		$session = Smr\Session::getInstance();
		$dbResult = $db->read('SELECT * FROM bounty WHERE game_id = ' . $db->escapeNumber($session->getGameID()) . ' AND type =' . $db->escapeString($type) . ' AND claimer_id = 0 ORDER BY amount DESC');
		$bounties = [];
		foreach ($dbResult->records() as $dbRecord) {
			$bounties[] = [
				'player' => SmrPlayer::getPlayer($dbRecord->getInt('account_id'), $session->getGameID()),
				'credits' => $dbRecord->getInt('amount'),
				'smr_credits' => $dbRecord->getInt('smr_credits')
			];
		}
		return $bounties;
	}
