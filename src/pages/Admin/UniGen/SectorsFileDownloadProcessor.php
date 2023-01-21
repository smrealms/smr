<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Page\AccountPageProcessor;
use Smr\SectorsFile;

class SectorsFileDownloadProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID
	) {}

	public function build(Account $account): never {
		SectorsFile::create($this->gameID, player: null, adminCreate: true);
	}

}
