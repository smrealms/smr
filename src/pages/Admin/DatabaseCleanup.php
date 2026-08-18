<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class DatabaseCleanup extends AccountPage {

	/**
	 * @param ?array{preview: bool, rowsDeleted: array<string, int>, diffBytes: int, endedGameIDs: array<int>} $results
	 */
	public function __construct(
		private readonly ?array $results = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Database Cleanup';

		$bytesToMB = function(int $bytes): string {
			return round($bytes / (1024 * 1024), 1) . ' MB';
		};

		$db = Database::getInstance();
		$dbSizeMB = $bytesToMB($db->getDbBytes());

		if ($this->results !== null) {
			// Display the results
			$template->pageRenderer = fn() => DatabaseCleanupRenderer::renderResults(
				DbSizeMB: $dbSizeMB,
				Results: $this->results['rowsDeleted'],
				DiffMB: $bytesToMB($this->results['diffBytes']),
				Preview: $this->results['preview'],
				EndedGames: $this->results['endedGameIDs'],
				BackHREF: new self()->href(),
			);
		} else {
			// Create processing links
			$template->pageRenderer = fn() => DatabaseCleanupRenderer::render(
				DbSizeMB: $dbSizeMB,
				DeleteHREF: new DatabaseCleanupProcessor('delete')->href(),
				PreviewHREF: new DatabaseCleanupProcessor('preview')->href(),
			);
		}
	}

}
