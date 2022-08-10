<?php declare(strict_types=1);

		$template = Smr\Template::getInstance();

		$template->assign('PageTopic', 'Create Alliance');

		$container = Page::create('alliance_create_processing.php');
		$template->assign('CreateHREF', $container->href());
