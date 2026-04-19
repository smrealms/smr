<?php declare(strict_types=1);

namespace Smr\Pages\Player\Headquarters;

use Smr\Alliance;
use Smr\Database;
use Smr\Exceptions\AllianceNotFound;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class HireTrader extends PlayerPage {

	public string $file = 'hire_trader.php';

	public const int BASE_HIRE_COST = 150_000;
	public const int MAX_NPCS_PER_ALLIANCE = 3;

	public function __construct(
		private readonly int $locationID,
	) {}

	public function build(Player $player, Template $template): void {
		$template->assign('PageTopic', 'Hire Trader');

		Menu::headquarters($this->locationID);

		$npcs = [];
		try {
			$alliance = Alliance::getAllianceByName(NPC_FOR_HIRE_ALLIANCE_NAME, $player->getGameID());
			foreach ($alliance->getMembers(includeNpc: true) as $npc) {
				$hireCost = self::BASE_HIRE_COST + 10 * $npc->getExperience();
				$container = new HireTraderProcessor(
					locationID: $this->locationID,
					npcAccountID: $npc->getAccountID(),
					hireCost: $hireCost,
				);
				$npcs[] = [
					'player' => $npc,
					'hireCost' => $hireCost,
					'hireHref' => $container->href(),
				];
			}
		} catch (AllianceNotFound) {
			// No NPCs because alliance has not been created yet
		}
		$template->assign('Npcs', $npcs);

		$disableReason = null;
		if (!$player->hasAlliance()) {
			$disableReason = 'You must be in an alliance to hire traders.';
		} else {
			$alliance = $player->getAlliance();
			$db = Database::getInstance();
			$dbResult = $db->select('alliance_has_roles', [
				...$alliance->SQLID,
				'role_id' => $player->getAllianceRole(),
			]);
			if (!$dbResult->record()->getBoolean('manage_npcs')) {
				$disableReason = 'You are not authorized to hire traders on behalf of your alliance.';
			} elseif (count($alliance->getNpcs()) >= self::MAX_NPCS_PER_ALLIANCE) {
				$disableReason = 'You have reached the maximum number of hires for your alliance.';
			} elseif (!$alliance->hasRoomForPlayer()) {
				$disableReason = 'You do not have enough room in your alliance to hire traders.';
			} elseif (count($npcs) === 0) {
				$disableReason = 'There are no traders available for hire at this time.';
			}
		}
		$template->assign('DisableReason', $disableReason);
	}

}
