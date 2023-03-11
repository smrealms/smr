<?php declare(strict_types=1);

namespace Smr\Pages\Player\Planet;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Page\PlayerPageProcessor;
use Smr\Request;

class OwnershipProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		if (!$player->isLandedOnPlanet()) {
			create_error('You are not on a planet!');
		}
		// get a planet from the sector where the player is in
		$planet = $player->getSectorPlanet();
		$action = Request::get('action');

		if ($action == 'Take Ownership') {
			if ($planet->hasOwner() && $planet->getPassword() != Request::get('password')) {
				create_error('You entered an incorrect password for this planet!');
			}

			// delete all previous ownerships
			$db = Database::getInstance();
			$db->update(
				'planet',
				[
					'owner_id' => 0,
					'password' => null,
				],
				[
					'owner_id' => $player->getAccountID(),
					'game_id' => $player->getGameID(),
				],
			);

			// set ownership
			$planet->setOwnerID($player->getAccountID());
			$planet->removePassword();
			$player->log(LOG_TYPE_PLANETS, 'Player takes ownership of planet.');
		} elseif ($action == 'Rename') {
			$name = Request::get('name');
			if (empty($name)) {
				create_error('You cannot leave your planet nameless!');
			}
			// rename planet
			$planet->setName($name);
			$player->log(LOG_TYPE_PLANETS, 'Player renames planet to ' . $name . '.');

		} elseif ($action == 'Set Password') {
			// set password
			$password = Request::get('password');
			$planet->setPassword($password);
			$player->log(LOG_TYPE_PLANETS, 'Player sets planet password to ' . $password);
		}

		(new Ownership())->go();
	}

}
