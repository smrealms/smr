<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPage;
use Smr\Template;

class DatabaseCleanup extends AccountPage {

	public string $file = 'admin/db_cleanup.php';

	/**
	 * @param ?array{preview: bool, rowsDeleted: array<string, int>, diffBytes: int, endedGameIDs: array<int>} $results
	 */
	public function __construct(
		private readonly ?array $results = null,
	) {}

	public function build(Account $account, Template $template): void {
		$template->assign('PageTopic', 'Database Cleanup');

		$bytesToMB = function(int $bytes): string {
			return round($bytes / (1024 * 1024), 1) . ' MB';
		};

		$db = Database::getInstance();
		$template->assign('DbSizeMB', $bytesToMB($db->getDbBytes()));

		if ($this->results !== null) {
			// Display the results
			$template->assign('Results', $this->results['rowsDeleted']);
			$template->assign('DiffMB', $bytesToMB($this->results['diffBytes']));
			$template->assign('Preview', $this->results['preview']);
			$template->assign('EndedGames', $this->results['endedGameIDs']);
			$container = new self();
			$template->assign('BackHREF', $container->href());
		} else {
			// Create processing links
			$container = new DatabaseCleanupProcessor('delete');
			$template->assign('DeleteHREF', $container->href());
			$container = new DatabaseCleanupProcessor('preview');
			$template->assign('PreviewHREF', $container->href());
		}
	}

}
