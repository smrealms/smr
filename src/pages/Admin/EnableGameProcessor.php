<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Game;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class EnableGameProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$game = Game::getGame(Request::getInt('game_id'));
		$game->setEnabled(true);
		$game->save(); // because next page queries database

		// Create the Newbie Help Alliance
		require_once(LIB . 'Default/nha.inc.php');
		createNHA($game->getGameID());

		$msg = '<span class="green">SUCCESS: </span>Enabled game ' . $game->getDisplayName();

		(new EnableGame($msg))->go();
	}

}
