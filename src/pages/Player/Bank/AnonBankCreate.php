<?php declare(strict_types=1);

		$template = Smr\Template::getInstance();

		$template->assign('PageTopic', 'Create Anonymous Account');
		Menu::bank();

		$container = Page::create('bank_anon_create_processing.php');
		$template->assign('CreateHREF', $container->href());
