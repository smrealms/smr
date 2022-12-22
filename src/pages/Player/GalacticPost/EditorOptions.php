<?php declare(strict_types=1);

namespace Smr\Pages\Player\GalacticPost;

use AbstractSmrPlayer;
use Exception;
use Menu;
use Smr\Database;
use Smr\Page\PlayerPage;
use Smr\Template;

class EditorOptions extends PlayerPage {

	public string $file = 'galactic_post.php';

	public function build(AbstractSmrPlayer $player, Template $template): void {
		if (!$player->isGPEditor()) {
			throw new Exception('Only the GP Editor is allowed to view this page!');
		}

		$template->assign('PageTopic', 'Galactic Post');
		Menu::galacticPost();

		$db = Database::getInstance();

		$container = new ArticleView();
		$template->assign('ViewArticlesHREF', $container->href());

		$container = new PaperMake();
		$template->assign('MakePaperHREF', $container->href());

		$dbResult = $db->read('SELECT * FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
		$papers = [];
		foreach ($dbResult->records() as $dbRecord) {
			$paper_id = $dbRecord->getInt('paper_id');
			$published = $dbRecord->getNullableInt('online_since') !== null;

			$dbResult2 = $db->read('SELECT count(*) FROM galactic_post_paper_content WHERE paper_id = ' . $db->escapeNumber($paper_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
			$numArticles = $dbResult2->record()->getInt('count(*)');
			$hasEnoughArticles = $numArticles > 2 && $numArticles < 9;

			$paper = [
				'title' => $dbRecord->getString('title'),
				'num_articles' => $numArticles,
				'color' => $hasEnoughArticles ? 'green' : 'red',
				'published' => $published,
			];

			if ($hasEnoughArticles && !$published) {
				$container = new PaperPublishProcessor($paper_id);
				$paper['PublishHREF'] = $container->href();
			}

			$container = new PaperDeleteConfirm($paper_id);
			$paper['DeleteHREF'] = $container->href();

			$container = new PaperEdit($paper_id);
			$paper['EditHREF'] = $container->href();

			$papers[] = $paper;
		}
		$template->assign('Papers', $papers);
	}

}
