<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Galaxy;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use Smr\Sector;

class RedoConnectionsProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID,
		private readonly EditGalaxy $returnTo,
	) {}

	public function build(Account $account): never {
		$galaxy = Galaxy::getGalaxy($this->gameID, $this->galaxyID);
		$connectivity = Request::getFloat('connect');
		if (!$galaxy->setConnectivity($connectivity)) {
			$message = '<span class="red">Error</span> : Failed to reach ' . $connectivity . '% connectivity target!';
		} else {
			$message = '<span class="green">Success</span> : Regenerated connectivity with ' . $connectivity . '% target.';
		}
		Sector::saveSectors();

		$this->returnTo->message = $message;
		$this->returnTo->go();
	}

}
