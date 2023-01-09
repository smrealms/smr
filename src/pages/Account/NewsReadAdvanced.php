<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Menu;
use Smr\News;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class NewsReadAdvanced extends AccountPage {

	use ReusableTrait;

	public string $file = 'news_read_advanced.php';

	/**
	 * @param ?array<int> $accountIDs
	 * @param ?array<int> $allianceIDs
	 */
	public function __construct(
		private readonly int $gameID,
		private readonly ?string $submit = null,
		private readonly ?string $label = null,
		private readonly ?array $accountIDs = null,
		private readonly ?array $allianceIDs = null,
	) {}

	public function build(Account $account, Template $template): void {
		$gameID = $this->gameID;

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT alliance_id, alliance_name
					FROM alliance
					WHERE game_id = ' . $db->escapeNumber($gameID));

		$newsAlliances = [0 => 'None'];
		foreach ($dbResult->records() as $dbRecord) {
			$newsAlliances[$dbRecord->getInt('alliance_id')] = htmlentities($dbRecord->getString('alliance_name'));
		}
		$template->assign('NewsAlliances', $newsAlliances);

		$template->assign('AdvancedNewsFormHref', (new NewsReadAdvancedProcessor($this->gameID))->href());

		// No submit value when first navigating to the page
		$submit_value = $this->submit;

		if ($submit_value == 'Search For Player') {
			$template->assign('ResultsFor', $this->label);
			$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND (killer_id IN (' . $db->escapeArray($this->accountIDs) . ') OR dead_id IN (' . $db->escapeArray($this->accountIDs) . ')) ORDER BY news_id DESC');
		} elseif ($submit_value == 'Search For Alliance') {
			$allianceID = $this->allianceIDs[0];
			$template->assign('ResultsFor', $newsAlliances[$allianceID]);
			$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND ((killer_alliance = ' . $db->escapeNumber($allianceID) . ' AND killer_id != ' . $db->escapeNumber(ACCOUNT_ID_PORT) . ') OR (dead_alliance = ' . $db->escapeNumber($allianceID) . ' AND dead_id != ' . $db->escapeNumber(ACCOUNT_ID_PORT) . ')) ORDER BY news_id DESC');
		} elseif ($submit_value == 'Search For Players') {
			$template->assign('ResultsFor', $this->label);
			$dbResult = $db->read('SELECT * FROM news
						WHERE game_id = ' . $db->escapeNumber($gameID) . '
							AND (
								killer_id IN (' . $db->escapeArray($this->accountIDs) . ') AND dead_id IN (' . $db->escapeArray($this->accountIDs) . ')
							) ORDER BY news_id DESC');
		} elseif ($submit_value == 'Search For Alliances') {
			$allianceID1 = $this->allianceIDs[0];
			$allianceID2 = $this->allianceIDs[1];
			$template->assign('ResultsFor', $newsAlliances[$allianceID1] . ' vs. ' . $newsAlliances[$allianceID2]);
			$dbResult = $db->read('SELECT * FROM news
						WHERE game_id = ' . $db->escapeNumber($gameID) . '
							AND (
								(killer_alliance = ' . $db->escapeNumber($allianceID1) . ' AND dead_alliance = ' . $db->escapeNumber($allianceID2) . ')
								OR
								(killer_alliance = ' . $db->escapeNumber($allianceID2) . ' AND dead_alliance = ' . $db->escapeNumber($allianceID1) . ')
							) ORDER BY news_id DESC');
		} else {
			$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' ORDER BY news_id DESC LIMIT 50');
		}

		$template->assign('NewsItems', News::getNewsItems($dbResult));

		$template->assign('PageTopic', 'Advanced News');
		Menu::news($gameID);
	}

}
