<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Menu;
use Smr\Database;
use Smr\News;
use Smr\Page\PlayerPage;
use Smr\Page\ReusableTrait;
use Smr\Template;

class NewsReadCurrent extends PlayerPage {

	use ReusableTrait;

	public string $file = 'news_read_current.php';

	public function __construct(
		private ?int $lastNewsUpdate = null
	) {}

	public function build(AbstractSmrPlayer $player, Template $template): void {
		$gameID = $player->getGameID();

		$template->assign('PageTopic', 'Current News');
		Menu::news($gameID);

		News::doBreakingNewsAssign($gameID);
		News::doLottoNewsAssign($gameID);

		if ($this->lastNewsUpdate === null) {
			$this->lastNewsUpdate = $player->getLastNewsUpdate();
		}

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $db->escapeNumber($gameID) . ' AND time > ' . $db->escapeNumber($this->lastNewsUpdate) . ' AND type != \'lotto\' ORDER BY news_id DESC');
		$template->assign('NewsItems', News::getNewsItems($dbResult));

		$player->updateLastNewsUpdate();
	}

}
