<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPageProcessor;

class ChangelogSetLiveProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $versionID
	) {}

	public function build(Account $account): never {
		$db = Database::getInstance();
		$db->update(
			'version',
			['went_live' => $db->escapeNumber(Epoch::time())],
			['version_id' => $db->escapeNumber($this->versionID)],
		);

		// Initialize the next version (since the version set live is not always the
		// last one, we INSERT IGNORE to skip this step in this case).
		$dbResult = $db->read('SELECT * FROM version WHERE version_id = :version_id', [
			'version_id' => $db->escapeNumber($this->versionID),
		]);
		$dbRecord = $dbResult->record();
		$versionID = $dbRecord->getInt('version_id') + 1;
		$major = $dbRecord->getInt('major_version');
		$minor = $dbRecord->getInt('minor_version');
		$patch = $dbRecord->getInt('patch_level') + 1;
		$db->write('INSERT IGNORE INTO version (version_id, major_version, minor_version, patch_level, went_live) VALUES
					(:version_id, :major_version, :minor_version, :patch_level, 0)', [
			'version_id' => $db->escapeNumber($versionID),
			'major_version' => $db->escapeNumber($major),
			'minor_version' => $db->escapeNumber($minor),
			'patch_level' => $db->escapeNumber($patch),
		]);

		(new Changelog())->go();
	}

}
