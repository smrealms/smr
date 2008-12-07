<?

// TODO: Sectors explored, sector visited (Possibly put in current sector)
if ($var['target_sector'] == $player->getSectorID())
	forward(create_container('skeleton.php', $var['target_page']));

$db->query('SELECT galaxy_id,sector_id FROM sector WHERE (sector_id=' . $player->getSectorID() . ' OR sector_id=' . $var['target_sector'] . ') AND game_id=' . SmrSession::$game_id . ' LIMIT 2' );
while($db->next_record()){
	$gal_ids[$db->f('sector_id')] = $db->f('galaxy_id');
}

//allow hidden players (admins that don't play) to move without pinging, hitting mines, losing turns
if (in_array($player->getAccountID(), $HIDDEN_PLAYERS))
{
	//for plotted course
	$player->setLastSectorID($player->getSectorID());
	if($gal_ids[$player->getSectorID()] != $gal_ids[$var['target_sector']])
		$turns = 5;
	else
		$turns = 1;
	//make them pop on CPL
	$player->updateLastCPLAction();
	$player->getSectorID($var['target_sector']);
	$player->update();
	
	//update plot
	$db->query('SELECT course, distance FROM player_plotted_course WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID().' LIMIT 1');
	if ($db->next_record()) {
	    $path_list	= unserialize($db->f('course'));
	    $distance	= $db->f('distance');
	    if ($path_list[0] == $var['target_sector']) {
	        array_shift($path_list);
	        $distance -= $turns;
	        if (!empty($path_list))
	            $db->query('UPDATE player_plotted_course SET distance = '.$distance.', course = ' . $db->escape_string(serialize($path_list)) . ' WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID().' LIMIT 1');
	        else
	            $db->query('DELETE FROM player_plotted_course WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
	    } else
	        $db->query('DELETE FROM player_plotted_course WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
	}
	// get new sector object
	$sector =& SmrSector::getSector($player->getGameID(), $player->getSectorID(), $player->getAccountID());
	$sector->mark_visited();
	$container['url'] = 'skeleton.php';
	$container['body'] = $var['target_page'];
	forward($container);
}
if(isset($_REQUEST['action'])) {
	$action = $_REQUEST['action'];
	if ($action == 'No')
		forward(create_container('skeleton.php', $var['target_page']));
}

// you can't move while on planet
if ($player->isLandedOnPlanet())
    create_error('You can\'t activate your engine while you are on a planet!');

if($gal_ids[$player->getSectorID()] != $gal_ids[$var['target_sector']]) {
    $turns = 5;
}
else {
    $turns = 1;
}
if ($player->getTurns() < $turns)
    create_error('You don\'t have enough turns to move!');


		
// ok we can only get the leave save heaven if we go through a warp
if ($action != 'Yes' && $turns == 5 && $gal_ids[$player->getSectorID()] < 9) {

	// get our rank
	$rank_id = $account->get_rank();

	// are we a noob
	if ($rank_id < FLEDGLING && $account->veteran == 'FALSE') {


		if($gal_ids[$player->getSectorID()] < 9 && $gal_ids[$var['target_sector']] > 8) {
			$container = create_container('skeleton.php', 'leaving_newbie_galaxy.php');
			$container['method'] = 'move';
			transfer('target_page');
			transfer('target_sector');
			forward($container);

		}
	}
}
$query = get_forces_query($gal_ids[$player->getSectorID()]);
$db->query($query);

$mine_owner_id = false;
$scout_owners = array();

while($db->next_record()) {
	if($db->f('mines') && !$mine_owner_id) {
		$mine_owner_id = $db->f('account_id');
		$forces[$mine_owner_id][2] = $db->f('mines');
	}
	if($db->f('scout_drones')) {
		$scout_owners[] = $db->f('account_id');
	}
}

if ($player->getLastSectorID() != $var['target_sector'] && $mine_owner_id) {

	// set last sector
	$player->setLastSectorID($var['target_sector']);

	if ($player->getNewbieTurns() > 0) {

		$container['url']	= 'skeleton.php';
        $container['body']	= 'current_sector.php';
        $container['msg']	= 'You have just flown past a sprinkle of mines.<br>Because of your newbie status you have been spared from the harsh reality of the forces.<br>It has cost you ';
		if($forces[$mine_owner_id][2] < 10) {
       	    $player->takeTurns(1,1);
			$container['msg'] .= '1 turn';
		}
		else if($forces[$mine_owner_id][2] >= 10 && $forces[$mine_owner_id][2] < 25) {
			$player->takeTurns(2,1);
			$container['msg'] .= '2 turns';
		}
		else if($forces[$mine_owner_id][2] >= 25) {
			$player ->takeTurns(3,1);
			$container['msg'] .= '3 turns';
		}
	}
	
	$player->update();
	
	if($player->getNewbieTurns() > 0) {
		$container['msg'] .= ' to navigate the minefield safely';
        forward($container);
	}       
	else {
		$owner_id = $mine_owner_id;
		include('forces_minefield_processing.php');
		exit;
	}
}

//set the last sector
$player->setLastSectorID($player->getSectorID());

// log action
$account->log(5, 'Moves to sector: ' . $var['target_sector'], $player->getSectorID());

// send scout msg
if(count($scout_owners)) {
	send_scout_messages($scout_owners,false);
}

// Move the user around (Must be done while holding both sector locks)
$player->setSectorID($var['target_sector']);
$player->takeTurns($turns,$turns);
$player->detected = 'false';
$player->update();

// We need to release the lock on our old sector
release_lock();

// We need a lock on the new sector so that more than one person isn't hitting the same mines
acquire_lock($var['target_sector']);

// check if this came from a plotted course from db
$db->query('SELECT course, distance FROM player_plotted_course WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID().' LIMIT 1');
if ($db->next_record()) {

    // get the array back
    $path_list	= unserialize($db->f('course'));
    $distance	= $db->f('distance');

    if ($path_list[0] == $var['target_sector']) {

        array_shift($path_list);
        $distance -= $turns;

        // write back to db
        if (!empty($path_list))
            $db->query('UPDATE player_plotted_course SET distance = '.$distance.', course = ' . $db->escape_string(serialize($path_list)) . ' WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID().' LIMIT 1');
        else
            $db->query('DELETE FROM player_plotted_course WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());

    } else
        $db->query('DELETE FROM player_plotted_course WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());

}

// get new sector object
$sector =& SmrSector::getSector($player->getGameID(), $player->getSectorID(), $player->getAccountID());

//add that the player explored here if it hasnt been explored...for HoF
if (!$sector->isVisited()) {
	$player->increaseHOF(1,'sectors_explored');
}
// make current sector visible to him
$sector->mark_visited();

// send scout msgs
$db->query(get_forces_query($gal_ids[$var['target_sector']]));

$mine_owner_id = false;
$scout_owners = array();

while($db->next_record()) {
	if($db->f('mines') && !$mine_owner_id) {
		$mine_owner_id = $db->f('account_id');
		$forces[$mine_owner_id][2] = $db->f('mines');
	}
	if($db->f('scout_drones')) {
		$scout_owners[] = $db->f('account_id');
	}
}

if(count($scout_owners)) {
	send_scout_messages($scout_owners,true);
}

if ($mine_owner_id) {

	if ($player->getNewbieTurns() > 0) {

		$container['url']	= 'skeleton.php';
        $container['body']	= 'current_sector.php';
        $container['msg']	= 'You have just flown past a sprinkle of mines.<br>Because of your newbie status you have been spared from the harsh reality of the forces.<br>It has cost you ';

		if($forces[$mine_owner_id][2] < 10) {
       	    $player->takeTurns(1,1);
			$container['msg'] .= '1 turn';
		}
		else if($forces[$mine_owner_id][2] >= 10 && $forces[$mine_owner_id][2] < 25) {
			$player->takeTurns(2,1);
			$container['msg'] .= '2 turns';
		}
		else if($forces[$mine_owner_id][2] >= 25) {
			$player ->takeTurns(3,1);
			$container['msg'] .= '3 turns';
		}
	}
	
	$player->update();
	
	if($player->getNewbieTurns() > 0) {
		$container['msg'] .= ' to navigate the minefield safely';
        forward($container);
	}       
	else {
		$owner_id = $mine_owner_id;
		include('forces_minefield_processing.php');
		exit;
	}
}

// otherwise
$container['url'] = 'skeleton.php';
$container['body'] = $var['target_page'];
forward($container);


function get_forces_query($galaxy_id) {
	global $account,$session,$player,$db;
	$db->query('SELECT * FROM alliance_treaties WHERE (alliance_id_1 = '.$player->getAllianceID().' OR alliance_id_2 = '.$player->getAllianceID().')
				AND game_id = '.$player->getGameID().'
				AND official = \'TRUE\'
				AND forces_nap = 1');
	$allied[] = $player->getAllianceID();
	while ($db->next_record()) {
		if ($db->f('alliance_id_1') == $player->getAllianceID()) $allied[] = $db->f('alliance_id_2');
		else $allied[] = $db->f('alliance_id_1');
	}
	$query = '
	SELECT
	sector_has_forces.owner_id as account_id,
	sector_has_forces.scout_drones as scout_drones,
	sector_has_forces.mines as mines
	FROM sector_has_forces,player';

	// Vets don't see newbies in racials and vice versa
	if ($galaxy_id < 9) {
		$query .= ',account_has_stats,account
					WHERE account.account_id = sector_has_forces.owner_id
					AND account_has_stats.account_id = sector_has_forces.owner_id';

		if($account->get_rank() > BEGINNER || $account->veteran == 'TRUE') {
			$query2 = ' AND (
			(account_has_stats.kills >= 15 OR account_has_stats.experience_traded >= 60000) OR 
			(account_has_stats.kills >= 10 AND account_has_stats.experience_traded >= 40000)
			OR account.veteran=\'TRUE\')';
		}
		else {
			$query2 = ' AND (
			(account_has_stats.kills < 15 AND account_has_stats.experience_traded < 60000) OR 
			(account_has_stats.kills < 10 AND account_has_stats.experience_traded < 40000)
			) AND account.veteran=\'FALSE\'';
		}
		$query2 .= ' AND ';
	}
	else {
		$query2 = ' WHERE ';
	}

	$query .= $query2 . '
	player.account_id=sector_has_forces.owner_id
	AND player.account_id!=' . SmrSession::$account_id . '
	AND (player.alliance_id=0 OR player.alliance_id NOT IN (' . implode(',',$allied) . '))
	AND player.game_id=' . SmrSession::$game_id . '
	AND sector_has_forces.game_id=' . SmrSession::$game_id . '
	AND sector_has_forces.sector_id=' . $player->getSectorID() . ' ORDER BY sector_has_forces.mines DESC';
	
	return $query;
}

function send_scout_messages($scout_owners,$direction){
	global $db,$player,$SCOUTMSG,$session;
	$scout_query = '';
	$scout_query2 = '';
	$helper_query = ',' . SmrSession::$game_id .',' . $SCOUTMSG . ',';
	$message = 'Your forces have spotted ' . $player->getDisplayName() . ' ';
	if($direction) {
		$message .= 'entering';
	}
	else {
		$message .= 'leaving';
	}
	$message .= ' sector #<span class="yellow">' . $player->getSectorID() . '</span>';
	$helper_query .= $db->escape_string($message,false) . ',' . $player->getAccountID() . ',' . time() . ',' . (time() + 259200) . ')'; 
	$helper_query2 = '(' . SmrSession::$game_id . ',' . $SCOUTMSG . ',';

	foreach ($scout_owners as $account_id){
		if(!empty($scout_query)){
			$scout_query .= ',';
			$scout_query2 .= ',';
		}	
		$scout_query .= '(' . $account_id . $helper_query;
		$scout_query2 .= $helper_query2 . $account_id . ')';
	}
	$db->query('INSERT INTO message (account_id,game_id,message_type_id,message_text,sender_id,send_time,expire_time) VALUES ' . $scout_query);
	$db->query('REPLACE INTO player_has_unread_messages (game_id,message_type_id,account_id) VALUES ' . $scout_query2);
}
?>
