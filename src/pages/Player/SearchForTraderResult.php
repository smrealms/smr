<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Exceptions\PlayerNotFound;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Account\NewsReadAdvanced;
use Smr\Pages\Player\Council\ViewCouncil;
use Smr\Player;
use Smr\Request;
use Smr\Template;

class SearchForTraderResult extends PlayerPage {

	use ReusableTrait;
	public function __construct(
		private ?int $playerID = null,
		private ?string $playerName = null,
	) {}

	public function build(Player $player, Template $template): void {
		$this->playerID ??= Request::getInt('player_id');
		$player_id = $this->playerID;

		// When clicking on a player name, only the 'player_id' is supplied
		$this->playerName ??= Request::get('player_name', '');
		$player_name = $this->playerName;

		if ($player_name === '' && $player_id === 0) {
			create_error('You must specify either a player name or ID!');
		}

		$similarPlayers = [];
		if ($player_id !== 0) {
			try {
				$resultPlayer = Player::getPlayerByPlayerID($player_id, $player->getGameID());
			} catch (PlayerNotFound) {
				// No player found, we'll return an empty result
				$resultPlayer = null;
			}
		} else {
			try {
				$resultPlayer = Player::getPlayerByPlayerName($player_name, $player->getGameID());
			} catch (PlayerNotFound) {
				// No exact match, but that's okay
				$resultPlayer = null;
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
			foreach ($dbResult->records() as $dbRecord) {
				$similarPlayers[] = Player::getPlayer($dbRecord->getInt('account_id'), $player->getGameID(), false, $dbRecord);
			}
		}

		/**
		 * @return array{Player: Player, SearchHREF: string, RaceHREF: string, MessageHREF: string, BountyHREF: string, HofHREF: string, NewsHREF: string, JumpHREF?: string}
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

			$result['HofHREF'] = $linkPlayer->getPersonalHofHREF();

			$container = new NewsReadAdvanced(
				gameID: $linkPlayer->getGameID(),
				submit: 'Search For Player',
				accountIDs: [$linkPlayer->getAccountID()],
			);
			$result['NewsHREF'] = $container->href();

			if ($player->isObserver()) {
				$container = new SectorJumpProcessor($linkPlayer->getSectorID());
				$result['JumpHREF'] = $container->href();
			}

			return $result;
		};

		if ($resultPlayer === null && count($similarPlayers) === 0) {
			$container = new SearchForTrader(emptyResult: true);
			$container->go();
		}

		if ($resultPlayer !== null) {
			$resultPlayerLinks = $playerLinks($resultPlayer);
		} else {
			$resultPlayerLinks = null;
		}

		if (count($similarPlayers) > 0) {
			$similarPlayersLinks = [];
			foreach ($similarPlayers as $similarPlayer) {
				$similarPlayersLinks[] = $playerLinks($similarPlayer);
			}
		} else {
			$similarPlayersLinks = null;
		}

		$template->pageTopic = 'Search For Trader Results';

		$template->pageRenderer = fn() => SearchForTraderResultRenderer::render(
			ResultPlayerLinks: $resultPlayerLinks,
			SimilarPlayersLinks: $similarPlayersLinks,
			ThisPlayer: $player,
		);
	}

}
