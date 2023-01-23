<?php declare(strict_types=1);

namespace Smr\Pages\Account\HistoryGames;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

abstract class HistoryPage extends AccountPage {

	public function __construct(
		protected readonly string $historyDatabase,
		protected readonly int $historyGameID,
		protected readonly string $historyGameName
	) {}

	abstract protected function buildHistory(Account $account, Template $template): void;

	protected function addMenu(Template $template, ?string $currentClass = null): void {
		$menuPages = [
			'Game Details' => Summary::class,
			'Extended Stats' => ExtendedStats::class,
			'Hall of Fame' => HallOfFame::class,
			'Game News' => GameNews::class,
		];
		$menuItems = [];
		$currentClass ??= $this::class;
		foreach ($menuPages as $text => $class) {
			if ($class === $currentClass) {
				$text = '<b>' . $text . '</b>';
			}
			$menuItems[] = [
				'Link' => (new $class($this->historyDatabase, $this->historyGameID, $this->historyGameName))->href(),
				'Text' => $text,
			];
		}
		$template->assign('MenuItems', $menuItems);
	}

	public function build(Account $account, Template $template): void {
		$db = Database::getInstance();
		$db->switchDatabases($this->historyDatabase);

		$this->buildHistory($account, $template);

		$db->switchDatabaseToLive(); // restore database
	}

}
