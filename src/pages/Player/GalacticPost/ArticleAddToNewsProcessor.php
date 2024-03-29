<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;

class ArticleAddToNewsProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $articleID,
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();
		$dbResult = $db->read('SELECT text FROM galactic_post_article WHERE game_id = :game_id AND article_id = :article_id', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'article_id' => $db->escapeNumber($this->articleID),
		]);
		$dbRecord = $dbResult->record();
		$newsMessage = $dbRecord->getString('text');

		$db->insert('news', [
			'game_id' => $player->getGameID(),
			'time' => Epoch::time(),
			'news_message' => $newsMessage,
			'type' => 'breaking',
		]);

		$container = new ArticleView($this->articleID, addedToNews: true);
		$container->go();
	}

}
