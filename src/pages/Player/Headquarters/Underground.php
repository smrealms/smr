<?php declare(strict_types=1);

use Smr\Bounties;
use Smr\BountyType;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$player = $session->getPlayer();

		if ($player->hasGoodAlignment()) {
			create_error('You are not allowed to come in here!');
		}

		if (!$player->getSector()->hasLocation($var['LocationID'])) {
			create_error('That location does not exist in this sector');
		}
		$location = SmrLocation::getLocation($player->getGameID(), $var['LocationID']);
		if (!$location->isUG()) {
			create_error('There is no underground here.');
		}

		$template->assign('PageTopic', $location->getName());

		Menu::headquarters($var['LocationID']);

		$template->assign('AllBounties', Bounties::getMostWanted(BountyType::UG));
		$template->assign('MyBounties', $player->getClaimableBounties(BountyType::UG));

		if ($player->hasNeutralAlignment()) {
			$container = Page::create('government_processing.php');
			$container->addVar('LocationID');
			$template->assign('JoinHREF', $container->href());
		}
