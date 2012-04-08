<?php

$MISSION_ACTIONS = array('LeaveSector','EnterSector','WalkSector','JoinAlliance','LeaveAlliance','DisbandAlliance','KickPlayer','PlayerKicked');

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
$MISSIONS = array(
	array(
		'Name' => 'Drunk Guy',
		'Offerer' => 'Drunk',
		'Time Limit' => 0,
		'Location' => 'Bar',
		'Rewards' => array(
			'Credits' => 500000,
			'Experience' => 1000,
			'Text' => '*Hiccup* For your...service *Hiccup* to me, take these *Hiccup* 500,000 credits and 1000 experience *Hiccup*!'
		),
		'Steps' => array(
			array(
				'Step' => 'MoveRacial',
				'Detail' => 60, //Salvene
				'Text' => '*Hiccup* Hey! I need you to...*Hiccup* do me a favor. All the Salvene Swamp Water in this bar is awful! Go to the Sal...*Hiccup*...the Salvene galaxy, visit a bar and get me some real Salvene Swamp Water.',
				'Task' => 'Go to the Salvene Galaxy'
			),
			array(
				'Step' => 'Drink',
				'Detail' => 'Salvene Swamp Water',
				'Text' => 'Here we are! The Salvene Galaxy! Now lets find a bar.',
				'Task' => 'Go to a bar and buy a Salvene Swamp Water from the bartender. sThis may take many tries.'
			),
			array(
				'Step' => 'MoveSector',
				'Detail' => '<Starting Sector>',
				'Text' => 'Finally! A true Salvene Swamp Water, let return to that drunk!',
				'Task' => 'Return to <Stating Sector> to claim your reward.'
			),
			array(
				'Step' => 'Claim',
				'Detail' => 'Bar',
				'Text' => 'Finally! A true Salvene Swamp Water, let return to that drunk!',
				'Task' => 'Enter the bar to claim your reward.'
			)
		)
	)
);
foreach($MISSIONS as $id => &$mission)
{
	$mission['ID'] = $id;
} unset($mission);

/*
function getMissionText($mission)
{
	global $RACES,$GAME_ID,$TIME,$id;
	$text = get_subtitle($mission['Name']);
	if ($mission['On Step'] > $mission['Total'])
	{
		$text .= get_status_message('green','Mission','Mission '.$mission['Name'].' Completed.');
	}
	else
	{
		$progress = $mission['Progress'];
		$need = $mission['Task']['Detail'];
		$remain = $need - $progress;
		$plural = ($remain==1?'':'s');
		$text .= get_status_message('green','Task',$mission['Task']['Text']);
		if ($mission['Task']['Step'] == 'KillSpawn')
		{
			//get the npc
			$sql = query('SELECT * FROM npcs WHERE game_id = '.$GAME_ID.' AND npc_id = '.$mission['Progress'].' AND dead = \'FALSE\' LIMIT 1');
			if ($result = next_record($sql))
			{
				$name = stripslashes($result['name']);
				$text .= get_status_message('green','Status','Kill the NPC '.$name.'.');
			}
			elseif ($mission['Expires'] < $TIME)
			{
				$link = array();
				$link['body'] = 'trader_missions.php';
				$link['respawn'] = TRUE;
				$link['mission_id'] = $mission['Mission ID'];
				$link['text'] = 'Respawn NPC';
				$text .= create_button($link,$id,'',TRUE);
				$text .= '<br /><br />';
			} else
				$text .= get_status_message('green','Status','You must wait for a respawn.');
			
		}
		elseif ($mission['Task']['Step'] == 'KillNPCs')
		{
			$text .= get_status_message('green','Status','Kill '.$remain.' more NPCs.');
		}
		elseif ($mission['Task']['Step'] == 'KillPlayers')
		{
			$text .= get_status_message('green','Status','Kill '.$remain.' more players.');
		}
		elseif ($mission['Task']['Step'] == 'KillName')
		{
			$text .= get_status_message('green','Status','Kill '.$need.'.');
		}
		elseif ($mission['Task']['Step'] == 'Visit')
		{
			$text .= get_status_message('green','Status','Visit a '.$need.'.');
		}
		elseif ($mission['Task']['Step'] == 'DrinkAmount')
		{
			$text .= get_status_message('green','Status','Buy '.$remain.' more drink'.$plural.'.');
		}
		elseif ($mission['Task']['Step'] == 'Drink')
		{
			$text .= get_status_message('green','Status','Buy a '.$need.' drink.');
		}
		elseif ($mission['Task']['Step'] == 'Move')
		{
			$text .= get_status_message('green','Status','Move '.$remain.' more sector'.$plural.'.');
		}
		elseif ($mission['Task']['Step'] == 'MoveSector')
		{
			$text .= get_status_message('green','Status','Move to sector '.$need.'.');
		}
		elseif ($mission['Task']['Step'] == 'MoveRacial')
		{
			$text .= get_status_message('green','Status','Move to the racial galaxy of the '.$RACES[$need].'.');
		}
		elseif ($mission['Task']['Step'] == 'MoveGal')
		{
			$text .= get_status_message('green','Status','Move to the '.$need.' galaxy.');
		}
		elseif ($mission['Task']['Step'] == 'ClearNPC')
		{
			$text .= get_status_message('green','Status','Clear '.$remain.' more NPC force'.$plural.'.');
		}
		elseif ($mission['Task']['Step'] == 'StartPortRaid')
		{
			$text .= get_status_message('green','Status','Start '.$remain.' more port raid'.$plural.'.');
		}
		elseif ($mission['Task']['Step'] == 'Claim')
		{
			$text .= get_status_message('green','Status','Visit a '.$need.' to claim your reward.');
		}
		else
		{
			$text .= get_status_message('green','Status','Unknown Mission Type: '.$mission['Task']['Step'].'.');
		}
		$text .= '<br />';
		return $text;
	}
}

function checkMissionRequirements($requirements,$THIS_SECTOR,$THIS_PLAYER,$THIS_SHIP)
{
	$must_meet = sizeof($requirements);
	$met = 0;
	foreach ($requirements as $requirement => $value)
	{
		$require_name = $requirement;
		$require_attrib = $value;
	
		if (strpos($require_name,'.'))
		{
			list($player_object_name,$real_require_name) = explode('.',$require_name);
			if (strtolower($player_object_name) == 'ship') $player_object = $THIS_SHIP;
			elseif (strtolower($player_object_name) == 'sector') $player_object = $THIS_SECTOR;
			elseif (strtolower($player_object_name) == 'player') $player_object = $THIS_PLAYER;
		}
		else
		{
			$player_object = $THIS_PLAYER;
			$real_require_name = $require_name;
		}
		
		if ($player_object == $THIS_PLAYER)
			$requireObjectActual = $player_object->get($real_require_name);
		else
			$requireObjectActual = $player_object[$real_require_name];
		if (is_array($requireObjectActual))
		{
			if (is_array($require_attrib))
			{
				//add that they must meet all (-1 because we already accounted for 1 in the array)
				$must_meet += sizeof($require_attrib) - 1;
				foreach ($require_attrib as $check_val)
				{
					if (in_array($check_val,$requireObjectActual)) $met++;
				}
			}
		}
		elseif (is_array($require_attrib))
		{
			//they only need to meet one of these
			foreach ($require_attrib as $check_val)
			{
				if ($check_val == $requireObjectActual)
				{
					$met++;
					break;
				}
			}
		}
		elseif (is_numeric($require_attrib))
		{
			//we just do a straight == compare
			if ($requireObjectActual == $require_attrib)
				$met++;
		}
		else
		{
			//we need to determine what to do with this number
			if (strpos($require_attrib,'<') !== FALSE)
			{
				list($crap,$require_attrib_real) = explode('<',$require_attrib);
				if ($requireObjectActual < $require_attrib_real)
					$met++;
			}
			elseif (strstr($require_attrib,'>') !== FALSE)
			{
				list($crap,$require_attrib_real) = explode('>',$require_attrib);
				if ($requireObjectActual > $require_attrib_real)
					$met++;
			}
			elseif (strstr($require_attrib,'!') !== FALSE)
			{
				list($crap,$require_attrib_real) = explode('!',$require_attrib);
				if ($requireObjectActual != $require_attrib_real)
					$met++;
			}
			elseif (strstr($require_attrib,'|') !== FALSE)
			{
				list($lower,$upper) = explode('|',$require_attrib);
				if ($requireObjectActual > $lower && $requireObjectActual < $upper)
					$met++;
			}
			else
			{
				if ($requireObjectActual == $require_attrib)
				$met++;
			}
		}
	}
	//echo 'Mission ' . $mission_id . ': Must meet ' . $must_meet . ' and met ' . $met . '<br />';
	if ($must_meet == $met)
		return true;
	return false;
}

function check_missions($player_object = '')
{
	global $error, $THIS_PLAYER, $GAME_ID,$weapons,$upgrades,$gadgets,$ship_id;
	if ($player_object == '') $player_object = $THIS_PLAYER;
	$playerMissions = $player_object->getMissions();
	foreach ($playerMissions as $mission_id => $mission_stuff)
	{
		//see if we need to do anything for this step
		if ($mission_stuff['Next Step'] == 'TRUE')
		{
			//display the appropriate text
			if ($mission_stuff['On Step'] == 1)
				$error .= get_status_message('green','Mission Accepted',$mission_stuff['Task']['Text']);
			else
				$error .= get_status_message('green','Mission Accomplished',$mission_stuff['Task']['Text']);
			$player_object->updateMission($mission_id,'Next Step','FALSE');
		}
		if ($mission_stuff['Task']['Step'] == 'KillSpawn' && $mission_stuff['Progress'] == 0)
		{
			//spawn the NPC
			get_ship_setup($mission_stuff['Task']['Detail'],$weapons,$upgrades,$gadgets,$ship_id);
			$npc_id = create_npc($GAME_ID,'',$player_object->getSectorID(),$ship_id,$mission_stuff['Task']['Level'],0,'',$weapons,$upgrades,$gadgets,0,'Member','None','Attacker',0);
			//update the db for this npc id
			$player_object->updateMission($mission_id,'Progress',$npc_id);
		}
		elseif ($mission_stuff['Task']['Step'] == 'ClearNPC' && $mission_stuff['Progress'] == 0)
		{
			//spawn forces
			//update db so that more forces are not spawned everytime
		}
	}
} */
?>