<?php declare(strict_types=1);

namespace Smr;

/**
 * Enumerates external game voting sites and their properties.
 */
enum VoteSite: int {

	// NOTE: site IDs should never be changed!
	case TWG = 3;
	case DOG = 4;
	case PBBG = 5;

	// MPOGD no longer exists
	//1 => array('default_img' => 'mpogd.png', 'star_img' => 'mpogd_vote.png', 'base_url' => 'http://www.mpogd.com/games/game.asp?ID=1145'),
	// OMGN no longer do voting - the link actually just redirects to archive site.
	//2 => array('default_img' => 'omgn.png', 'star_img' => 'omgn_vote.png', 'base_url' => 'http://www.omgn.com/topgames/vote.php?Game_ID=30'),

	/**
	 * @return array<string, mixed>
	 */
	public function getData(): array {
		// This can't be a static/constant attribute due to `url_func` closures.
		return match ($this) {
			self::TWG => [
				'img_default' => 'twg.png',
				'img_star' => 'twg_vote.png',
				'url_base' => 'https://topwebgames.com/in.aspx?ID=136',
				'url_func' => function($baseUrl, $accountId, $gameId) {
					$query = ['account' => $accountId, 'game' => $gameId, 'link' => $this->value, 'alwaysreward' => 1];
					return $baseUrl . '&' . http_build_query($query);
				},
			],
			self::DOG => [
				'img_default' => 'dog.png',
				'img_star' => 'dog_vote.png',
				'url_base' => 'https://www.directoryofgames.com/main.php?view=topgames&action=vote&v_tgame=2315',
				'url_func' => function($baseUrl, $accountId, $gameId) {
					$params = implode(',', [$accountId, $gameId, $this->value]);
					return $baseUrl . '&votedef=' . $params;
				},
			],
			self::PBBG => [
				'img_default' => 'pbbg.png',
				'url_base' => 'https://pbbg.com/games/space-merchant-realms',
			],
		};
	}

}
