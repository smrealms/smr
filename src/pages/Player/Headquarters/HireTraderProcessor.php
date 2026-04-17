<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;

class HireTraderProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $locationID,
		private readonly int $npcAccountID,
		private readonly int $hireCost,
	) {}

	public function build(AbstractPlayer $player): never {
		// Pay the hiring fee
		if ($player->getCredits() < $this->hireCost) {
			create_error('You do not have enough credits to hire this trader!');
		}
		$player->decreaseCredits($this->hireCost);

		// Leave NPC alliance and join player's alliance (this should be locked)
		$npc = AbstractPlayer::getPlayer($this->npcAccountID, $player->getGameID());
		$npcAlliance = $npc->getAlliance();
		if ($npcAlliance->getNumMembers() === 1) {
			$npcAlliance->setLeaderID(0);
			$npcAlliance->update();
		}
		$npc->leaveAlliance();
		$npc->joinAlliance($player->getAllianceID());

		// Enable NPC
		$db = Database::getInstance();
		$db->update(
			'npc_logins',
			['active' => $db->escapeBoolean(true)],
			['login' => $npc->getAccount()->getLogin()],
		);

		(new HireTrader($this->locationID))->go();
	}

}
