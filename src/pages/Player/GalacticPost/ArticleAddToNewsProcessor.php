<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;

class ArticleAddToNewsProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $articleID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT text FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($this->articleID));
		$dbRecord = $dbResult->record();
		$newsMessage = $dbRecord->getString('text');

		$db->insert('news', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'time' => $db->escapeNumber(Epoch::time()),
			'news_message' => $db->escapeString($newsMessage),
			'type' => $db->escapeString('breaking'),
		]);

		$container = new ArticleView($this->articleID, addedToNews: true);
		$container->go();
	}

}
