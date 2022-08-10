<?php declare(strict_types=1);

use Smr\Database;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$player = $session->getPlayer();

		if (!$player->isGPEditor()) {
			throw new Exception('Only the GP Editor is allowed to view this page!');
		}

		$template->assign('PageTopic', 'Galactic Post');
		Menu::galacticPost();

		$db = Database::getInstance();

		$container = Page::create('galactic_post_view_article.php');
		$template->assign('ViewArticlesHREF', $container->href());

		$container = Page::create('galactic_post_make_paper.php');
		$template->assign('MakePaperHREF', $container->href());

		$dbResult = $db->read('SELECT * FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
		$papers = [];
		foreach ($dbResult->records() as $dbRecord) {
			$paper_name = $dbRecord->getString('title');
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
				$container = Page::create('galactic_post_make_current.php');
				$container['id'] = $paper_id;
				$paper['PublishHREF'] = $container->href();
			}

			$container = Page::create('galactic_post_delete_confirm.php');
			$container['paper'] = 'yes';
			$container['id'] = $paper_id;
			$paper['DeleteHREF'] = $container->href();

			$container = Page::create('galactic_post_paper_edit.php');
			$container['id'] = $paper_id;
			$paper['EditHREF'] = $container->href();

			$papers[] = $paper;
		}
		$template->assign('Papers', $papers);
