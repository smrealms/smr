<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Request;
use Smr\Template;

class GameDeleteConfirm extends AccountPage {

	public string $file = 'admin/game_delete_confirm.php';

	public function __construct(
		private ?int $deleteGameID = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Delete Game - Confirmation');

		$this->deleteGameID ??= Request::getInt('delete_game_id');
		$template->assign('Game', Game::getGame($this->deleteGameID));

		$container = new GameDeleteProcessor($this->deleteGameID);
		$template->assign('ProcessingHREF', $container->href());
	}

}
