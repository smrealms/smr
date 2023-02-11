<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Page\ReusableTrait;

class CurrentEditionProcessor extends PlayerPageProcessor {

	use ReusableTrait;

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM galactic_post_paper WHERE online_since IS NOT NULL AND game_id = :game_id ORDER BY online_since DESC LIMIT 1', [
			'game_id' => $db->escapeNumber($player->getGameID()),
		]);
		if ($dbResult->hasRecord()) {
			$paper_id = $dbResult->record()->getInt('paper_id');
		} else {
			$paper_id = null;
		}

		$container = new EditionRead($player->getGameID(), $paper_id);
		$container->go();
	}

}
