<?php declare(strict_types=1);

namespace Smr\Pages\Account\HistoryGames;

use Exception;
use Smr\Account;
use Smr\Database;
use Smr\Session;
use Smr\Template;

class ExtendedStats extends HistoryPage {

	public string $file = 'history_games_detail.php';

	public function __construct(
		protected readonly string $historyDatabase,
		protected readonly int $historyGameID,
		protected readonly string $historyGameName,
	) {}

	protected function buildHistory(Account $account, Template $template): void {
		$game_id = $this->historyGameID;
		$template->assign('PageTopic', 'Extended Stats : ' . $this->historyGameName);
		$this->addMenu($template);

		$oldAccountID = $account->getOldAccountID($this->historyDatabase);

		$container = new self($this->historyDatabase, $this->historyGameID, $this->historyGameName);
		$template->assign('SelfHREF', $container->href());

		// Default page has no category (action) selected yet
		$session = Session::getInstance();
		$action = $session->getRequestVar('action', '');
		if (!empty($action)) {
			$rankings = [];
			$db = Database::getInstance();
			if (in_array($action, ['Top Mined Sectors', 'Most Dangerous Sectors'], true)) {
				[$sql, $header] = match ($action) {
					'Top Mined Sectors' => ['mines', 'Mines'],
					'Most Dangerous Sectors' => ['kills', 'Kills'],
				};
				$dbResult = $db->read('SELECT ' . $sql . ' as val, sector_id FROM sector WHERE game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY val DESC LIMIT 25');
				foreach ($dbResult->records() as $dbRecord) {
					$rankings[] = [
						'bold' => '',
						'data' => [
							$dbRecord->getInt('sector_id'),
							$dbRecord->getInt('val'),
						],
					];
				}
				$headers = ['Sector', $header];
			} elseif (in_array($action, ['Top Alliance Kills', 'Top Alliance Deaths'], true)) {
				[$sql, $header] = match ($action) {
					'Top Alliance Kills' => ['kills', 'Kills'],
					'Top Alliance Deaths' => ['deaths', 'Deaths'],
				};
				// Determine which alliance this account was in
				$dbResult = $db->read('SELECT alliance_id FROM player WHERE game_id = ' . $db->escapeNumber($game_id) . ' AND account_id = ' . $db->escapeNumber($oldAccountID));
				$oldAllianceID = $dbResult->hasRecord() ? $dbResult->record()->getInt('alliance_id') : 0;
				// Get the top 25 alliance ordered by the requested stat
				$dbResult = $db->read('SELECT alliance_name, alliance_id, ' . $sql . ' as val FROM alliance WHERE game_id = ' . $db->escapeNumber($game_id) . ' AND alliance_id > 0 GROUP BY alliance_id ORDER BY val DESC, alliance_id LIMIT 25');
				foreach ($dbResult->records() as $dbRecord) {
					$allianceID = $dbRecord->getInt('alliance_id');
					$name = htmlentities($dbRecord->getString('alliance_name'));
					$container = new AllianceDetail($this->historyDatabase, $this->historyGameID, $this->historyGameName, $allianceID, $this);
					$rankings[] = [
						'bold' => $oldAllianceID == $allianceID ? 'class="bold"' : '',
						'data' => [
							create_link($container, $name),
							$dbRecord->getInt('val'),
						],
					];
				}
				$headers = ['Alliance', $header];
			} elseif ($action == 'Top Planets') {
				$dbResult = $db->read('SELECT sector_id, owner_id, IFNULL(player_name, \'Unclaimed\') as player_name, IFNULL(alliance_name, \'None\') as alliance_name, IFNULL(player.alliance_id, 0) as alliance_id, ROUND((turrets + hangers + generators) / 3, 2) as level FROM planet LEFT JOIN player ON planet.owner_id = player.account_id AND planet.game_id = player.game_id LEFT JOIN alliance ON player.alliance_id = alliance.alliance_id AND planet.game_id = alliance.game_id WHERE planet.game_id = ' . $db->escapeNumber($game_id) . ' ORDER BY level DESC LIMIT 25');
				foreach ($dbResult->records() as $dbRecord) {
					$ownerID = $dbRecord->getInt('owner_id');
					$allianceID = $dbRecord->getInt('alliance_id');
					$allianceName = $dbRecord->getString('alliance_name');
					if ($allianceID != 0) {
						$container = new AllianceDetail($this->historyDatabase, $this->historyGameID, $this->historyGameName, $allianceID, $this);
						$allianceName = create_link($container, $allianceName);
					}
					$rankings[] = [
						'bold' => $ownerID > 0 && $oldAccountID == $ownerID ? 'class="bold"' : '',
						'data' => [
							$dbRecord->getFloat('level'),
							$dbRecord->getString('player_name'),
							$allianceName,
							$dbRecord->getInt('sector_id'),
						],
					];
				}
				$headers = ['Level', 'Owner', 'Alliance', 'Sector'];
			} else {
				throw new Exception('Unknown action');
			}
			$template->assign('Rankings', $rankings);
			$template->assign('Headers', $headers);
		}
	}

}
