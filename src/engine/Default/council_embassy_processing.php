<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

if (!$player->isPresident()) {
	create_error('Only the president can view the embassy.');
}

$race_id = $var['race_id'];
$type = strtoupper(Smr\Request::get('action'));
$time = Smr\Epoch::time() + TIME_FOR_COUNCIL_VOTE;

$db = Smr\Database::getInstance();
$dbResult = $db->read('SELECT count(*) FROM race_has_voting
			WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
			AND race_id_1 = ' . $db->escapeNumber($player->getRaceID()));
if ($dbResult->record()->getInt('count(*)') > 2) {
	create_error('You can\'t initiate more than 3 votes at a time!');
}

if ($type == 'PEACE') {
	$dbResult = $db->read('SELECT 1 FROM race_has_voting
				WHERE race_id_1=' . $db->escapeNumber($race_id) . ' AND race_id_2=' . $db->escapeNumber($player->getRaceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
	if ($dbResult->hasRecord()) {
		create_error('You cannot start a vote with that race.');
	}
}

// Create the vote for the player's race
$db->replace('race_has_voting', [
	'game_id' => $db->escapeNumber($player->getGameID()),
	'race_id_1' => $db->escapeNumber($player->getRaceID()),
	'race_id_2' => $db->escapeNumber($race_id),
	'type' => $db->escapeString($type),
	'end_time' => $db->escapeNumber($time),
]);

// If voting for peace, the other race also has to vote
if ($type == 'PEACE') {
	$db->replace('race_has_voting', [
		'game_id' => $db->escapeNumber($player->getGameID()),
		'race_id_1' => $db->escapeNumber($race_id),
		'race_id_2' => $db->escapeNumber($player->getRaceID()),
		'type' => $db->escapeString($type),
		'end_time' => $db->escapeNumber($time),
	]);
}


// Send vote announcement to members of the player's council (war votes)
// or both races' councils (peace votes).
$councilMembers = Council::getRaceCouncil($player->getGameID(), $player->getRaceID());
if ($type == 'PEACE') {
	$otherCouncil = Council::getRaceCouncil($player->getGameID(), $race_id);
	$councilMembers = array_merge($councilMembers, $otherCouncil);
}

// Construct the message to be sent to the council members.
$color = ($type == 'PEACE' ? 'dgreen' : 'red');
$type_fancy = "<span class=\"$color\">$type</span>";
$message = $player->getLevelName() . ' ' . $player->getBBLink()
	. " has initiated a vote for $type_fancy with the [race=$race_id]!"
	. ' You have ' . format_time(TIME_FOR_COUNCIL_VOTE)
	. ' to cast your vote.';

foreach ($councilMembers as $accountID) {
	// don't send to the player who started the vote
	if ($player->getAccountID() != $accountID) {
		SmrPlayer::sendMessageFromRace($player->getRaceID(), $player->getGameID(), $accountID, $message, $time);
	}
}

Page::create('skeleton.php', 'council_embassy.php')->go();
