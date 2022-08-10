<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Page;
use Smr\Page\AccountPageProcessor;
use Smr\Page\ReusableTrait;
use Smr\Session;
use SmrAccount;

class GameLeaveProcessor extends AccountPageProcessor {

	use ReusableTrait;

	public function __construct(
		private readonly Page $forwardTo
	) {}

	public function build(SmrAccount $account): never {
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
