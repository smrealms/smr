<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\CombatLogType;
use Smr\Database;
use Smr\Globals;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class CombatLogList extends PlayerPage {

	use ReusableTrait;
	public function __construct(
		private readonly CombatLogType $action = CombatLogType::Personal,
		private readonly int $page = 0,
		private readonly ?string $message = null,
	) {}

	public function build(Player $player, Template $template): void {
		$db = Database::getInstance();

		$template->pageTopic = 'Combat Logs';
		Menu::combatLog();

		$action = $this->action;

		$query = match ($action) {
			CombatLogType::Personal, CombatLogType::Alliance => 'type=\'PLAYER\'',
			CombatLogType::Port => 'type=\'PORT\'',
			CombatLogType::Planet => 'type=\'PLANET\'',
			CombatLogType::Saved => 'EXISTS(
							SELECT 1
							FROM player_saved_combat_logs
							WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . '
								AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
								AND log_id = c.log_id
						)',
			CombatLogType::Force => 'type=\'FORCE\'',
		};

		$query .= ' AND game_id=' . $db->escapeNumber($player->getGameID());
		if ($action !== CombatLogType::Personal && $player->hasAlliance()) {
			$query .= ' AND (attacker_alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' OR defender_alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ') ';
		} else {
			$query .= ' AND (attacker_id=' . $db->escapeNumber($player->getAccountID()) . ' OR defender_id=' . $db->escapeNumber($player->getAccountID()) . ') ';
		}

		$page = $this->page;
		$dbResult = $db->read('SELECT count(*) as count FROM combat_logs c WHERE ' . $query);
		$totalLogs = $dbResult->record()->getInt('count'); // count always returns a record

		$dbResult = $db->read('SELECT attacker_id,defender_id,timestamp,sector_id,log_id FROM combat_logs c WHERE ' . $query . ' ORDER BY log_id DESC, sector_id LIMIT ' . ($page * COMBAT_LOGS_PER_PAGE) . ', ' . COMBAT_LOGS_PER_PAGE);

		$getParticipantName = function($accountID, $sectorID) use ($player): string {
			if ($accountID === ACCOUNT_ID_PORT) {
				return '<a href="' . Globals::getPlotCourseHREF($player->getSectorID(), $sectorID) . '">Port <span class="sectorColour">#' . $sectorID . '</span></a>';
			}
			if ($accountID === ACCOUNT_ID_PLANET) {
				return '<span class="yellow">Planetary Defenses</span>';
			}
			return Player::getPlayer($accountID, $player->getGameID())->getLinkedDisplayName(false);
		};

		// Construct the list of logs of this type
		$logs = [];
		$previousPage = null;
		$nextPage = null;
		if ($dbResult->hasRecord()) {
			// Set the links for the "view next/previous log list" buttons
			if ($page > 0) {
				$previousPage = new self($action, $page - 1)->href();
			}
			if (($page + 1) * COMBAT_LOGS_PER_PAGE < $totalLogs) {
				$nextPage = new self($action, $page + 1)->href();
			}

			foreach ($dbResult->records() as $dbRecord) {
				$sectorID = $dbRecord->getInt('sector_id');
				$logs[$dbRecord->getInt('log_id')] = [
					'Attacker' => $getParticipantName($dbRecord->getInt('attacker_id'), $sectorID),
					'Defender' => $getParticipantName($dbRecord->getInt('defender_id'), $sectorID),
					'Time' => $dbRecord->getInt('timestamp'),
					'Sector' => $sectorID,
				];
			}
		}

		$template->pageRenderer = fn() => CombatLogListRenderer::render(
			template: $template,
			Message: $this->message,
			TotalLogs: $totalLogs,
			LogType: strtolower($action->name),
			LogFormPage: new CombatLogListProcessor($action),
			PreviousPage: $previousPage,
			NextPage: $nextPage,
			CanDelete: $action === CombatLogType::Saved,
			CanSave: $action !== CombatLogType::Saved,
			Logs: $logs,
			ThisAccount: $player->getAccount(),
		);
	}

}
