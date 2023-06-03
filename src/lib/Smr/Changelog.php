<?php declare(strict_types=1);

namespace Smr;

class Changelog {

	/**
	 * @return array<int, array{version: string, went_live: ?string, changes: array<array{title: string, message: string}>}>
	 */
	public static function getDisplayVersions(int $since, string $dateFormat): array {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT *
					FROM version
					WHERE went_live > :epoch
					ORDER BY version_id DESC', [
			'epoch' => $db->escapeNumber($since),
		]);

		$versions = [];
		foreach ($dbResult->records() as $dbRecord) {
			$version_id = $dbRecord->getInt('version_id');
			$version = $dbRecord->getInt('major_version') . '.' . $dbRecord->getInt('minor_version') . '.' . $dbRecord->getInt('patch_level');
			$went_live = $dbRecord->getInt('went_live');

			// get human readable format for date
			if ($went_live > 0) {
				$went_live = date($dateFormat, $went_live);
			} else {
				$went_live = null;
			}

			$dbResult2 = $db->read('SELECT *
						FROM changelog
						WHERE version_id = :version_id
						ORDER BY changelog_id', [
				'version_id' => $db->escapeNumber($version_id),
			]);
			$changes = [];
			foreach ($dbResult2->records() as $dbRecord2) {
				$changes[] = [
					'title' => htmlentities($dbRecord2->getString('change_title')),
					'message' => bbifyMessage(htmlentities($dbRecord2->getString('change_message'))),
				];
			}

			$versions[$version_id] = [
				'version' => $version,
				'went_live' => $went_live,
				'changes' => $changes,
			];
		}

		return $versions;
	}

}
