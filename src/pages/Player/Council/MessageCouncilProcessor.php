<?php declare(strict_types=1);

namespace Smr\Pages\Player\Council;

use Smr\AbstractPlayer;
use Smr\Council;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;
use Smr\Request;

class MessageCouncilProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $raceID,
	) {}

	public function build(AbstractPlayer $player): never {
		$message = htmlentities(Request::get('message'), ENT_COMPAT, 'utf-8');

		if ($message === '') {
			create_error('You have to enter text to send a message!');
		}

		// send to all council members
		$councilMembers = Council::getRaceCouncil($player->getGameID(), $this->raceID);
		foreach ($councilMembers as $accountID) {
			$player->sendMessage($accountID, MSG_POLITICAL, $message, true, $player->getAccountID() !== $accountID);
		}

		$msg = '<span class="green">SUCCESS: </span>Your message has been sent.';
		$container = new CurrentSector(message: $msg);
		$container->go();
	}

}
