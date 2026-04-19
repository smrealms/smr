<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;

class ArticleAddToNewsProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $articleID,
	) {}

	public function build(Player $player): never {
		$db = Database::getInstance();
		$dbResult = $db->select(
			'galactic_post_article',
			['game_id' => $player->getGameID(), 'article_id' => $this->articleID],
			['text'],
		);
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
