<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\Pages\Player\CurrentSector;
use Smr\Player;
use Smr\SectorLock;
use Smr\Session;

class GamePlayProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
	) {}

	public function build(Account $account): never {
		// register game_id
		Session::getInstance()->updateGame($this->gameID);

		$player = Player::getPlayer($account->getAccountID(), $this->gameID);

		// skip var update in do_voodoo
		SectorLock::getInstance()->acquireForPlayer($player);

		$player->updateLastCPLAction();

		// Check to see if newbie status has changed
		$player->updateNewbieStatus();

		// get rid of old plotted course
		$player->deletePlottedCourse();
		$player->update();

		// log
		$player->log(LOG_TYPE_GAME_ENTERING, 'Player entered game ' . $player->getGameID());

		$container = new CurrentSector();
		$container->go();
	}

}
