<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;

class ArticleAddToPaperProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $paperID,
		private readonly int $articleID
	) {}

	public function build(AbstractPlayer $player): never {
		//limit 4 per paper...make sure we arent over that
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT 1 FROM galactic_post_paper_content WHERE game_id = :game_id AND paper_id = :paper_id', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'paper_id' => $db->escapeNumber($this->paperID),
		]);
		if ($dbResult->getNumRecords() >= 8) {
			create_error('You can only have 8 articles per paper.');
		}
		$db->insert('galactic_post_paper_content', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'paper_id' => $db->escapeNumber($this->paperID),
			'article_id' => $db->escapeNumber($this->articleID),
		]);
		//we now have that article in the paper
		$container = new ArticleView();
		$container->go();
	}

}
