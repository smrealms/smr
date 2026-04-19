<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;

class AllianceManageNpcsDismissProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $npcAccountID,
	) {}

	public function build(AbstractPlayer $player): never {
		$npc = Player::getPlayer($this->npcAccountID, $player->getGameID());
		if (!$npc->sameAlliance($player)) {
			create_error('You cannot dismiss an NPC that is not in your alliance!');
		}

		// Dismissing an NPC while working could lead to abuse, so forbid it.
		$db = Database::getInstance();
		$dbResult = $db->select(
			'npc_logins',
			['login' => $npc->getAccount()->getLogin()],
		);
		if ($dbResult->record()->getBoolean('working')) {
			create_error('You cannot dismiss an NPC while it is on the job! Wait for it to finish working.');
		}

		self::dismissNpc($npc, $player);

		(new AllianceManageNpcs())->go();
	}

	public static function dismissNpc(AbstractPlayer $npc, AbstractPlayer $kickedBy): void {
		// Return to NPC-For-Hire alliance and deactive.
		$npc->leaveAlliance($kickedBy);
		$npcAlliance = Alliance::getAllianceByName(NPC_FOR_HIRE_ALLIANCE_NAME, $npc->getGameID());
		$npc->joinAlliance($npcAlliance->getAllianceID());
		$npc->update();
		if (!$npcAlliance->hasLeader()) {
			$npcAlliance->setLeaderID($npc->getAccountID());
			$npcAlliance->update();
		}
		$db = Database::getInstance();
		$db->update(
			'npc_logins',
			['active' => $db->escapeBoolean(false)],
			['login' => $npc->getAccount()->getLogin()],
		);
	}

}
