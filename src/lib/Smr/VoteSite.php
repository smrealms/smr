<?php declare(strict_types=1);

namespace Smr;

/**
 * Enumerates external game voting sites and their properties.
 */
enum VoteSite: int {

	// NOTE: site IDs should never be changed!
	case TWG = 3;
	case PBBG = 5;

	// MPOGD no longer exists
	//    1 => http://www.mpogd.com/games/game.asp?ID=1145
	// OMGN no longer do voting - the link actually just redirects to archive site.
	//    2 => http://www.omgn.com/topgames/vote.php?Game_ID=30
	// DOG domain went up for sale
	//    4 => https://www.directoryofgames.com/main.php?view=topgames&action=vote&v_tgame=2315

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
			self::PBBG => [
				'img_default' => 'pbbg.png',
				'url_base' => 'https://pbbg.com/games/space-merchant-realms',
			],
		};
	}

}
