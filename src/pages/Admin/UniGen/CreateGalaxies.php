<?php declare(strict_types=1);

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();

		$numGals = $session->getRequestVarInt('num_gals', 12);

		$game = SmrGame::getGame($var['game_id']);
		$template->assign('PageTopic', 'Create Galaxies : ' . $game->getDisplayName());
		$template->assign('GameEnabled', $game->isEnabled());

		// Link for updating the number of galaxies
		$container = Page::create('admin/unigen/universe_create_galaxies.php');
		$container->addVar('game_id');
		$template->assign('UpdateNumGalsHREF', $container->href());

		// Link for creating galaxies
		$container = Page::create('admin/unigen/universe_create_save_processing.php');
		$container['forward_to'] = 'admin/unigen/universe_create_sectors.php';
		$container->addVar('game_id');
		$container->addVar('num_gals');
		$submit = [
			'value' => 'Create Galaxies',
			'href' => $container->href(),
		];
		$template->assign('Submit', $submit);

		// Link for creating universe from SMR file
		$container = Page::create('admin/unigen/upload_smr_file_processing.php');
		$container->addVar('game_id');
		$template->assign('UploadSmrFileHREF', $container->href());

		//Galaxy Creation area
		$defaultNames = [0, 'Alskant', 'Creonti', 'Human', 'Ik\'Thorne', 'Nijarin', 'Salvene', 'Thevian', 'WQ Human', 'Omar', 'Salzik', 'Manton', 'Livstar', 'Teryllia', 'Doriath', 'Anconus', 'Valheru', 'Sardine', 'Clacher', 'Tangeria'];
		$template->assign('NumGals', $numGals);

		$galaxies = [];
		for ($i = 1; $i <= $numGals; ++$i) {
			$isRacial = $i <= 8;
			$galaxies[$i] = [
				'Name' => $defaultNames[$i] ?? 'Unknown',
				'Width' => 10,
				'Height' => 10,
				'Type' => $isRacial ? SmrGalaxy::TYPE_RACIAL : SmrGalaxy::TYPE_NEUTRAL,
				'ForceMaxHours' => $isRacial ? 12 : 60,
			];
		}
		$template->assign('Galaxies', $galaxies);
