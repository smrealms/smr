<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Database;
use Smr\Menu;
use Smr\News;
use Smr\Page\AccountPage;
use Smr\Page\ReusableTrait;
use Smr\Request;
use Smr\Template;

class NewsReadArchives extends AccountPage {

	use ReusableTrait;

	public string $file = 'news_read.php';

	public function __construct(
		private readonly int $gameID
	) {}

	public function build(Account $account, Template $template): void {
		$gameID = $this->gameID;

		$min_news = Request::getInt('min_news', 1);
		$max_news = Request::getInt('max_news', 50);
		if ($min_news > $max_news) {
			create_error('The first number must be lower than the second number!');
		}
		$template->assign('MinNews', $min_news);
		$template->assign('MaxNews', $max_news);

		$template->assign('PageTopic', 'Reading The News');

		Menu::news($gameID);

		News::doBreakingNewsAssign($gameID);
		News::doLottoNewsAssign($gameID);

		$template->assign('ViewNewsFormHref', (new self($this->gameID))->href());

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM news WHERE game_id = :game_id AND type != \'lotto\' ORDER BY news_id DESC LIMIT :limit_offset, :limit_count', [
			'game_id' => $db->escapeNumber($gameID),
			'limit_count' => $max_news - $min_news + 1,
			'limit_offset' => $min_news - 1,
		]);
		$template->assign('NewsItems', News::getNewsItems($dbResult));
	}

}
