<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class PaperDeleteProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $paperID
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();
		// Should we delete this paper?
		if (Request::getBool('action')) {

			// Should the articles associated with the paper be deleted as well?
			if (Request::getBool('delete_articles')) {
				$dbResult = $db->read('SELECT * FROM galactic_post_paper_content WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($this->paperID));
				foreach ($dbResult->records() as $dbRecord) {
					$db->write('DELETE FROM galactic_post_article WHERE article_id = ' . $db->escapeNumber($dbRecord->getInt('article_id')) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
				}
			}

			// Delete the paper and the article associations
			$db->write('DELETE FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($this->paperID));
			$db->write('DELETE FROM galactic_post_paper_content WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND paper_id = ' . $db->escapeNumber($this->paperID));
		}

		$container = new EditorOptions();
		$container->go();
	}

}
