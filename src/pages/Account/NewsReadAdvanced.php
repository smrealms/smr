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
	/**
	 * @param array<int> $playerIDs
	 * @param array<int> $allianceIDs
	 */
	public function __construct(
		private readonly int $gameID,
		private readonly ?string $submit = null,
		private readonly ?string $label = null,
		private readonly array $playerIDs = [],
		private readonly array $allianceIDs = [],
	) {}

	public function build(Account $account, Template $template): void {
		$gameID = $this->gameID;

		$db = Database::getInstance();
		$dbResult = $db->select('alliance', ['game_id' => $gameID], ['alliance_id', 'alliance_name']);

		$newsAlliances = [0 => 'None'];
		foreach ($dbResult->records() as $dbRecord) {
			$newsAlliances[$dbRecord->getInt('alliance_id')] = htmlentities($dbRecord->getString('alliance_name'));
		}

		$processor = new NewsReadAdvancedProcessor($this->gameID);

		// No submit value when first navigating to the page
		$submit_value = $this->submit;

		if ($submit_value === $processor->actionSearchPlayer->value) {
			$resultsFor = $this->label;
			$dbResult = $db->read('SELECT * FROM news WHERE killer_player_id IN (:player_ids) OR dead_player_id IN (:player_ids) ORDER BY news_id DESC', [
				'player_ids' => $db->escapeArray($this->playerIDs),
			]);
		} elseif ($submit_value === $processor->actionSearchAlliance->value) {
			$allianceID = $this->allianceIDs[0];
			$resultsFor = $newsAlliances[$allianceID];
			$dbResult = $db->read('SELECT * FROM news WHERE game_id = :game_id AND ((killer_alliance = :alliance_id AND killer_player_id != :player_id_port) OR (dead_alliance = :alliance_id AND dead_player_id != :player_id_port)) ORDER BY news_id DESC', [
				'game_id' => $db->escapeNumber($gameID),
				'player_id_port' => $db->escapeNumber(PLAYER_ID_PORT),
				'alliance_id' => $db->escapeNumber($allianceID),
			]);
		} elseif ($submit_value === $processor->actionSearchPlayers->value) {
			$resultsFor = $this->label;
			$dbResult = $db->read('SELECT * FROM news
						WHERE (
								killer_player_id IN (:player_ids) AND dead_player_id IN (:player_ids)
							) ORDER BY news_id DESC', [
				'player_ids' => $db->escapeArray($this->playerIDs),
			]);
		} elseif ($submit_value === $processor->actionSearchAlliances->value) {
			$allianceID1 = $this->allianceIDs[0];
			$allianceID2 = $this->allianceIDs[1];
			$resultsFor = $newsAlliances[$allianceID1] . ' vs. ' . $newsAlliances[$allianceID2];
			$dbResult = $db->read('SELECT * FROM news
						WHERE game_id = :game_id
							AND (
								(killer_alliance = :alliance_id_1 AND dead_alliance = :alliance_id_2)
								OR
								(killer_alliance = :alliance_id_2 AND dead_alliance = :alliance_id_1)
							) ORDER BY news_id DESC', [
				'game_id' => $db->escapeNumber($gameID),
				'alliance_id_1' => $db->escapeNumber($allianceID1),
				'alliance_id_2' => $db->escapeNumber($allianceID2),
			]);
		} else {
			$resultsFor = null;
			$dbResult = $db->select(
				'news',
				['game_id' => $gameID],
				orderBy: ['news_id'],
				order: ['DESC'],
				limit: 50,
			);
		}

		$template->pageTopic = 'Advanced News';
		Menu::news($gameID);

		$template->pageRenderer = fn() => NewsReadAdvancedRenderer::render(
			NewsAlliances: $newsAlliances,
			AdvancedNewsForm: $processor,
			ResultsFor: $resultsFor,
			NewsItems: News::getNewsItems($dbResult),
		);
	}

}
