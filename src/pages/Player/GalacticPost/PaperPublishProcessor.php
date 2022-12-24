<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;

class PaperPublishProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $paperID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		// Make sure this paper hasn't been published before
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM galactic_post_paper WHERE online_since IS NOT NULL AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($this->paperID));
		if ($dbResult->hasRecord()) {
			create_error('Cannot publish a paper that has previously been published!');
		}

		// Update the online_since column
		$db->write('UPDATE galactic_post_paper SET online_since=' . $db->escapeNumber(Epoch::time()) . ' WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($this->paperID));

		//all done lets send back to the main GP page.
		$container = new EditorOptions();
		$container->go();
	}

}