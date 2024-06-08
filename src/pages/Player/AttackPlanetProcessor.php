<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\AbstractPlayer;
use Smr\Database;
use Smr\Epoch;
use Smr\Page\PlayerPageProcessor;
use Smr\Player;
use Smr\SectorLock;

class AttackPlanetProcessor extends PlayerPageProcessor {

	public function build(AbstractPlayer $player): never {
		$account = $player->getAccount();
		$ship = $player->getShip();

		if ($player->hasNewbieTurns()) {
			create_error('You are under newbie protection!');
		}
		if ($player->hasFederalProtection()) {
			create_error('You are under federal protection!');
		}
		if ($player->isLandedOnPlanet()) {
			create_error('You cannot attack planets whilst on a planet!');
		}
		if ($player->getTurns() < TURNS_TO_SHOOT_PLANET) {
			create_error('You do not have enough turns to attack this planet!');
		}
		if (!$ship->hasWeapons() && !$ship->hasCDs()) {
			create_error('What are you going to do? Insult it to death?');
		}
		if (!$player->canFight()) {
			create_error('You are not allowed to fight!');
		}

		$planet = $player->getSectorPlanet();
		if (!$planet->exists()) {
			create_error('This planet does not exist.');
		}
		if (!$planet->hasOwner()) {
			create_error('This planet is not claimed.');
		}

		$planetOwner = $planet->getOwner();

		if ($player->forceNAPAlliance($planetOwner)) {
			create_error('You have a planet NAP, you cannot attack this planet!');
		}

		$attackers = $player->getSector()->getFightingTradersAgainstPlanet($player, $planet);
		if (count($attackers) === 0) {
			create_error('No players in sector are able to attack this planet!');
		}

		// ********************************
		// *
		// * P l a n e t   a t t a c k
		// *
		// ********************************

		$results = ['Attackers' => ['TotalDamage' => 0]];

		// take the turns
		$player->takeTurns(TURNS_TO_SHOOT_PLANET);

		$planet->attackedBy($player, $attackers);

		//decloak all attackers
		foreach ($attackers as $attacker) {
			$attacker->getShip()->decloak();
		}

		$totalShieldDamage = 0;
		foreach ($attackers as $attacker) {
			$playerResults = $attacker->getShip()->shootPlanet($planet);
			$results['Attackers']['Traders'][$attacker->getAccountID()] = $playerResults;
			$results['Attackers']['TotalDamage'] += $playerResults['TotalDamage'];
			foreach ($playerResults['Weapons'] as $weapon) {
				if (isset($weapon['ActualDamage'])) { // Only set if the weapon hits
					$totalShieldDamage += $weapon['ActualDamage']['Shield'];
				}
			}
		}

		// Planet downgrades only occur on non-shield damage
		$downgradeDamage = $results['Attackers']['TotalDamage'] - $totalShieldDamage;
		$results['Attackers']['Downgrades'] = $planet->checkForDowngrade($downgradeDamage);

		$results['Planet'] = $planet->shootPlayers($attackers);

		$account->log(LOG_TYPE_PLANET_BUSTING, 'Player attacks planet, the planet does ' . $results['Planet']['TotalDamage'] . ', their team does ' . $results['Attackers']['TotalDamage'] . ' and downgrades: ' . var_export($results['Attackers']['Downgrades'], true), $planet->getSectorID());

		// Add this log to the `combat_logs` database table
		$db = Database::getInstance();
		$logId = $db->insertAutoIncrement('combat_logs', [
			'game_id' => $player->getGameID(),
			'type' => 'PLANET',
			'sector_id' => $planet->getSectorID(),
			'timestamp' => Epoch::time(),
			'attacker_id' => $player->getAccountID(),
			'attacker_alliance_id' => $player->getAllianceID(),
			'defender_id' => $planetOwner->getAccountID(),
			'defender_alliance_id' => $planetOwner->getAllianceID(),
			'result' => $db->escapeObject($results, true),
		]);

		if ($planet->isDestroyed()) {
			$db->update(
				'player',
				['land_on_planet' => 'FALSE'],
				[
					'sector_id' => $planet->getSectorID(),
					'game_id' => $player->getGameID(),
				],
			);
			$planet->removeOwner();
			$planet->removePassword();

			// Prepare message for planet owners
			$planetAttackMessage = 'The defenses of ' . $planet->getCombatName() . ' have been breached. The planet is lost! [combatlog=' . $logId . ']';
		} else {
			$planetAttackMessage = 'Reports from the surface of ' . $planet->getCombatName() . ' confirm that it is under <span class="red">attack</span>! [combatlog=' . $logId . ']';
		}

		// Send notification to planet owners
		if ($planetOwner->hasAlliance()) {
			foreach ($planetOwner->getAlliance()->getMemberIDs() as $allyAccountID) {
				Player::sendMessageFromPlanet($planet->getGameID(), $allyAccountID, $planetAttackMessage);
			}
		} else {
			Player::sendMessageFromPlanet($planet->getGameID(), $planetOwner->getAccountID(), $planetAttackMessage);
		}

		// Update sector messages for attackers
		foreach ($attackers as $attacker) {
			if (!$player->equals($attacker)) {
				$db->replace('sector_message', [
					'account_id' => $attacker->getAccountID(),
					'game_id' => $attacker->getGameID(),
					'message' => '[ATTACK_RESULTS]' . $logId,
				]);
			}
		}

		// If player died they are now in another sector, and thus locks need reset
		if ($player->isDead()) {
			saveAllAndReleaseLock(updateSession: false);
			// Grab the lock in the new sector to avoid reloading session
			SectorLock::getInstance()->acquireForPlayer($player);
		}

		// If they died on the shot they get to see the results
		$container = new AttackPlanet($planet->getSectorID(), $results, $player->isDead());
		$container->go();
	}

}
