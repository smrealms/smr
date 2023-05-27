<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class ChangelogAddProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $versionID,
	) {}

	public function build(Account $account): never {
		$change_title = Request::get('change_title');
		$change_message = Request::get('change_message');
		$affected_db = Request::get('affected_db');

		if (Request::get('action') === 'Preview') {
			$container = new Changelog(
				changeTitle: $change_title,
				changeMessage: $change_message,
				affectedDb: $affected_db,
			);
			$container->go();
		}

		$db = Database::getInstance();
		$db->lockTable('changelog');

		$dbResult = $db->read('SELECT IFNULL(MAX(changelog_id)+1, 0) AS next_changelog_id
					FROM changelog
					WHERE version_id = :version_id', [
			'version_id' => $db->escapeNumber($this->versionID),
		]);
		$changelog_id = $dbResult->record()->getInt('next_changelog_id');

		$db->insert('changelog', [
			'version_id' => $this->versionID,
			'changelog_id' => $changelog_id,
			'change_title' => $change_title,
			'change_message' => $change_message,
			'affected_db' => $affected_db,
		]);
		$db->unlock();

		$container = new Changelog();
		$container->go();
	}

}
