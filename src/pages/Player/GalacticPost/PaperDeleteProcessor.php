<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\Request;

class PaperDeleteProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $paperID,
	) {}

	public function build(Player $player): never {
		$db = Database::getInstance();
		// Should we delete this paper?
		if (Request::getBool('action')) {
			$sqlParams = [
				'game_id' => $player->getGameID(),
				'paper_id' => $this->paperID,
			];

			// Should the articles associated with the paper be deleted as well?
			if (Request::getBool('delete_articles')) {
				$dbResult = $db->select('galactic_post_paper_content', $sqlParams);
				foreach ($dbResult->records() as $dbRecord) {
					$db->delete('galactic_post_article', [
						'article_id' => $db->escapeNumber($dbRecord->getInt('article_id')),
						'game_id' => $db->escapeNumber($player->getGameID()),
					]);
				}
			}

			// Delete the paper and the article associations
			$db->delete('galactic_post_paper', $sqlParams);
			$db->delete('galactic_post_paper_content', $sqlParams);
		}

		$container = new EditorOptions();
		$container->go();
	}

}
