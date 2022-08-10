<?php declare(strict_types=1);

namespace Smr\Pages\Admin;

use Smr\Database;
use Smr\Page\AccountPageProcessor;
use SmrAccount;

class AlbumApproveProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $albumAccountID,
		private readonly bool $approved
	) {}

	public function build(SmrAccount $account): never {
		$approved = $this->approved ? 'YES' : 'NO';

		$db = Database::getInstance();
		$db->write('UPDATE album
					SET approved = ' . $db->escapeString($approved) . '
					WHERE account_id = ' . $db->escapeNumber($this->albumAccountID));

		(new AlbumApprove())->go();
	}

}
