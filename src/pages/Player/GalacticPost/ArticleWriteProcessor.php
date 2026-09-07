<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use Smr\Database;
use Smr\Epoch;
use Smr\Globals;
use Smr\Html\Submit;
use Smr\Page\PlayerPageProcessor;
use Smr\Pages\Player\CurrentSector;
use Smr\Player;
use Smr\Request;

class ArticleWriteProcessor extends PlayerPageProcessor {

	private const string ACTION = 'action';

	public readonly Submit $actionPreview;
	public readonly Submit $actionSubmit;

	public function __construct(
		private readonly ?int $articleID = null,
	) {
		$this->actionPreview = new Submit(self::ACTION, 'Preview article');
		$this->actionSubmit = new Submit(self::ACTION, 'Submit article');
	}

	public function build(Player $player): never {
		$title = Request::get('title');
		$message = Request::get('message');
		if (!$player->isGPEditor()) {
			$title = htmlentities($title, ENT_COMPAT, 'utf-8');
			$message = htmlentities($message, ENT_COMPAT, 'utf-8');
		}

		if (Request::get(self::ACTION) === $this->actionPreview->value) {
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
			new ArticleView($this->articleID)->go();
		} else {
			// Adding a new article
			$editorMsg = 'Dear Galactic Post editors,<br /><br />' . $player->getBBLink() . ' has just submitted an article to the Galactic Post!';
			foreach (Globals::getGalacticPostEditorPlayerIDs($player->getGameID()) as $editorPlayerID) {
				if ($editorPlayerID !== $player->getPlayerID()) {
					$editor = Player::getPlayer($editorPlayerID);
					Player::sendMessageFromAdmin($editor->getPlayerID(), $editorMsg);
				}
			}

			$dbResult = $db->read('SELECT IFNULL(MAX(article_id)+1, 0) AS next_article_id FROM galactic_post_article WHERE game_id = :game_id', [
				'game_id' => $db->escapeNumber($player->getGameID()),
			]);
			$num = $dbResult->record()->getInt('next_article_id');

			$db->insert('galactic_post_article', [
				'game_id' => $player->getGameID(),
				'article_id' => $num,
				'writer_player_id' => $player->getPlayerID(),
				'title' => $title,
				'text' => $message,
				'last_modified' => Epoch::time(),
			]);
			$db->update(
				'galactic_post_writer',
				['last_wrote' => Epoch::time()],
				['player_id' => $player->getPlayerID()],
			);
			$msg = '<span class="green">SUCCESS</span>: Your article has been submitted.';
			$container = new CurrentSector(message: $msg);
			$container->go();
		}
	}

}
