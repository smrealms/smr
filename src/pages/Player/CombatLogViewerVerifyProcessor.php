<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;

// Verify that the player is permitted to view the requested combat log
// Qualifications:
//  * Log must be from the current game
//  * Attacker or defender is the player OR in the player's alliance
class CombatLogViewerVerifyProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $logID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$db = Database::getInstance();

		$query = 'SELECT 1 FROM combat_logs WHERE log_id=' . $db->escapeNumber($this->logID) . ' AND game_id=' . $db->escapeNumber($player->getGameID()) . ' AND ';
		if ($player->hasAlliance()) {
			$query .= '(attacker_alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ' OR defender_alliance_id=' . $db->escapeNumber($player->getAllianceID()) . ')';
		} else {
			$query .= '(attacker_id=' . $db->escapeNumber($player->getAccountID()) . ' OR defender_id=' . $db->escapeNumber($player->getAccountID()) . ')';
		}
		$dbResult = $db->read($query . ' LIMIT 1');

		// Error if qualifications are not met
		if (!$dbResult->hasRecord()) {
			create_error('You do not have permission to view this combat log!');
		}

		// Player has permission, so go to the display page!
		$container = new CombatLogViewer([$this->logID]);
		$container->go();
	}

}
