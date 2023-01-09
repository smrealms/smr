<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class GameDelete extends AccountPage {

	public string $file = 'admin/game_delete.php';

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Deleting A Game');

		$container = new GameDeleteConfirm();
		$template->assign('ConfirmHREF', $container->href());

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT game_id, game_name FROM game ORDER BY game_id DESC');
		$games = [];
		foreach ($dbResult->records() as $dbRecord) {
			$name = $dbRecord->getString('game_name');
			$game_id = $dbRecord->getInt('game_id');
			$games[] = [
				'game_id' => $game_id,
				'display' => '(' . $game_id . ') ' . $name,
			];
		}
		$template->assign('Games', $games);
	}

}
