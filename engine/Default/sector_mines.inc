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
	if ($player->hasNewbieTurns()) {
		$turns = $sectorForces[$mine_owner_id]->getBumpTurnCost($ship);
		$player->takeTurns($turns, $turns);
		$container = create_container('skeleton.php', 'current_sector.php');
		$container['msg'] = 'You have just flown past a sprinkle of mines.<br />Because of your newbie status you have been spared from the harsh reality of the forces.<br />It has cost you ' . $turns . ' ' . pluralise('turn', $turns) . ' to navigate the minefield safely.';
		forward($container);
	} else {
		$container = create_container('forces_attack_processing.php');
		$container['action'] = 'bump';
		$container['owner_id'] = $mine_owner_id;
		forward($container);
	}
}
