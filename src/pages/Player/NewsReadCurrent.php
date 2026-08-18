<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Database;
use Smr\Menu;
use Smr\News;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Player;
use Smr\Template;

class NewsReadCurrent extends PlayerPage {

	use ReusableTrait;
	public function __construct(
		private ?int $lastNewsUpdate = null,
	) {}

	public function build(Player $player, Template $template): void {
		$gameID = $player->getGameID();

		$template->pageTopic = 'Current News';
		Menu::news($gameID);

		if ($this->lastNewsUpdate === null) {
			$this->lastNewsUpdate = $player->getLastNewsUpdate();
		}

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM news WHERE game_id = :game_id AND time > :last_news_update AND type != \'lotto\' ORDER BY news_id DESC', [
			'game_id' => $db->escapeNumber($gameID),
			'last_news_update' => $db->escapeNumber($this->lastNewsUpdate),
		]);
		$newsItems = News::getNewsItems($dbResult);

		$player->updateLastNewsUpdate();

		$template->pageRenderer = fn() => NewsReadCurrentRenderer::render(
			NewsItems: $newsItems,
			ThisAccount: $player->getAccount(),
			BreakingNews: News::getBreakingNews($gameID),
			LottoNews: News::getLottoNews($gameID),
		);
	}

}
