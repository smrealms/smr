<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Request;
use Smr\Template;

class AdminMessageSend extends AccountPage {

	public string $file = 'admin/admin_message_send.php';

	public const ALL_GAMES_ID = 20000;

	public function __construct(
		private ?int $sendGameID = null,
		private readonly ?string $preview = null,
		private readonly float $expireHours = 0.5,
		private readonly int $sendAccountID = 0,
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Send Admin Message');

		$this->sendGameID ??= Request::getInt('SendGameID');
		$gameID = $this->sendGameID;
		$container = new AdminMessageSendProcessor($gameID);
		$template->assign('AdminMessageSendFormHref', $container->href());
		$template->assign('MessageGameID', $gameID);
		$template->assign('ExpireTime', $this->expireHours);

		if ($gameID !== self::ALL_GAMES_ID) {
			$game = Game::getGame($gameID);
			$gamePlayers = [['AccountID' => 0, 'Name' => 'All Players (' . $game->getName() . ')']];
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT account_id,player_id,player_name FROM player WHERE game_id = :game_id ORDER BY player_name', [
				'game_id' => $db->escapeNumber($gameID),
			]);
			foreach ($dbResult->records() as $dbRecord) {
				$gamePlayers[] = [
					'AccountID' => $dbRecord->getInt('account_id'),
					'Name' => $dbRecord->getString('player_name') . ' (' . $dbRecord->getInt('player_id') . ')',
				];
			}
			$template->assign('GamePlayers', $gamePlayers);
			$template->assign('SelectedAccountID', $this->sendAccountID);
		}
		$template->assign('Preview', $this->preview);

		$container = new AdminMessageSendSelect();
		$template->assign('BackHREF', $container->href());
	}

}
