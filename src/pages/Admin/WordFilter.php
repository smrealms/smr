<?php declare(strict_types=1);

use Smr\Database;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();

		$template->assign('PageTopic', 'Word Filter');

		if (isset($var['msg'])) {
			$template->assign('Message', $var['msg']);
		}

		$db = Database::getInstance();
		$dbResult = $db->read('SELECT * FROM word_filter');
		if ($dbResult->hasRecord()) {
			$container = Page::create('admin/word_filter_del.php');
			$template->assign('DelHREF', $container->href());

			$filteredWords = [];
			foreach ($dbResult->records() as $dbRecord) {
				$filteredWords[] = $dbRecord->getRow();
			}
			$template->assign('FilteredWords', $filteredWords);
		}

		$container = Page::create('admin/word_filter_add.php');
		$template->assign('AddHREF', $container->href());
