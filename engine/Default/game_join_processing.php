<?php

// trim input now
$player_name = trim($_REQUEST['player_name']);

if(!defined('NPCScript')&&strpos($player_name,'NPC')===0)
	create_error('Player names cannot begin with "NPC".');

// disallow certain ascii chars
for ($i = 0; $i < strlen($player_name); $i++)
	if (ord($player_name[$i]) < 32 || ord($player_name[$i]) > 127)
		create_error('The player name contains invalid characters!');


if (empty($player_name))
	create_error('You must enter a player name!');
$race_id = $_REQUEST['race_id'];
if (empty($race_id) || $race_id == 1)
	create_error('Please choose a race!');
if(!is_numeric($var['game_id']))
	create_error('Game ID is not numeric');

$gameID = $var['game_id'];

$db->query('SELECT * FROM player WHERE game_id = ' . $gameID . ' AND player_name = ' . $db->escape_string($player_name, true));
if ($db->getNumRows() > 0)
	create_error('The player name already exists.');

if (Globals::getGameInfo($gameID)===false)
	create_error('Game not found!');

// does it cost something to join that game?
$credits	= Globals::getGameCreditsRequired($gameID);
if ($credits > 0)
{
	if($account->getTotalSmrCredits()<$credits)
		create_error('You do not have enough credits to join this game');
	$account->decreaseTotalSmrCredits($credits);
}

// check if hof entry is there
$db->query('SELECT * FROM account_has_stats WHERE account_id = '.SmrSession::$account_id);
if (!$db->getNumRows())
	$db->query('INSERT INTO account_has_stats (account_id, HoF_name) VALUES ('.$account->account_id.', ' . $db->escape_string($account->login, true) . ')');

// put him in a sector with a hq
$hq_id = $race_id + 101;
$db->query('SELECT * FROM location NATURAL JOIN sector ' .
		   'WHERE location.game_id = ' . $gameID . ' AND ' .
		   'location_type_id = '.$hq_id);
if ($db->nextRecord())
	$home_sector_id = $db->getField('sector_id');
else
	$home_sector_id = 1;

// get rank_id
$rank_id = $account->get_rank();

// for newbie and beginner another ship, more shields and armour
if ($account->isNewbie())
{
	$ship_id = SHIP_TYPE_NEWBIE_MERCHANT_VESSEL;
	$amount_shields = 75;
	$amount_armour = 150;
}
else
{
	$ship_id = SHIP_TYPE_GALACTIC_SEMI;
	$amount_shields = 50;
	$amount_armour = 50;
}

$last_turn_update = Globals::getGameStartDate($gameID);

//// newbie leaders need to put into there alliances
if (SmrSession::$account_id == ACCOUNT_ID_NHL)
  $alliance_id = 302;
else
  $alliance_id = 0;

$db->lockTable('player');

// get last registered player id in that game and increase by one.
$db->query('SELECT MAX(player_id) FROM player WHERE game_id = ' . $gameID . ' ORDER BY player_id DESC LIMIT 1');
if ($db->nextRecord())
	$player_id = $db->getField('MAX(player_id)') + 1;
else
	$player_id = 1;

// insert into player table.
$db->query('INSERT INTO player (account_id, game_id, player_id, player_name, race_id, ship_type_id, credits, alliance_id, sector_id, last_turn_update, last_cpl_action,last_active) ' .
						'VALUES('.SmrSession::$account_id.', ' . $gameID . ', '.$player_id.', ' . $db->escape_string($player_name, true) . ', '.$race_id.', '.$ship_id.', '.$db->escapeNumber(Globals::getStartingCredits($gameID)).', '.$alliance_id.', '.$home_sector_id.', '.$last_turn_update.', ' . TIME . ', ' . TIME . ')');

$db->unlock();

// give the player shields
$db->query('INSERT INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) ' .
								   'VALUES('.SmrSession::$account_id.', ' . $gameID . ', 1, '.$amount_shields.', '.$amount_shields.')');
// give the player armour
$db->query('INSERT INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) ' .
								   'VALUES('.SmrSession::$account_id.', ' . $gameID . ', 2, '.$amount_armour.', '.$amount_armour.')');
// give the player cargo hold
$db->query('INSERT INTO ship_has_hardware (account_id, game_id, hardware_type_id, amount, old_amount) ' .
								   'VALUES('.SmrSession::$account_id.', ' . $gameID . ', 3, 40, 40)');
// give the player weapons
$db->query('INSERT INTO ship_has_weapon (account_id, game_id, order_id, weapon_type_id) ' .
								 'VALUES('.SmrSession::$account_id.', ' . $gameID . ', 0, 46)');

// update stats
$db->query('UPDATE account_has_stats SET games_joined = games_joined + 1 WHERE account_id = '.$account->account_id);

// is this our first game?
$db->query('SELECT * FROM account_has_stats WHERE account_id = '.$account->account_id);
$db->nextRecord();
if ($db->getField('games_joined') == 1) {

	//we are a newb set our alliance to be Newbie Help Allaince
	$id = 302;
	$db->query('UPDATE player SET alliance_id = '.$id.' WHERE account_id = '.$account->account_id.' AND game_id = '.$gameID);
	$db->query('INSERT INTO player_has_alliance_role (game_id, account_id, role_id,alliance_id) VALUES ('.$gameID.', '.$account->account_id.', 2,'.$id.')');
	//we need to send them some messages
	$time = TIME;
	$message = 'Welcome to Space Merchant Realms, this message is to get you underway with information to start you off in the game. All newbie and beginner rated player are placed into a teaching alliance run by a Veteran player who is experienced enough to answer all your questions and give you a helping hand at learning the basics of the game.<br /><br />
	Apart from your leader (denoted with a star on your alliance roster) there are various other ways to get information and help. Newbie helpers are players in Blue marked on the Current Players List which you can view by clicking the link on the left-hand side of the screen that says "Current Players". Also you can visit the SMR Manual via a link on the left which gives detailed information on all aspects fo the game.<br /><br />
	SMR is a very community orientated game and as such there is an IRC Chat server setup for people to talk with each other and coordinate your alliances. There is a link on the left which will take you directly to the main SMR room where people come to hang out and chat. You can also get help in the game in the #smr room. You can access this by typing /join #smr-help in the server window. If you prefer to use a dedicated program to access IRC Chat rather than a browser you can goto http://www.mirc.com which is a good shareware program (asks to register the program after 30 days but you can still use it after 30 days so you won\'t get cut off from using it) or http://www.xchat.org which is a free alternative. In the options of either program you will need to enter the server information to access the server. Add a new server and enter the server address irc.coldfront.net using port 6667. Once connected you can use the /join command to join #smr (/join #smr) or any other room on the server as normal.<br /><br />
	Apart from this you can view the webboard via a link on the left to join in community chat and conversations, ask questions for help and make suggestions for the game in various forums.<br /><br />
	To get underway, click the alliance link on the left where you can get more information on 	how to get started on the alliance message board which will get you into your alliance chat 	on IRC so you can get started and have your questions answered.<br /><br />Depending on the size and resolution of your monitor the default font size may be too large or small. This can be changed using the preferences link on the left panel.';

	$db->query('INSERT INTO message (game_id, account_id, message_type_id, message_text, sender_id, send_time, msg_read, expire_time) ' .
	'VALUES ('.$gameID.', '.$account->account_id.', '.MSG_ADMIN.', '.$db->escapeString($message).', 0, '.$time.', \'FALSE\', 0)');

	$db->query('REPLACE INTO player_has_unread_messages (account_id, game_id, message_type_id) VALUES ' .
				'('.$account->account_id.', '.$gameID.', '.MSG_ADMIN.')');

}
// insert the huge amount of sectors into the database :)
$db->query('SELECT MIN(sector_id), MAX(sector_id)
			FROM sector
			WHERE game_id = ' . $gameID);
if (!$db->nextRecord())
	create_error('This game doesn\'t have any sectors');

$min_sector = $db->getField('MIN(sector_id)');
$max_sector = $db->getField('MAX(sector_id)');

for ($i = $min_sector; $i <= $max_sector; $i++) {

    //if this is our home sector we dont add it.
    if ($i == $home_sector_id)
        continue;

    $db->query('INSERT INTO player_visited_sector (account_id, game_id, sector_id) VALUES ('.SmrSession::$account_id.', ' . $gameID . ', '.$i.')');

}
$db->query('INSERT INTO player_has_stats (account_id, game_id) VALUES ('.SmrSession::$account_id.', ' . $gameID . ')');
forward(create_container('skeleton.php', 'game_play.php'));

?>