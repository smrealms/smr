<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Game;
use Smr\Page\AccountPage;
use Smr\Request;
use Smr\Template;

class GameDeleteConfirm extends AccountPage {

	public function __construct(
		private ?int $deleteGameID = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Delete Game - Confirmation';

		$this->deleteGameID ??= Request::getInt('delete_game_id');
		$template->pageRenderer = fn() => GameDeleteConfirmRenderer::render(
			CancelHREF: new AdminTools()->href(),
			ConfirmHREF: new GameDeleteProcessor($this->deleteGameID)->href(),
			Game: Game::getGame($this->deleteGameID),
		);
	}

}
