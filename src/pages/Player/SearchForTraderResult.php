<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Exceptions\PlayerNotFound;
use Smr\Globals;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Account\HallOfFamePersonal;
use Smr\Pages\Account\NewsReadAdvanced;
use Smr\Pages\Player\Council\ViewCouncil;
use Smr\Player;
use Smr\Request;
use Smr\Template;

class SearchForTraderResult extends PlayerPage {

	use ReusableTrait;

	public string $file = 'trader_search_result.php';

	public function __construct(
		private ?int $playerID = null,
		private ?string $playerName = null
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		$this->playerID ??= Request::getInt('player_id');
		$player_id = $this->playerID;

		// When clicking on a player name, only the 'player_id' is supplied
		$this->playerName ??= Request::get('player_name', '');
		$player_name = $this->playerName;

		if (empty($player_name) && empty($player_id)) {
			create_error('You must specify either a player name or ID!');
		}

		if (!empty($player_id)) {
			try {
				$resultPlayer = Player::getPlayerByPlayerID($player_id, $player->getGameID());
			} catch (PlayerNotFound) {
				// No player found, we'll return an empty result
			}
		} else {
			try {
				$resultPlayer = Player::getPlayerByPlayerName($player_name, $player->getGameID());
			} catch (PlayerNotFound) {
				// No exact match, but that's okay
			}

			$db = Database::getInstance();
			$dbResult = $db->read('SELECT * FROM player
						WHERE game_id = :game_id
							AND player_name LIKE :player_name_like
							AND player_name != :player_name
						ORDER BY player_name LIMIT 5', [
				'game_id' => $db->escapeNumber($player->getGameID()),
				'player_name_like' => $db->escapeString('%' . $player_name . '%'),
				'player_name' => $db->escapeString($player_name),
			]);
			$similarPlayers = [];
			foreach ($dbResult->records() as $dbRecord) {
				$similarPlayers[] = Player::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), false, $dbRecord);
			}
		}

		/**
		 * @return array<string, Player|string>
		 */
		$playerLinks = function(Player $linkPlayer) use ($player): array {
			$result = ['Player' => $linkPlayer];

			$container = new self($linkPlayer->getPlayerID());
			$result['SearchHREF'] = $container->href();

			$container = new ViewCouncil($linkPlayer->getRaceID());
			$result['RaceHREF'] = $container->href();

			$container = new MessageSend($linkPlayer->getAccountID());
			$result['MessageHREF'] = $container->href();

			$container = new BountyView($linkPlayer->getAccountID());
			$result['BountyHREF'] = $container->href();

			$container = new HallOfFamePersonal($linkPlayer->getAccountID(), $linkPlayer->getGameID());
			$result['HofHREF'] = $container->href();

			$container = new NewsReadAdvanced(
				gameID: $linkPlayer->getGameID(),
				submit: 'Search For Player',
				accountIDs: [$linkPlayer->getAccountID()],
			);
			$result['NewsHREF'] = $container->href();

			if (in_array($player->getAccountID(), Globals::getHiddenPlayers())) {
				$container = new SectorJumpProcessor($linkPlayer->getSectorID());
				$result['JumpHREF'] = $container->href();
			}

			return $result;
		};

		if (empty($resultPlayer) && empty($similarPlayers)) {
			$container = new SearchForTrader(emptyResult: true);
			$container->go();
		}

		if (!empty($resultPlayer)) {
			$resultPlayerLinks = $playerLinks($resultPlayer);
			$template->assign('ResultPlayerLinks', $resultPlayerLinks);
		}

		if (!empty($similarPlayers)) {
			$similarPlayersLinks = [];
			foreach ($similarPlayers as $similarPlayer) {
				$similarPlayersLinks[] = $playerLinks($similarPlayer);
			}
			$template->assign('SimilarPlayersLinks', $similarPlayersLinks);
		}

		$template->assign('PageTopic', 'Search For Trader Results');
	}

}
