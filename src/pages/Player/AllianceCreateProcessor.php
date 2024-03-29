<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Exception;
use Smr\AbstractPlayer;
use Smr\Alliance;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class AllianceCreateProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		if ($player->getAllianceJoinable() > Epoch::time()) {
			create_error('You cannot create an alliance for another ' . format_time($player->getAllianceJoinable() - Epoch::time()) . '.');
		}

		$name = Request::get('name');
		if ($name === '') {
			throw new Exception('No alliance name entered');
		}

		// disallow certain ascii chars
		if (!ctype_print($name)) {
			create_error('The alliance name contains invalid characters!');
		}

		$password = Request::get('password', '');
		$description = Request::get('description');
		$recruitType = Request::get('recruit_type');
		$perms = Request::get('Perms');

		$name2 = strtolower($name);
		if ($name2 === 'none' || $name2 === '(none)' || $name2 === '( none )' || $name2 === 'no alliance') {
			create_error('That is not a valid alliance name!');
		}
		$filteredName = word_filter($name);
		if ($name !== $filteredName) {
			create_error('The alliance name contains one or more filtered words, please reconsider the name.');
		}

		// create the alliance
		$alliance = Alliance::createAlliance($player->getGameID(), $name);
		$alliance->setRecruitType($recruitType, $password);
		$alliance->setAllianceDescription($description, $player);
		$alliance->setLeaderID($player->getAccountID());
		$alliance->createDefaultRoles($perms);
		$alliance->update();

		// assign the player to the created alliance
		$player->joinAlliance($alliance->getAllianceID());
		$player->update();

		(new AllianceRoster())->go();
	}

}
