<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;

class PaperEditProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $paperID,
		private readonly int $articleID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$db = Database::getInstance();
		$db->write('DELETE FROM galactic_post_paper_content WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($this->articleID) . ' AND paper_id = ' . $db->escapeNumber($this->paperID));

		$container = new PaperEdit($this->paperID);
		$container->go();
	}

}
