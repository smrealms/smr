<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AllianceGovernanceProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $allianceID
	) {}

	public function build(AbstractPlayer $player): never {
		$alliance_id = $this->allianceID;
		$alliance = Alliance::getAlliance($alliance_id, $player->getGameID());

		if (Request::has('description')) {
			$description = Request::get('description');
			$alliance->setAllianceDescription($description, $player);
		}

		if (Request::has('discord_server')) {
			$discordServer = Request::get('discord_server');
			$alliance->setDiscordServer($discordServer);
		}

		if (Request::has('irc')) {
			$irc = Request::get('irc');
			$alliance->setIrcChannel($irc);
		}

		if (Request::has('mod')) {
			$mod = Request::get('mod');
			$alliance->setMotD($mod);
		}

		if (Request::has('url')) {
			$url = filter_var(Request::get('url'), FILTER_VALIDATE_URL);
			if ($url === false) {
				create_error('You must enter a valid URL for the image link!');
			}
			$alliance->setImageURL($url);
		}

		if (Request::has('recruit_type')) {
			$recruitType = Request::get('recruit_type');
			$password = Request::get('password', '');
			$alliance->setRecruitType($recruitType, $password);
		}

		if (Request::has('discord_channel')) {
			$discordChannel = Request::get('discord_channel');
			if (empty($discordChannel)) {
				$alliance->setDiscordChannel(null);
			} else {
				// no duplicates in a given game
				$db = Database::getInstance();
				$dbResult = $db->read('SELECT 1 FROM alliance WHERE discord_channel = :discord_channel AND game_id = :game_id AND alliance_id != :alliance_id LIMIT 1', [
					'discord_channel' => $db->escapeString($discordChannel),
					'game_id' => $db->escapeNumber($alliance->getGameID()),
					'alliance_id' => $db->escapeNumber($alliance->getAllianceID()),
				]);
				if ($dbResult->hasRecord()) {
					create_error('Another alliance is already using that Discord Channel ID!');
				}

				$alliance->setDiscordChannel($discordChannel);
			}
		}

		$alliance->update();
		$container = new AllianceRoster($alliance_id);
		$container->go();
	}

}
