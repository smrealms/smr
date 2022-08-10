<?php declare(strict_types=1);

		require_once(LIB . 'Default/planet.inc.php');
		planet_common();

		$planet = Smr\Session::getInstance()->getPlayer()->getSectorPlanet();

		$template = Smr\Template::getInstance();
		$template->assign('BondDuration', format_time($planet->getBondTime()));
		$template->assign('ReturnHREF', $planet->getFinancesHREF());
