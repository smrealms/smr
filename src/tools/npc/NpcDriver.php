<?php declare(strict_types=1);

namespace Smr\Npc;

use Smr\Database;
use Smr\Epoch;
use Smr\Exceptions\SectorLockTimeout;
use Smr\Npc\Exceptions\FinalAction;
use Smr\Npc\Exceptions\ForwardAction;
use Smr\Page\Page;
use Smr\Player;
use Smr\SectorLock;
use Smr\Session;

class NpcDriver {

	/** @var list<int> */
	private array $availableNpcPlayerIDs;

	public function __construct() {
		$db = Database::getInstance();
		// Make sure to select NPCs from active games only.
		// Lacking a convenient way to get up-to-date turns, order NPCs
		// by how recently they have taken an action.
		$result = $db->read('SELECT npc_players.player_id
			FROM npc_players
			JOIN game USING(game_id)
			JOIN player USING(player_id)
			WHERE active=\'TRUE\' AND working=\'FALSE\'
				AND start_time < :now AND end_time > :now
			ORDER BY last_turn_update ASC', [
			'now' => $db->escapeNumber(Epoch::time()),
		]);
		$this->availableNpcPlayerIDs = [];
		foreach ($result->records() as $record) {
			$this->availableNpcPlayerIDs[] = $record->getInt('player_id');
		}
	}

	/**
	 * @return bool Whether the selected NPC performed any actions
	 */
	public function run(): bool {
		// Initialize session with a fake blank page
		Session::getInstance()->setCurrentVar(new Page());

		// Load the first available NPC.
		$playerID = $this->selectNpcPlayer();
		try {
			$actor = new NpcActor($playerID);
		} catch (FinalAction) {
			// Startup conditions not satisfied, try another NPC.
			return false;
		}

		// Loop over actions for this NPC.
		while (true) {
			try {
				$actor->processNextAction();
			} catch (SectorLockTimeout $e) {
				// On timeout, skip this action and select a new action. This is
				// comparable to refreshing the page rather than aborting uncleanly.
				debug('Caught ' . $e::class . ' exception. Will try another action.');
				SectorLock::resetInstance();
			} catch (ForwardAction) {
				// The processor forwarded to the next action.
			} catch (FinalAction) {
				$actor->shutdown();
				// We will switch to a new NPC if we haven't taken any actions yet
				return $actor->hasTakenActions();
			} finally {
				// Save any changes made during this action, then discard caches
				// because other players may have changed their underlying data.
				saveAllAndReleaseLock(updateSession: false);
				clearCaches();
			}

			// Small sleep between actions
			sleepNPC();
		}
	}

	private function selectNpcPlayer(): int {
		releaseNPC();
		debug('Choosing new NPC');

		// Pop an NPC off the top of the stack to activate
		$playerID = array_shift($this->availableNpcPlayerIDs);
		if ($playerID === null) {
			debug('No free NPCs. Have you created any yet?');
			exitNPC();
		}
		$npcPlayer = Player::getPlayer($playerID);

		// Update session info for this chosen NPC
		$session = Session::getInstance();
		$session->setAccount($npcPlayer->getAccount());
		$session->updateGame($npcPlayer->getGameID());

		$db = Database::getInstance();
		$db->update(
			'npc_players',
			['working' => $db->escapeBoolean(true)],
			$npcPlayer->SQLID,
		);
		debug('Chosen NPC:', [...$npcPlayer->SQLID, 'name' => $npcPlayer->getPlayerName()]);

		return $playerID;
	}

}
