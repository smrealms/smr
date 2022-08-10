<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;
use Smr\Request;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$account = $session->getAccount();
		$player = $session->getPlayer();

		$timeUntilMaps = $player->getGame()->getStartTime() + TIME_MAP_BUY_WAIT - Epoch::time();
		if ($timeUntilMaps > 0) {
			create_error('You cannot buy maps for another ' . format_time($timeUntilMaps) . '!');
		}

		if ($account->getTotalSmrCredits() < CREDITS_PER_GAL_MAP) {
			create_error('You don\'t have enough SMR Credits. Donate to SMR to gain SMR Credits!');
		}

		//gal map buy
		if (isset($var['process'])) {
			$galaxyID = Request::getInt('gal_id');

			//get start sector
			$galaxy = SmrGalaxy::getGalaxy($player->getGameID(), $galaxyID);
			$low = $galaxy->getStartSector();
			//get end sector
			$high = $galaxy->getEndSector();

			// Have they already got this map? (Are there any unexplored sectors?
			$db = Database::getInstance();
			$dbResult = $db->read('SELECT 1 FROM player_visited_sector WHERE sector_id >= ' . $db->escapeNumber($low) . ' AND sector_id <= ' . $db->escapeNumber($high) . ' AND ' . $player->getSQL() . ' LIMIT 1');
			if (!$dbResult->hasRecord()) {
				create_error('You already have maps of this galaxy!');
			}

			$player->increaseHOF(1, ['Bar', 'Maps Bought'], HOF_PUBLIC);
			//take money
			$account->decreaseTotalSmrCredits(CREDITS_PER_GAL_MAP);
			//now give maps

			// delete all entries from the player_visited_sector/port table
			$db->write('DELETE FROM player_visited_sector WHERE sector_id >= ' . $db->escapeNumber($low) . ' AND sector_id <= ' . $db->escapeNumber($high) . ' AND ' . $player->getSQL());
			//start section

			// add port infos
			foreach ($galaxy->getPorts() as $port) {
				$port->addCachePort($player->getAccountID());
			}

			$container = Page::create('bar_main.php');
			$container->addVar('LocationID');
			$container['message'] = '<div class="center">Galaxy maps have been added. Enjoy!</div><br />';
			$container->go();
		} else {
			// This is a display page!
			$template->assign('PageTopic', 'Buy Galaxy Maps');
			Menu::bar();

			//find what gal they want
			$container = Page::create('bar_galmap_buy.php');
			$container->addVar('LocationID');
			$container['process'] = true;
			$template->assign('BuyHREF', $container->href());
		}
