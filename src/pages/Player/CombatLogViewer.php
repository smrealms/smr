<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class CombatLogViewer extends PlayerPage {

	use ReusableTrait;

	public string $file = 'combat_log_viewer.php';

	/**
	 * @param non-empty-array<int> $logIDs
	 */
	public function __construct(
		private readonly array $logIDs,
		private readonly int $currentLog = 0
	) {}

	public function build(AbstractPlayer $player, Template $template): void {
		// Set properties for the current display page
		$display_id = $this->logIDs[$this->currentLog];
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT timestamp,sector_id,result,type FROM combat_logs WHERE log_id=' . $db->escapeNumber($display_id) . ' LIMIT 1');

		$dbRecord = $dbResult->record();
		$template->assign('CombatLogSector', $dbRecord->getInt('sector_id'));
		$template->assign('CombatLogTimestamp', date($player->getAccount()->getDateTimeFormat(), $dbRecord->getInt('timestamp')));
		$results = $dbRecord->getObject('result', true);
		$template->assign('CombatResultsType', $dbRecord->getString('type'));
		$template->assign('CombatResults', $results);

		// Create a container for the next/previous log.
		// We initialize it with the current $var, then modify it to set
		// which log to view when we press the next/previous log buttons.
		if ($this->currentLog > 0) {
			$container = new self($this->logIDs, $this->currentLog - 1);
			$template->assign('PreviousLogHREF', $container->href());
		}
		if ($this->currentLog < count($this->logIDs) - 1) {
			$container = new self($this->logIDs, $this->currentLog + 1);
			$template->assign('NextLogHREF', $container->href());
		}

		$template->assign('PageTopic', 'Combat Logs');
		Menu::combatLog();
	}

}
