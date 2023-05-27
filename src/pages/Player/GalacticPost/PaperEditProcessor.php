<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;

class PaperEditProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $paperID,
		private readonly int $articleID,
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();
		$db->delete('galactic_post_paper_content', [
			'game_id' => $player->getGameID(),
			'article_id' => $this->articleID,
			'paper_id' => $this->paperID,
		]);

		$container = new PaperEdit($this->paperID);
		$container->go();
	}

}
