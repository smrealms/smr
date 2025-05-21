<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class EditGameCreateStatusProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameId,
		private readonly EditGalaxy $returnTo,
	) {}

	public function build(Account $account): never {
		$mapReady = Request::has('map_ready') ? date('Y-m-d', Epoch::time()) : null;
		$allEdit = Request::has('all_edit');

		$db = Database::getInstance();
		$db->update(
			'game_create_status',
			[
				'ready_date' => $db->escapeNullableString($mapReady),
				'all_edit' => $db->escapeBoolean($allEdit),
			],
			[
				'game_id' => $db->escapeNumber($this->gameId),
			],
		);

		$this->returnTo->go();
	}

}
