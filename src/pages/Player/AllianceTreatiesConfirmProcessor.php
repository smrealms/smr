<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use SmrAlliance;
use SmrPlayer;

class AllianceTreatiesConfirmProcessor extends PlayerPageProcessor {

	/**
	 * @param array<string, bool> $terms
	 */
	public function __construct(
		private readonly int $otherAllianceID,
		private readonly array $terms
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$alliance1 = $player->getAlliance();
		$alliance2 = SmrAlliance::getAlliance($this->otherAllianceID, $player->getGameID());

		$alliance_id_1 = $alliance1->getAllianceID();
		$alliance_id_2 = $alliance2->getAllianceID();

		$db = Database::getInstance();
		$db->insert('alliance_treaties', [
			'alliance_id_1' => $db->escapeNumber($alliance_id_1),
			'alliance_id_2' => $db->escapeNumber($alliance_id_2),
			'game_id' => $db->escapeNumber($player->getGameID()),
			'trader_assist' => $db->escapeBoolean($this->terms['trader_assist']),
			'trader_defend' => $db->escapeBoolean($this->terms['trader_defend']),
			'trader_nap' => $db->escapeBoolean($this->terms['trader_nap']),
			'raid_assist' => $db->escapeBoolean($this->terms['raid_assist']),
			'planet_land' => $db->escapeBoolean($this->terms['planet_land']),
			'planet_nap' => $db->escapeBoolean($this->terms['planet_nap']),
			'forces_nap' => $db->escapeBoolean($this->terms['forces_nap']),
			'aa_access' => $db->escapeBoolean($this->terms['aa_access']),
			'mb_read' => $db->escapeBoolean($this->terms['mb_read']),
			'mb_write' => $db->escapeBoolean($this->terms['mb_write']),
			'mod_read' => $db->escapeBoolean($this->terms['mod_read']),
			'official' => $db->escapeBoolean(false),
		]);

		//send a message to the leader letting them know the offer is waiting.
		$leader2 = $alliance2->getLeaderID();
		$message = 'An ambassador from ' . $alliance1->getAllianceBBLink() . ' has arrived with a treaty offer.';

		SmrPlayer::sendMessageFromAllianceAmbassador($player->getGameID(), $leader2, $message);

		$container = new AllianceTreaties('The treaty offer has been sent.');
		$container->go();
	}

}
