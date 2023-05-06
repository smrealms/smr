<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Globals;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;
use Smr\Player;
use Smr\Request;

class ArticleWriteProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly ?int $articleID = null
	) {}

	public function build(AbstractPlayer $player): never {
		$title = Request::get('title');
		$message = Request::get('message');
		if (!$player->isGPEditor()) {
			$title = htmlentities($title, ENT_COMPAT, 'utf-8');
			$message = htmlentities($message, ENT_COMPAT, 'utf-8');
		}

		if (Request::get('action') === 'Preview article') {
			$container = new ArticleWrite($this->articleID, $title, $message);
			$container->go();
		}

		$db = Database::getInstance();
		if ($this->articleID !== null) {
			// Editing an article
			$db->update(
				'galactic_post_article',
				[
					'last_modified' => Epoch::time(),
					'text' => $message,
					'title' => $title,
				],
				[
					'game_id' => $player->getGameID(),
					'article_id' => $this->articleID,
				],
			);
			(new ArticleView($this->articleID))->go();
		} else {
			// Adding a new article
			$editorMsg = 'Dear Galactic Post editors,<br /><br />[player=' . $player->getPlayerID() . '] has just submitted an article to the Galactic Post!';
			foreach (Globals::getGalacticPostEditorIDs($player->getGameID()) as $editorID) {
				if ($editorID !== $player->getAccountID()) {
					Player::sendMessageFromAdmin($player->getGameID(), $editorID, $editorMsg);
				}
			}

			$dbResult = $db->read('SELECT IFNULL(MAX(article_id)+1, 0) AS next_article_id FROM galactic_post_article WHERE game_id = :game_id', [
				'game_id' => $db->escapeNumber($player->getGameID()),
			]);
			$num = $dbResult->record()->getInt('next_article_id');

			$db->insert('galactic_post_article', [
				'game_id' => $player->getGameID(),
				'article_id' => $num,
				'writer_id' => $player->getAccountID(),
				'title' => $title,
				'text' => $message,
				'last_modified' => Epoch::time(),
			]);
			$db->update(
				'galactic_post_writer',
				['last_wrote' => Epoch::time()],
				['account_id' => $player->getAccountID()],
			);
			$msg = '<span class="green">SUCCESS</span>: Your article has been submitted.';
			$container = new CurrentSector(message: $msg);
			$container->go();
		}
	}

}
