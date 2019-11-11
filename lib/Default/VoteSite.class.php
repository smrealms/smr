<?php

/**
 * Handles links to external game voting sites.
 */
class VoteSite {

	private $linkID;
	private $data;

	// MPOGD no longer exists
	//1 => array('default_img' => 'mpogd.png', 'star_img' => 'mpogd_vote.png', 'base_url' => 'http://www.mpogd.com/games/game.asp?ID=1145'),
	// OMGN no longer do voting - the link actually just redirects to archive site.
	//2 => array('default_img' => 'omgn.png', 'star_img' => 'omgn_vote.png', 'base_url' => 'http://www.omgn.com/topgames/vote.php?Game_ID=30'),

	private static function getAllSiteData() {
		// This can't be a static/constant attribute due to `url_func` closures.
		// NOTE: array keys (a.k.a. link ID's) should never be changed!
		return array(
			3 => array(
				'img_default' => 'twg.png',
				'img_star' => 'twg_vote.png',
				'url_base' => 'http://topwebgames.com/in.aspx?ID=136',
				'url_func' => function($baseUrl, $accountId, $gameId, $linkId) {
					$query = array('account' => $accountId, 'game' => $gameId, 'link' => $linkId, 'alwaysreward' => 1);
					return $baseUrl . '&' . http_build_query($query);
				},
			),
			4 => array(
				'img_default' => 'dog.png',
				'img_star' => 'dog_vote.png',
				'url_base' => 'http://www.directoryofgames.com/main.php?view=topgames&action=vote&v_tgame=2315',
				'url_func' => function($baseUrl, $accountId, $gameId, $linkId) {
					return "$baseUrl&votedef=$accountId,$gameId,$linkId";
				},
			),
			5 => array(
				'img_default' => 'pbbg.png',
				'url_base' => 'https://pbbg.com/games/space-merchant-realms',
			),
		);
	}

	public static function getAllSites() {
		static $ALL_SITES;
		if (!isset($ALL_SITES)) {
			$ALL_SITES = array(); // ensure this is set
			foreach (self::getAllSiteData() as $linkID => $siteData) {
				$ALL_SITES[$linkID] = new VoteSite($linkID, $siteData);
			}
		}
		return $ALL_SITES;
	}

	/**
	 * Returns the earliest time (in seconds) until free turns
	 * are available across all voting sites.
	 */
	public static function getMinTimeUntilFreeTurns($accountID) {
		$minWait = [];
		foreach (self::getAllSites() as $site) {
			if ($site->givesFreeTurns()) {
				$minWait[] = $site->getTimeUntilFreeTurns($accountID);
			}
		}
		return min($minWait);
	}

	private function __construct($linkID, $siteData) {
		$this->linkID = $linkID;
		$this->data = $siteData;
	}

	/**
	 * Does this VoteSite have a voting callback that can be used
	 * to award free turns?
	 */
	public function givesFreeTurns() {
		return isset($this->data['img_star']);
	}

	/**
	 * Time until the account can get free turns from voting at this site.
	 * If the time is 0, this site is eligible for free turns.
	 */
	public function getTimeUntilFreeTurns($accountID) {
		if (!$this->givesFreeTurns()) {
			throw new Exception('This vote site cannot award free turns!');
		}

		static $WAIT_TIMES;
		if (!isset($WAIT_TIMES)) {
			$WAIT_TIMES = array(); // ensure this is set
			$activeLinkIDs = array_keys(self::getAllSites());
			$db = new SmrMySqlDatabase();
			$db->query('SELECT link_id, timeout FROM vote_links WHERE account_id=' . $db->escapeNumber($accountID) . ' AND link_id IN (' . join(',', $activeLinkIDs) . ') LIMIT ' . $db->escapeNumber(count($activeLinkIDs)));
			while ($db->nextRecord()) {
				// 'timeout' is the last time the player claimed free turns (or 0, if unclaimed)
				$WAIT_TIMES[$db->getInt('link_id')] = ($db->getField('timeout') + TIME_BETWEEN_VOTING) - TIME;
			}
			// If not in the vote_link database, this site is eligible now.
			foreach ($activeLinkIDs as $linkID) {
				if (!isset($WAIT_TIMES[$linkID])) {
					$WAIT_TIMES[$linkID] = 0;
				}
			}
		}
		return $WAIT_TIMES[$this->linkID];
	}

	/**
	 * Returns true if account can currently receive free turns at this site.
	 */
	private function freeTurnsReady($accountID, $gameID) {
		return $this->givesFreeTurns() && $gameID != 0 && $this->getTimeUntilFreeTurns($accountID) <= 0;
	}

	/**
	 * Returns the image to display for this voting site.
	 */
	public function getLinkImg($accountID, $gameID) {
		if ($this->freeTurnsReady($accountID, $gameID)) {
			return $this->data['img_star'];
		} else {
			return $this->data['img_default'];
		}
	}

	/**
	 * Returns the URL that should be used for this voting site.
	 */
	public function getLinkUrl($accountID, $gameID) {
		$baseUrl = $this->data['url_base'];
		if ($this->freeTurnsReady($accountID, $gameID)) {
			return $this->data['url_func']($baseUrl, $accountID, $gameID, $this->linkID);
		} else {
			return $baseUrl;
		}
	}

	/**
	 * Returns the SN to redirect the current page to if free turns are
	 * available; otherwise, returns false.
	 */
	public function getSN($accountID, $gameID) {
		if ($this->freeTurnsReady($accountID, $gameID)) {
			// This page will prepare the account for the voting callback.
			$container = create_container('vote_link.php');
			$container['link_id'] = $this->linkID;
			$container['can_get_turns'] = true;
			return SmrSession::getNewHREF($container, true);
		} else {
			return false;
		}
	}

}
