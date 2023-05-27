<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Exception;
use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Game;
use Smr\Page\PlayerPageProcessor;
use Smr\Page\ReusableTrait;
use Smr\Player;

class AllianceDraftMemberProcessor extends PlayerPageProcessor {

	use ReusableTrait;

	public function __construct(
		private readonly int $pickedAccountID,
	) {}

	public function build(AbstractPlayer $player): never {
		if (!$player->getGame()->isGameType(Game::GAME_TYPE_DRAFT)) {
			throw new Exception('This page is only allowed in Draft games!');
		}

		$pickedAccountID = $this->pickedAccountID;

		require_once(LIB . 'Default/alliance_pick.inc.php');
		$teams = get_draft_teams($player->getGameID());
		if (!$teams[$player->getAccountID()]['CanPick']) {
			create_error('You have to wait for others to pick first.');
		}
		$pickedPlayer = Player::getPlayer($pickedAccountID, $player->getGameID());

		if ($pickedPlayer->isDraftLeader()) {
			create_error('You cannot pick another leader.');
		}

		if ($pickedPlayer->hasAlliance()) {
			if ($pickedPlayer->getAlliance()->isNHA()) {
				$pickedPlayer->leaveAlliance();
			} else {
				create_error('Picked player already has an alliance.');
			}
		}

		// assign the player to the current alliance
		$pickedPlayer->joinAlliance($player->getAllianceID());

		// move the player to the alliance home sector if not using traditional HQ's
		if ($pickedPlayer->getSectorID() === 1) {
			$pickedPlayer->setSectorID($pickedPlayer->getHome());
			$pickedPlayer->getSector()->markVisited($pickedPlayer);
		}

		$pickedPlayer->update();

		// Update the draft history
		$db = Database::getInstance();
		$db->insert('draft_history', [
			'game_id' => $player->getGameID(),
			'leader_account_id' => $player->getAccountID(),
			'picked_account_id' => $pickedPlayer->getAccountID(),
			'time' => Epoch::time(),
		]);

		(new AllianceDraftMember())->go();
	}

}
