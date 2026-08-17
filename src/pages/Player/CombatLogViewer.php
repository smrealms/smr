<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Combat\Results\FullCombatResults;
use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class CombatLogViewer extends PlayerPage {

	use ReusableTrait;
	/**
	 * @param non-empty-array<int> $logIDs
	 */
	public function __construct(
		private readonly array $logIDs,
		private readonly int $currentLog = 0,
	) {}

	public function build(Player $player, Template $template): void {
		// Set properties for the current display page
		$display_id = $this->logIDs[$this->currentLog];
		$db = Database::getInstance();
		$dbResult = $db->select(
			'combat_logs',
			['log_id' => $display_id],
			['timestamp', 'sector_id', 'result', 'type'],
		);

		$dbRecord = $dbResult->record();
		$results = $dbRecord->getClass('result', FullCombatResults::class, true);

		// Create a container for the next/previous log.
		// We initialize it with the current $var, then modify it to set
		// which log to view when we press the next/previous log buttons.
		if ($this->currentLog > 0) {
			$previousLogHREF = new self($this->logIDs, $this->currentLog - 1)->href();
		} else {
			$previousLogHREF = null;
		}
		if ($this->currentLog < count($this->logIDs) - 1) {
			$nextLogHREF = new self($this->logIDs, $this->currentLog + 1)->href();
		} else {
			$nextLogHREF = null;
		}

		$template->pageTopic = 'Combat Logs';
		Menu::combatLog();

		$template->pageRenderer = fn() => CombatLogViewerRenderer::render(
			template: $template,
			CombatLogSector: $dbRecord->getInt('sector_id'),
			CombatLogTimestamp: date($player->getAccount()->getDateTimeFormat(), $dbRecord->getInt('timestamp')),
			CombatResults: $results,
			PreviousLogHREF: $previousLogHREF,
			NextLogHREF: $nextLogHREF,
			ThisPlayer: $player,
		);
	}

}
