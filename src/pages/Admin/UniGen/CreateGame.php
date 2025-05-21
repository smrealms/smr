<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Pages\Admin\GameDeleteConfirm;
use Smr\Template;

class CreateGame extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/unigen/game_create.php';

	public function build(Account $account, Template $template): void {
		$db = Database::getInstance();

		//get information
		$container = new CreateGameProcessor();
		$template->assign('CreateGalaxiesHREF', $container->href());

		$container = new EditGalaxy(canEdit: true);
		$template->assign('EditGameHREF', $container->href());
		$container = new EditGalaxy(canEdit: false);
		$template->assign('ViewGameHREF', $container->href());

		$canEditEnabledGames = $account->hasPermission(PERMISSION_EDIT_ENABLED_GAMES);
		$template->assign('CanEditEnabledGames', $canEditEnabledGames);

		$defaultGame = [
			'name' => '',
			'description' => '',
			'speed' => 1.5,
			'maxTurns' => DEFAULT_MAX_TURNS,
			'startTurnHours' => DEFAULT_START_TURN_HOURS,
			'maxPlayers' => 5000,
			'joinDate' => date('Y-m-d', Epoch::time()),
			'startDate' => '',
			'endDate' => date('Y-m-d', Epoch::time() + (2 * 31 * 86400)), // 3 months
			'smrCredits' => 0,
			'gameType' => 'Default',
			'allianceMax' => 25,
			'allianceMaxVets' => 15,
			'startCredits' => 100000,
			'ignoreStats' => false,
			'relations' => MIN_GLOBAL_RELATIONS,
			'destroyPorts' => false,
		];
		$template->assign('Game', $defaultGame);
		$template->assign('SubmitValue', 'Create Game');

		// Get information for "In Development" game table
		$devGames = [];
		$dbResult = $db->read('SELECT * FROM game LEFT JOIN game_create_status USING (game_id) WHERE enabled = :enabled ORDER BY game_id DESC', [
			'enabled' => $db->escapeBoolean(false),
		]);
		foreach ($dbResult->records() as $dbRecord) {
			$game = Game::getGame($dbRecord->getInt('game_id'), false, $dbRecord);
			$allEdit = $dbRecord->getBoolean('all_edit');
			$creatorId = $dbRecord->getInt('account_id');
			if ($creatorId === $account->getAccountID() || $canEditEnabledGames || $allEdit) {
				$editHREF = new EditGalaxy(canEdit: true, gameID: $game->getGameID())->href();
			} else {
				$editHREF = null;
			}
			if ($creatorId === $account->getAccountID() || $canEditEnabledGames) {
				$deleteHREF = new GameDeleteConfirm($game->getGameID())->href();
			} else {
				$deleteHREF = null;
			}
			if ($creatorId === 0) {
				// fallback for legacy games from before we tracked creators
				$creator = '';
			} else {
				$creator = Account::getAccount($creatorId)->getLogin();
			}
			$devGames[] = [
				'ID' => $game->getGameID(),
				'Name' => $game->getName(),
				'Creator' => $creator,
				'CreateDate' => $dbRecord->getString('create_date'),
				'ReadyDate' => $dbRecord->getNullableString('ready_date') ?? '',
				'ViewHREF' => new EditGalaxy(canEdit: false, gameID: $game->getGameID())->href(),
				'EditHREF' => $editHREF,
				'DeleteHREF' => $deleteHREF,
			];
		}
		$template->assign('DevGames', $devGames);
	}

}
