<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;

class PaperEditProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $paperID,
		private readonly int $articleID
	) {}

	public function build(AbstractPlayer $player): never {
		$db = Database::getInstance();
		$db->write('DELETE FROM galactic_post_paper_content WHERE game_id = :game_id AND article_id = :article_id AND paper_id = :paper_id', [
			'game_id' => $db->escapeNumber($player->getGameID()),
			'article_id' => $db->escapeNumber($this->articleID),
			'paper_id' => $db->escapeNumber($this->paperID),
		]);

		$container = new PaperEdit($this->paperID);
		$container->go();
	}

}
