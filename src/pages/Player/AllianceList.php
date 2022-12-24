<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;
use SmrAlliance;
use SmrGame;

class AllianceList extends PlayerPage {

	use ReusableTrait;

	public string $file = 'alliance_list.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$template->assign('PageTopic', 'List Of Alliances');

		$allowCreate = !$player->hasAlliance() && (!$player->getGame()->isGameType(SmrGame::GAME_TYPE_DRAFT) || $player->isDraftLeader());
		if ($allowCreate) {
			$container = new AllianceCreate();
			$template->assign('CreateAllianceHREF', $container->href());
		}

		// get list of alliances
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT
		count(account_id) as alliance_member_count,
		sum(experience) as alliance_xp,
		floor(avg(experience)) as alliance_avg,
		alliance.*
		FROM player
		JOIN alliance USING (game_id, alliance_id)
		WHERE leader_id > 0
		AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
		GROUP BY alliance_id
		ORDER BY alliance_name ASC');

		$alliances = [];
		foreach ($dbResult->records() as $dbRecord) {
			$allianceID = $dbRecord->getInt('alliance_id');
			$alliance = SmrAlliance::getAlliance($allianceID, $player->getGameID(), false, $dbRecord);

			$alliances[$allianceID] = [
				'Name' => $alliance->getAllianceDisplayName(true),
				'TotalExperience' => $dbRecord->getInt('alliance_xp'),
				'AverageExperience' => $dbRecord->getInt('alliance_avg'),
				'Members' => $dbRecord->getInt('alliance_member_count'),
				'OpenRecruitment' => $alliance->getRecruitType() === SmrAlliance::RECRUIT_OPEN,
			];
		}
		$template->assign('Alliances', $alliances);
	}

}