<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\AbstractPlayer;
use Smr\Page\PlayerPageProcessor;
use Smr\SectorsFile;

class SectorsFileDownloadProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $gameID
	) {}

	public function build(AbstractPlayer $player): never {
		SectorsFile::create($this->gameID, player: null, adminCreate: true);
	}

}
