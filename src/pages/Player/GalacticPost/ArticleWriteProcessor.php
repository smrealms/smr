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

		if (Request::get('action') == 'Preview article') {
			$container = new ArticleWrite($this->articleID, $title, $message);
			$container->go();
		}

		$db = Database::getInstance();
		if ($this->articleID !== null) {
			// Editing an article
			$db->write('UPDATE galactic_post_article SET last_modified = :now, text = :text, title = :title WHERE game_id = :game_id AND article_id = :article_id', [
				'now' => $db->escapeNumber(Epoch::time()),
				'text' => $db->escapeString($message),
				'title' => $db->escapeString($title),
				'game_id' => $db->escapeNumber($player->getGameID()),
				'article_id' => $db->escapeNumber($this->articleID),
			]);
			(new ArticleView($this->articleID))->go();
		} else {
			// Adding a new article
			$editorMsg = 'Dear Galactic Post editors,<br /><br />[player=' . $player->getPlayerID() . '] has just submitted an article to the Galactic Post!';
			foreach (Globals::getGalacticPostEditorIDs($player->getGameID()) as $editorID) {
				if ($editorID != $player->getAccountID()) {
					Player::sendMessageFromAdmin($player->getGameID(), $editorID, $editorMsg);
				}
			}

			$dbResult = $db->read('SELECT IFNULL(MAX(article_id)+1, 0) AS next_article_id FROM galactic_post_article WHERE game_id = :game_id', [
				'game_id' => $db->escapeNumber($player->getGameID()),
			]);
			$num = $dbResult->record()->getInt('next_article_id');

			$db->insert('galactic_post_article', [
				'game_id' => $db->escapeNumber($player->getGameID()),
				'article_id' => $db->escapeNumber($num),
				'writer_id' => $db->escapeNumber($player->getAccountID()),
				'title' => $db->escapeString($title),
				'text' => $db->escapeString($message),
				'last_modified' => $db->escapeNumber(Epoch::time()),
			]);
			$db->write('UPDATE galactic_post_writer SET last_wrote = :now WHERE account_id = :account_id', [
				'now' => $db->escapeNumber(Epoch::time()),
				'account_id' => $db->escapeNumber($player->getAccountID()),
			]);
			$msg = '<span class="green">SUCCESS</span>: Your article has been submitted.';
			$container = new CurrentSector(message: $msg);
			$container->go();
		}
	}

}
