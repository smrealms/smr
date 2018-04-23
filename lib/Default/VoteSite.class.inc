<?php

/**
 * Handles links to external game voting sites.
 */
class VoteSite {

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
				'url_base' => 'http://topwebgames.com/in.aspx?ID=136&alwaysreward=1',
				'url_func' => function ($baseUrl, $accountId, $gameId, $linkId) {
					$query = array('account' => $accountId, 'game' => $gameId, 'link' => $linkId);
					return $baseUrl . '&' . http_build_query($query);
				},
			),
			4 => array(
				'img_default' => 'dog.png',
				'img_star' => 'dog_vote.png',
				'url_base' => 'http://www.directoryofgames.com/main.php?view=topgames&action=vote&v_tgame=2315',
				'url_func' => function ($baseUrl, $accountId, $gameId, $linkId) {
					return "$baseUrl&votedef=$accountId,$gameId,$linkId";
				},
			),
		);
	}

	public static function getAllSites() {
		static $ALL_SITES;
		if (!isset($allSites)) {
			$ALL_SITES = array(); // ensure this is set
			foreach (self::getAllSiteData() as $linkID => $siteData) {
				$ALL_SITES[$linkID] = new VoteSite($linkID, $siteData);
			}
		}
		return $ALL_SITES;
	}

	function __construct($linkID, $siteData) {
		$this->linkID = $linkID;
		$this->data = $siteData;
	}

	/**
	 * Time until the account can get free turns from voting at this site.
	 * If the time is 0, this site is eligible for free turns.
	 */
	public function getTimeUntilFreeTurns($accountID) {
		static $WAIT_TIMES;
		if (!isset($WAIT_TIMES)) {
			$WAIT_TIMES = array(); // ensure this is set
			$activeLinkIDs = array_keys(self::getAllSites());
			$db = new SmrMySqlDatabase();
			$db->query('SELECT link_id, timeout FROM vote_links WHERE account_id=' . $db->escapeNumber($accountID) . ' AND link_id IN (' . join(',', $activeLinkIDs) . ') LIMIT ' . $db->escapeNumber(count($activeLinkIDs)));
			while ($db->nextRecord()) {
				// 'timeout' is the last time the player clicked this vote site link
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
	 * Returns the image to display for this voting site.
	 */
	public function getLinkImg($accountID, $gameID) {
		if ($gameID !=0 && $this->getTimeUntilFreeTurns($accountID) <= 0) {
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
		if ($gameID != 0 && $this->getTimeUntilFreeTurns($accountID) <= 0) {
			$container = create_container('vote_link.php');
			$container['link_id'] = $this->linkID;
			$container['can_get_turns'] = true;
			$internalUrl = SmrSession::getNewHREF($container, true);
			$externalUrl = $this->data['url_func']($baseUrl, $accountID, $gameID, $this->linkID);
			return 'javascript:voteSite("' . $externalUrl . '","' . $internalUrl . '")';
		} else {
			return $baseUrl;
		}
	}

}
