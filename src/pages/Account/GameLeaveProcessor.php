<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\Page\Page;
use Smr\Page\ReusableTrait;
use Smr\Session;

class GameLeaveProcessor extends AccountPageProcessor {

	use ReusableTrait;

	public function __construct(
		private readonly Page $forwardTo
	) {}

	public function build(Account $account): never {
		$session = Session::getInstance();

		// Reset the game ID if necessary
		if ($session->hasGame()) {
			$account->log(LOG_TYPE_GAME_ENTERING, 'Player left game ' . $session->getGameID());
			$session->updateGame(0);
		}

		$session->clearLinks();

		$this->forwardTo->go();
	}

}
