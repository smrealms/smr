<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Account;
use Smr\Database;
use Smr\Page\AccountPageProcessor;

class AlbumApproveProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $albumAccountID,
		private readonly bool $approved,
	) {}

	public function build(Account $account): never {
		$approved = $this->approved ? 'YES' : 'NO';

		$db = Database::getInstance();
		$db->update(
			'album',
			['approved' => $approved],
			['account_id' => $this->albumAccountID],
		);

		(new AlbumApprove())->go();
	}

}
