<?php declare(strict_types=1);

use Smr\Pages\Player\AttackForcesProcessor;
use Smr\Pages\Player\CurrentSector;
use Smr\ShipClass;

function hit_sector_mines(AbstractSmrPlayer $player): void {

	// Get sector forces sorted by decreasing mines (largest mine stacks first)
	$sectorForces = $player->getSector()->getForces();
	uasort($sectorForces, fn($a, $b) => $b->getMines() <=> $a->getMines());

	$forcesHit = false;
	foreach ($sectorForces as $forces) {
		if ($forces->hasMines() && !$player->forceNAPAlliance($forces->getOwner())) {
			$forcesHit = $forces;
			break;
		}
	}

	if ($forcesHit === false) {
		return;
	}

	$ship = $player->getShip();
	if ($player->hasNewbieTurns() || $ship->getClass() === ShipClass::Scout) {
		$turns = $forcesHit->getBumpTurnCost($ship);
		$player->takeTurns($turns, $turns);
		if ($player->hasNewbieTurns()) {
			$flavor = 'Because of your newbie status you have been spared from the harsh reality of the forces';
		} else {
			$flavor = 'Your ship was sufficiently agile to evade them';
		}
		$msg = 'You have just flown past a sprinkle of mines.<br />' . $flavor . ',<br />but it has cost you <span class="red">' . pluralise($turns, 'turn') . '</span> to navigate the minefield safely.';
		$container = new CurrentSector(message: $msg);
		$container->go();
	} else {
		$container = new AttackForcesProcessor($forcesHit->getOwnerID(), bump: true);
		$container->go();
	}

}
