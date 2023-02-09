<?php declare(strict_types=1);

namespace Smr\Pages\Account\HistoryGames;

use Smr\Account;
use Smr\Template;

class ExtendedStats extends HistoryPage {

	public string $file = 'history_games_extended_stats.php';

	public function __construct(
		protected readonly string $historyDatabase,
		protected readonly int $historyGameID,
		protected readonly string $historyGameName,
	) {}

	protected function buildHistory(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Extended Stats : ' . $this->historyGameName);
		$this->addMenu($template);

		$categories = [
			'Most Dangerous Sectors',
			'Top Mined Sectors',
			'Top Planets',
			'Top Alliance Kills',
			'Top Alliance Deaths',
		];
		$links = [];
		foreach ($categories as $category) {
			$container = new ExtendedStatsDetail($this->historyDatabase, $this->historyGameID, $this->historyGameName, $category);
			$links[$category] = $container->href();
		}
		$template->assign('Links', $links);
	}

}
