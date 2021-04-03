<?php declare(strict_types=1);

$sectorForces = $sector->getForces();
Sorter::sortByNumMethod($sectorForces, 'getMines', true);
$mine_owner_id = false;
foreach ($sectorForces as $forces) {
	if (!$mine_owner_id && $forces->hasMines() && !$player->forceNAPAlliance($forces->getOwner())) {
		$mine_owner_id = $forces->getOwnerID();
		break;
	}
}

if ($mine_owner_id) {
	if ($player->hasNewbieTurns() || $ship->getShipClassID() === SmrShip::SHIP_CLASS_SCOUT) {
		$turns = $sectorForces[$mine_owner_id]->getBumpTurnCost($ship);
		$player->takeTurns($turns, $turns);
		$container = Page::create('skeleton.php', 'current_sector.php');
		if ($player->hasNewbieTurns()) {
			$flavor = 'Because of your newbie status you have been spared from the harsh reality of the forces';
		} else {
			$flavor = 'Your ship was sufficiently agile to evade them';
		}
		$container['msg'] = 'You have just flown past a sprinkle of mines.<br />' . $flavor . ',<br />but it has cost you <span class="red">' . $turns . ' ' . pluralise('turn', $turns) . '</span> to navigate the minefield safely.';
		$container->go();
	} else {
		$container = Page::create('forces_attack_processing.php');
		$container['action'] = 'bump';
		$container['owner_id'] = $mine_owner_id;
		$container->go();
	}
}
