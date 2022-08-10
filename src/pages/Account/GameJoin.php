<?php declare(strict_types=1);

use Smr\Database;
use Smr\Epoch;
use Smr\Race;
use Smr\RaceDetails;

		$template = Smr\Template::getInstance();
		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$account = $session->getAccount();

		$game = SmrGame::getGame($var['game_id']);

		// Don't allow vets to join Newbie games
		if ($game->isGameType(SmrGame::GAME_TYPE_NEWBIE) && $account->isVeteran()) {
			create_error('Veteran players are not allowed to join Newbie games!');
		}

		// do we need credits for this game?
		if ($game->getCreditsNeeded() > 0) {
			// do we have enough
			if ($account->getTotalSmrCredits() < $game->getCreditsNeeded()) {
				create_error('Sorry you do not have enough SMR Credits to play this game.<br />To get SMR credits you need to donate to SMR.');
			}
		}

		// is the game already full?
		if ($game->getTotalPlayers() >= $game->getMaxPlayers()) {
			create_error('The maximum number of players in that game is reached!');
		}

		if ($game->hasEnded()) {
			create_error('You want to join a game that is already over?');
		}

		$races = [];
		$db = Database::getInstance();
		foreach ($game->getPlayableRaceIDs() as $raceID) {
			// get number of traders in game
			$dbResult = $db->read('SELECT count(*) as number_of_race FROM player WHERE race_id = ' . $db->escapeNumber($raceID) . ' AND game_id = ' . $db->escapeNumber($var['game_id']));

			$races[$raceID] = [
				'Name' => Race::getName($raceID),
				'ShortDescription' => RaceDetails::getShortDescription($raceID),
				'LongDescription' => RaceDetails::getLongDescription($raceID),
				'NumberOfPlayers' => $dbResult->record()->getInt('number_of_race'),
				'Selected' => false,
			];
		}
		if (empty($races)) {
			create_error('This game has no races assigned yet!');
		}

		$template->assign('PageTopic', 'Join Game: ' . $game->getDisplayName());
		$template->assign('Game', $game);

		if (Epoch::time() >= $game->getJoinTime()) {
			$container = Page::create('game_join_processing.php');
			$container->addVar('game_id');
			$template->assign('JoinGameFormHref', $container->href());
		}

		// Pick an initial race to display (prefer *not* Alskant)
		do {
			$raceKey = array_rand($races);
		} while ($raceKey == RACE_ALSKANT && count($races) > 1);
		$races[$raceKey]['Selected'] = true;
		$template->assign('SelectedRaceID', $raceKey);
		$template->assign('Races', $races);

		// This instructs EndingJavascript.inc.php to include the javascript to display
		// the Plotly.js radar charts.
		$template->assign('AddRaceRadarChartJS', true);
