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

	public const array DATA = [
		self::TWG->value => [
			'img_default' => 'twg.png',
			'img_star' => 'twg_vote.png',
			'url_base' => 'https://topwebgames.com/in.aspx?ID=136',
			'url_func' => static function(string $baseUrl, int $accountId, int $gameId): string {
				$query = ['account' => $accountId, 'game' => $gameId, 'link' => self::TWG->value, 'alwaysreward' => 1];
				return $baseUrl . '&' . http_build_query($query);
			},
		],
		self::PBBG->value => [
			'img_default' => 'pbbg.png',
			'url_base' => 'https://pbbg.com/games/space-merchant-realms',
		],
	];

	/**
	 * Does this voting site have the configuration required to award free turns?
	 */
	public function givesFreeTurns(): bool {
		$data = self::DATA[$this->value];
		return isset($data['img_star'], $data['url_func']);
	}

	/**
	 * Returns the image to display for this voting site.
	 */
	public function getImg(bool $freeTurnsReady): string {
		$data = self::DATA[$this->value];
		if ($freeTurnsReady && isset($data['img_star'])) {
			return $data['img_star'];
		}
		return $data['img_default'];
	}

	/**
	 * Returns the URL that should be used for this voting site.
	 */
	public function getUrl(bool $freeTurnsReady, int $accountId, int $gameId): string {
		$data = self::DATA[$this->value];
		if (!$freeTurnsReady || !isset($data['url_func'])) {
			return $data['url_base'];
		}
		$urlFunc = $data['url_func'];
		return $urlFunc(
			baseUrl: $data['url_base'],
			accountId: $accountId,
			gameId: $gameId,
		);
	}

}
