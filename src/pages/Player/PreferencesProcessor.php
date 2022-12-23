<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Exception;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;
use Smr\SectorLock;

class PreferencesProcessor extends PlayerPageProcessor {

	public function build(AbstractSmrPlayer $player): never {
		$db = Database::getInstance();
		$account = $player->getAccount();

		$action = Request::get('action');

		if ($action == 'Change Kamikaze Setting') {
			$player->setCombatDronesKamikazeOnMines(Request::get('kamikaze') == 'Yes');
			$message = '<span class="green">SUCCESS: </span>You have changed your combat drones options.';

		} elseif ($action == 'Change Message Setting') {
			$player->setForceDropMessages(Request::get('forceDropMessages') == 'Yes');
			$message = '<span class="green">SUCCESS: </span>You have changed your message options.';

		} elseif ($action == 'change_name') {
			$old_name = $player->getDisplayName();
			$player_name = Request::get('PlayerName');

			// Check that the player can afford the name change
			$smrCreditCost = $player->isNameChanged() ? CREDITS_PER_NAME_CHANGE : 0;
			if ($account->getTotalSmrCredits() < $smrCreditCost) {
				create_error('You do not have enough credits to change your name.');
			}

			$player->changePlayerNameByPlayer($player_name);
			$account->decreaseTotalSmrCredits($smrCreditCost);

			$news = 'Please be advised that ' . $old_name . ' has changed their name to ' . $player->getBBLink();
			$db->insert('news', [
				'time' => $db->escapeNumber(Epoch::time()),
				'news_message' => $db->escapeString($news),
				'game_id' => $db->escapeNumber($player->getGameID()),
				'type' => $db->escapeString('admin'),
				'killer_id' => $db->escapeNumber($player->getAccountID()),
			]);
			$message = '<span class="green">SUCCESS: </span>You have changed your player name.';

		} elseif ($action == 'change_race') {
			if (!$player->canChangeRace()) {
				throw new Exception('Player is not allowed to change their race!');
			}
			$newRaceID = Request::getInt('race_id');
			if (!in_array($newRaceID, $player->getGame()->getPlayableRaceIDs())) {
				throw new Exception('Invalid race ID selected!');
			}
			if ($newRaceID == $player->getRaceID()) {
				create_error('You are already the ' . $player->getRaceName() . ' race!');
			}

			// Modify the player
			$oldRaceID = $player->getRaceID();
			$player->setRaceID($newRaceID);
			$player->setLandedOnPlanet(false);
			$player->getShip()->getPod($player->hasNewbieStatus()); // just to reset
			$player->getShip()->giveStarterShip();
			$player->setNewbieTurns(max(1, $player->getNewbieTurns()));
			$player->setExperience(0);
			$player->setRaceChanged(true);

			// Reset relations
			$db->write('DELETE FROM player_has_relation WHERE ' . $player->getSQL());
			$player->giveStartingRelations();

			// Move them to their new race HQ and reset sector lock
			$player->setSectorID($player->getHome());
			$player->getSector()->markVisited($player);
			$player->update();
			$lock = SectorLock::getInstance();
			$lock->release();
			$lock->acquireForPlayer($player);

			$news = 'Please be advised that ' . $player->getBBLink() . ' has changed their race from [race=' . $oldRaceID . '] to [race=' . $player->getRaceID() . ']';
			$db->insert('news', [
				'time' => $db->escapeNumber(Epoch::time()),
				'news_message' => $db->escapeString($news),
				'game_id' => $db->escapeNumber($player->getGameID()),
				'type' => $db->escapeString('admin'),
				'killer_id' => $db->escapeNumber($player->getAccountID()),
			]);
			$message = '<span class="green">SUCCESS: </span>You have changed your player race.';
		} else {
			throw new Exception('Invalid action: ' . $action);
		}

		$container = new CurrentSector(message: $message);
		$container->go();
	}

}
