<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Html\Submit;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use Smr\RowLockMode;

class ChangelogAddProcessor extends AccountPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionPreview;
	public readonly Submit $actionAdd;

	public function __construct(
		private readonly int $versionID,
	) {
		$this->actionPreview = new Submit(self::ACTION, 'Preview');
		$this->actionAdd = new Submit(self::ACTION, 'Add');
	}

	public function build(Account $account): never {
		$change_title = Request::get('change_title');
		$change_message = Request::get('change_message');
		$affected_db = Request::get('affected_db');

		if (Request::get(self::ACTION) === $this->actionPreview->value) {
			$container = new ChangelogAdd(
				changeTitle: $change_title,
				changeMessage: $change_message,
				affectedDb: $affected_db,
			);
			$container->go();
		}

		$db = Database::getInstance();
		$db->transaction(function() use ($affected_db, $change_message, $change_title, $db): void {
			$dbResult = $db->select(
				table: 'changelog',
				criteria: ['version_id' => $this->versionID],
				returnColumns: ['changelog_id'],
				orderBy: ['changelog_id'],
				order: ['DESC'],
				limit: 1,
				lock: RowLockMode::Update,
			);
			$changelog_id = $dbResult->hasRecord() ?
				$dbResult->record()->getInt('changelog_id') + 1 : 0;

			$db->insert('changelog', [
				'version_id' => $this->versionID,
				'changelog_id' => $changelog_id,
				'change_title' => $change_title,
				'change_message' => $change_message,
				'affected_db' => $affected_db,
			]);
		});

		$container = new ChangelogAdd();
		$container->go();
	}

}
