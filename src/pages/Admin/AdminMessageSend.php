<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Request;
use Smr\Template;

class AdminMessageSend extends AccountPage {

	public const ALL_GAMES_ID = 20000;

	public function __construct(
		private ?int $sendGameID = null,
		private readonly ?string $preview = null,
		private readonly float $expireHours = 0.5,
		private readonly int $sendAccountID = 0,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Send Admin Message';

		$this->sendGameID ??= Request::getInt('SendGameID');
		$gameID = $this->sendGameID;

		if ($gameID !== self::ALL_GAMES_ID) {
			$game = Game::getGame($gameID);
			$gamePlayers = [['AccountID' => 0, 'Name' => 'All Players (' . $game->getName() . ')']];
			$db = Database::getInstance();
			$dbResult = $db->select(
				'player',
				['game_id' => $gameID],
				['account_id', 'player_id', 'player_name'],
				orderBy: ['player_name'],
			);
			foreach ($dbResult->records() as $dbRecord) {
				$gamePlayers[] = [
					'AccountID' => $dbRecord->getInt('account_id'),
					'Name' => htmlentities($dbRecord->getString('player_name')) . ' (' . $dbRecord->getInt('player_id') . ')',
				];
			}
		} else {
			$gamePlayers = null;
		}

		$template->pageRenderer = fn() => AdminMessageSendRenderer::render(
			AdminMessageSendForm: new AdminMessageSendProcessor($gameID),
			MessageGameID: $gameID,
			ExpireTime: $this->expireHours,
			GamePlayers: $gamePlayers,
			SelectedAccountID: $this->sendAccountID,
			Preview: $this->preview,
			BackHREF: new AdminMessageSendSelect()->href(),
		);
	}

}
