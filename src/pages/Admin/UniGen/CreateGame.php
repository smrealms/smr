<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class CreateGame extends AccountPage {

	use ReusableTrait;

	public string $file = 'admin/unigen/game_create.php';

	public function build(Account $account, Template $template): void {
		$db = Database::getInstance();

		//get information
		$container = new CreateGameProcessor();
		$template->assign('CreateGalaxiesHREF', $container->href());

		$container = new EditGalaxy();
		$template->assign('EditGameHREF', $container->href());

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

		$games = [];
		if ($canEditEnabledGames) {
			$dbResult = $db->read('SELECT game_id FROM game ORDER BY game_id DESC');
		} else {
			$dbResult = $db->read('SELECT game_id FROM game WHERE enabled = :enabled ORDER BY game_id DESC', [
				'enabled' => $db->escapeBoolean(false),
			]);
		}
		foreach ($dbResult->records() as $dbRecord) {
			$games[] = Game::getGame($dbRecord->getInt('game_id'));
		}
		$template->assign('EditGames', $games);
	}

}
