<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use AbstractSmrPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;
use SmrAlliance;

class AllianceGovernanceProcessor extends PlayerPageProcessor {

	public function __construct(
		private readonly int $allianceID
	) {}

	public function build(AbstractSmrPlayer $player): never {
		$alliance_id = $this->allianceID;

		if (Request::has('description')) {
			$description = Request::get('description');
		}
		if (Request::has('discord_server')) {
			$discordServer = Request::get('discord_server');
		}
		if (Request::has('discord_channel')) {
			$discordChannel = Request::get('discord_channel');
		}
		if (Request::has('irc')) {
			$irc = Request::get('irc');
		}
		if (Request::has('mod')) {
			$mod = Request::get('mod');
		}
		if (Request::has('url')) {
			$url = Request::get('url');
		}

		// Prevent XSS attacks
		if (isset($url) && preg_match('/"/', $url)) {
			create_error('You cannot use a " in the image link!');
		}

		$alliance = SmrAlliance::getAlliance($alliance_id, $player->getGameID());
		if (Request::has('recruit_type')) {
			$recruitType = Request::get('recruit_type');
			$password = Request::get('password', '');
			$alliance->setRecruitType($recruitType, $password);
		}
		if (isset($description)) {
			$alliance->setAllianceDescription($description, $player);
		}
		if (isset($discordServer)) {
			$alliance->setDiscordServer($discordServer);
		}
		if (isset($discordChannel)) {
			if (empty($discordChannel)) {
				$alliance->setDiscordChannel(null);
			} else {
				// no duplicates in a given game
				$db = Database::getInstance();
				$dbResult = $db->read('SELECT 1 FROM alliance WHERE discord_channel =' . $db->escapeString($discordChannel) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND alliance_id != ' . $db->escapeNumber($alliance->getAllianceID()) . ' LIMIT 1');
				if ($dbResult->hasRecord()) {
					create_error('Another alliance is already using that Discord Channel ID!');
				}

				$alliance->setDiscordChannel($discordChannel);
			}
		}
		if (isset($irc)) {
			$alliance->setIrcChannel($irc);
		}
		if (isset($mod)) {
			$alliance->setMotD($mod);
		}
		if (isset($url)) {
			$alliance->setImageURL($url);
		}

		$alliance->update();
		$container = new AllianceRoster($alliance_id);
		$container->go();
	}

}