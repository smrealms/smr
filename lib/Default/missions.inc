<?php declare(strict_types=1);

const MISSION_ACTIONS = array(
	'LeaveSector',
	'EnterSector',
	'WalkSector',
	'JoinAlliance',
	'LeaveAlliance',
	'DisbandAlliance',
	'KickPlayer',
	'PlayerKicked',
	'BuyDrink'
);

//REQUIREMENTS
//if you use an array as a requirement and the requirement name represents an array, it will check every value and all must pass
//if you use an array as a requirement and the requirement name is not an array, only one of the checks must pass
//ie 'Completed Missions' => array(2,3) means the player must have completed BOTH missions
//ie 'Ship ID' => array(1,2) means the player must be in EITHER ship id 1 or 2
//STEPS
/*key types:
*'KillName' - kill 'Detail' Player/NPC
*'KillNPCs' - kill 'Detail' NPCs
*'KillPlayers' - kill 'Detail' Players
*'KillSpawn' - Spawn 'Detail' Type NPC and kill it, DB field Progress with then be NPC_ID, also requires a 'Level' element, use -1 for normal
'Trade' -
^'Visit' - Examine 'Detail' location
*'DrinkAmount' - Buy 'Detail' drinks at a bar
*'Drink' - Buy 'Detail' drink name at a bar
*'Move' - Move 'Detail' sectors anywhere
*'MoveSector' - Move to 'Detail' sector
*'MoveRacial' - Move to galaxy containing 'Detail' race HQ (use racial id)
*'MoveGal' - Move to 'Detail' galaxy
'ClearNPC' - Clear 'Detail' stacks of NPC forces in sector (use MoveSector) command to tell them which sector, also stored as mission_sector in DB
*'StartPortRaid' - start raiding 'detail' ports
*'RaidPort' - raid 'detail' ports
'Bring' - bring 'detail' to starting sector

Replacements:
<Race> - Current race name
<Starting Sector> - Sector where mission was accepted
<Sector> - Random sector for mission.

* = implemented
^ = partial implementaion
	Visit - done for 'Bar'
*/

// NOTE: Array keys are the mission ID and should not be changed!
const MISSIONS = array(
	0 => array(
		'Name' => 'Drunk Guy',
		'Offerer' => 'Drunk',
		'Time Limit' => 0,
		'HasX' => array(
			'Type' => 'Locations',
			'X' => 'Bar'
		),
		'Steps' => array(
			array(
				'Step' => 'EnterSector',
				'PickSector' => array(
					'Type' => 'Locations',
					'X' => RACE_SALVENE + LOCATION_GROUP_RACIAL_HQS
				),
				'Detail' => array(
					'SectorID' => '<Sector>'
				),
				'Text' => '*Hiccup* Hey! I need you to...*Hiccup* do me a favor. All the Salvene Swamp Water in this bar is awful! Go to the Sal...*Hiccup*...the Salvene HQ, they\'ll know a good bar.',
				'Task' => 'Go to the Salvene HQ at [sector=<Sector>]'
			),
			array(
				'Step' => 'EnterSector',
				'PickSector' => array(
					'Type' => 'Locations',
					'X' => 'Bar'
				),
				'Detail' => array(
					'SectorID' => '<Sector>'
				),
				'Text' => 'Here we are! The Salvene HQ! You ask around a bit and find that the bar in [sector=<Sector>] does the best Salvene Swamp Water around!',
				'Task' => 'Go to the bar at [sector=<Sector>] and buy a Salvene Swamp Water from the bartender. This may take many tries.'
			),
			array(
				'Step' => 'BuyDrink',
				'Detail' => array(
					'SectorID' => '<Sector>',
					'Drink' => 'Salvene Swamp Water'
				),
				'Text' => 'Here we are! Now let\'s get this Salvene Swamp Water.',
				'Task' => 'Go to the bar at [sector=<Sector>] and buy a Salvene Swamp Water from the bartender. This may take many tries.'
			),
			array(
				'Step' => 'EnterSector',
				'Detail' => array(
					'SectorID' => '<Starting Sector>'
				),
				'Text' => 'Finally! A true Salvene Swamp Water, let\'s return to that drunk!',
				'Task' => 'Return to [sector=<Starting Sector>] to claim your reward.'
			),
			array(
				'Step' => 'Claim',
				'Rewards' => array(
					'Credits' => 500000,
					'Experience' => 1000,
					'Text' => '*Hiccup* For your...service *Hiccup* to me, take these *Hiccup* 500,000 credits and 1,000 experience *Hiccup*!'
				),
				'Detail' => array(
					'SectorID' => '<Starting Sector>'
				),
				'Text' => 'You hand the Salvene Swamp water to the drunk!'
			)
		)
	)
);

/**
 * Callback for array_walk_recursive in SmrPlayer::rebuildMission.
 * Searches for placeholders in template and replaces them with values
 * derived from the supplied data.
 */
function replaceMissionTemplate(&$template, $key, array $data) : void {
	if (!is_string($template)) {
		return;
	}
	$search = ['<Race>', '<Sector>', '<Starting Sector>'];
	$replace = [$data['player']->getRaceID(), $data['mission']['Sector'], $data['mission']['Starting Sector']];
	$template = str_replace($search, $replace, $template);
}

function checkMissionRequirements(array $values, array $requirements) : bool {
	foreach ($requirements as $reqName => $reqValue) {
		if ($values[$reqName] != $reqValue) {
			return false;
		}
	}
	return true;
}
