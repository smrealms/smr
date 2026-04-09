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
		// Only allow deleting games that haven't been enabled yet
		$dbResult = $db->select(
			'game',
			['enabled' => $db->escapeBoolean(false)],
			['game_id', 'game_name'],
			orderBy: ['game_id'],
			order: ['DESC'],
		);
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
