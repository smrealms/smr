<?php

$template->assign('PageTopic','Create Universe - Adding Admin (9/10)');

$PHP_OUTPUT.=('<dl>');
$db->query('SELECT * FROM game WHERE game_id = ' . $var['game_id']);
if ($db->nextRecord())
	$PHP_OUTPUT.=('<dt class="bold">Game<dt><dd>' . $db->getField('game_name') . '</dd>');
$PHP_OUTPUT.=('<dt class="bold">Task:<dt><dd>Adding admins</d>');
$PHP_OUTPUT.=('<dt class="bold">Description:<dt><dd style="width:50%;">');
$PHP_OUTPUT.=('The universe is up and running so far. Here you have the chance to put all important people in the order you want! MrSpock has to be always the first tho. *fg*</dd>');
$PHP_OUTPUT.=('<dd>**DO NOT FORGET TO ADD ACCOUNT #'.ACCOUNT_ID_NHL.' (Newbie Help Leader)**</dd>');
$PHP_OUTPUT.=('</dl>');

$container = array();
$container['url']		= 'universe_create_admin_processing.php';
$container['game_id']	= $var['game_id'];
$PHP_OUTPUT.=create_echo_form($container);

$PHP_OUTPUT.=('<p>&nbsp;</p>');
$PHP_OUTPUT.=('<p>Please select the account to add:<br /><br />');

$db2 = new SmrMySqlDatabase();

$PHP_OUTPUT.=('<select name="admin_id" id="InputFields" style="padding-left:10px;">');
// check if mrspock was created
$db->query('SELECT player_name
			FROM player
			WHERE account_id = 1 AND
				  game_id = ' . $var['game_id']);
if ($db->nextRecord()) {

	$PHP_OUTPUT.=('<option value="0">[please select]</option>');
	// get all accounts
	$db->query('SELECT account_id, login
				FROM account
				ORDER BY login');
	while ($db->nextRecord()) {

		// get current account id and login
		$curr_account_id	= $db->getField('account_id');
		$curr_login			= $db->getField('login');

		// check if this guy is already in
		$db2->query('SELECT player_name
					 FROM player
					 WHERE account_id = '.$curr_account_id.' AND
						   game_id = ' . $var['game_id']);
		if (!$db2->nextRecord())
			$PHP_OUTPUT.=('<option value="'.$curr_account_id.'">'.$curr_login.'</option>');

	}

} else {

	$PHP_OUTPUT.=('<option value="1">MrSpock</option>');
	$player_name = 'MrSpock';
	$readonly = ' readonly';

}
$PHP_OUTPUT.=('</select><br /><br /><br />');

$PHP_OUTPUT.=('Player Name:<br /><br />');
$PHP_OUTPUT.=('<input type="text" name="player_name" value="'.$player_name.'" id="InputFields" style="padding-left:10px;"'.$readonly.'><br /><br /><br />');

$PHP_OUTPUT.=('Player Race:<br /><br />');

$PHP_OUTPUT.=('<select name="race_id" id="InputFields" style="padding-left:10px;">');
//this prevents multiple races appearing when there is more than 1 game
$only = array();
// get all available hq's
$db->query('SELECT location_name
			FROM location NATURAL JOIN location_type
			WHERE location.location_type_id > '.UNDERGROUND.' AND
				  location.location_type_id < '.FED.' AND
				  game_id = ' . $var['game_id'] . '
			ORDER BY location.location_type_id');
while ($db->nextRecord()) {

	// get the name for this race
	// HACK! cut ' HQ' from location name!
	$race_name = substr($db->getField('location_name'), 0, -3);

	// get race id for this race
	$db2->query('SELECT race_id FROM race WHERE race_name = '.$db->escapeString($race_name) .' LIMIT 1');
	if ($db2->nextRecord())
		$race_id = $db2->getField('race_id');
	else
		create_error('Couldn\'t find the '.$race_name.' in database!');
	if (in_array($race_id, $only)) continue;
	$only[] = $race_id;
	// hack for the user mrspock
	if ($player_name == 'MrSpock' && $race_name == 'Salvene')
		$selected = ' selected';
	else
		$selected = '';
	$PHP_OUTPUT.=('<option value="'.$race_id.'"'.$selected.'>'.$race_name.'</option>');

}
$PHP_OUTPUT.=('</select><br /><br /><br />');

$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=create_submit('Next >>');
$PHP_OUTPUT.=('&nbsp;&nbsp;');
//PAGE if ($player_name != 'MrSpock')
	$PHP_OUTPUT.=create_submit('End >>');
$PHP_OUTPUT.=('</form>');

?>