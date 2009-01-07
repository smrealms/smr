<?

$container = array();
$container['url'] = 'skeleton.php';
if (SmrSession::$game_id > 0)
  if ($player->isLandedOnPlanet()) $container['body'] = 'planet_main.php'; else $container['body'] = 'current_sector.php';
else
  $container['body'] = 'game_play.php';
$action = $_REQUEST['action'];
$email = $_REQUEST['email'];
$new_password = $_REQUEST['new_password'];
$old_password = $_REQUEST['old_password'];
$retype_password = $_REQUEST['retype_password'];
$HoF_name = $_REQUEST['HoF_name'];
if ($action == 'Save and resend validation code') {

  // get user and host for the provided address
  list($user, $host) = explode('@', $email);

  // check if the host got a MX or at least an A entry
  if (!checkdnsrr($host, 'MX') && !checkdnsrr($host, 'A'))
    create_error('This is not a valid email address! The domain '.$db->escapeString($host).' does not exist.');

  if (strstr($email, ' '))
    create_error('The eMail is invalid! It cannot contain any spaces.');
  
  $db->query('SELECT * FROM account WHERE email = '.$db->escapeString($email).' and account_id != ' . $account->account_id);
  if ($db->nf() > 0)
    create_error('This eMail address is already registered.');

  $account->email = $email;
  $account->validation_code = substr(SmrSession::$session_id, 0, 10);
  $account->validated = 'FALSE';
  $account->update();

  // remember when we sent validation code
  $db->query('REPLACE INTO notification (notification_type, account_id, time) ' .
                   'VALUES(\'validation_code\', '.SmrSession::$account_id.', ' . time() . ')');

  mail($email, 'Your validation code!',
    'You changed your email address registered within SMR and need to revalidate now!\n\r\n\r' .
    '   Your new validation code is: '.$account->validation_code.'\n\r\n\r' .
    'The Space Merchant Realms server is on the web at '.$URL.'/.\n\r' .
    'You\'ll find a quick how-to-play here '.$URL.'/manual.php\n\r' .
    'Please verify within the next 7 days or your account will be automatically deleted.',
    'From: support@smrealms.de');

  // get rid of that email permission
  $db->query('DELETE FROM account_is_closed ' .
            'WHERE account_id = '.$account->account_id.' AND ' .
                'reason_id = 1');

  // overwrite container
  $container['body'] = 'validate.php';

} elseif ($action == 'Change Password') {

  if (empty($new_password))
    create_error('You must enter a non empty password!');

  if ($account->checkPassword($old_password))
    create_error('Your current password is wrong!');

  if ($new_password != $retype_password)
    create_error('The passwords you entered don\'t match!');

  if ($new_password == $account->login)
    create_error('Your chosen password is invalid!');

  $account->setPassword($new_password);
} elseif ($action == 'Change Name') {

  // disallow certain ascii chars
  for ($i = 0; $i < strlen($HoF_name); $i++)
    if (ord($HoF_name[$i]) < 32 || ord($HoF_name[$i]) > 127)
      create_error('Your Hall Of Fame name contains invalid characters!');

  //disallow blank names
  if (empty($HoF_name) || $HoF_name == '') create_error('You Hall of Fame name must contain characters!');

  //no duplicates
  $db->query('SELECT * FROM account_has_stats WHERE HoF_name = ' . $db->escape_string($HoF_name, true) . ' AND account_id <> '.$account->account_id);
  if ($db->next_record()) create_error('Someone is already using that name!');

  // set the HoF name in account stat
  $db->query('UPDATE account_has_stats SET HoF_name = ' . $db->escape_string($HoF_name, true) . ' WHERE account_id = '.$account->account_id.'');

} elseif ($action == 'Yes')  {

  $account_id = $var['account_id'];
  $amount = $var['amount'];

  // take from us
  $account->set_credits($account->get_credits() - $amount);

  // create his account
  $his_account =& SmrAccount::getAccount($account_id);

  // add to him
  $his_account->set_credits($his_account->get_credits() + $amount);

} elseif ($action == 'Change Timezone') {

  $timez = $_REQUEST['timez'];
  if (!is_numeric($timez))
  	create_error('Numbers only please');

  $db->query('UPDATE account SET offset = '.$timez.' WHERE account_id = '.SmrSession::$account_id);

} elseif ($action == 'Change') {

  $account->images = $_REQUEST['images'];
  $account->update();

}
else if ($action == 'Change Size' && is_numeric($_REQUEST['fontsize']) && $_REQUEST['fontsize'] > 50) {
	$db->query('UPDATE account SET fontsize=' . $_REQUEST['fontsize'] . ' WHERE account_id = '.SmrSession::$account_id);
	$account->fontsize = $_REQUEST['fontsize'];

}
else if ($action == 'Alter Player') {
	
	// disallow certain ascii chars
	for ($i = 0; $i < strlen($_POST['PlayerName']); $i++)
		if (ord($_POST['PlayerName'][$i]) < 32 || ord($_POST['PlayerName'][$i]) > 127)
			create_error('The player name contains invalid characters!');

	// trim input now
	$player_name = trim($_POST['PlayerName']);

	if (empty($player_name))
		create_error('You must enter a player name!');
	
	// Check if name is in use.
	$db->query('SELECT account_id FROM player WHERE account_id!=' . SmrSession::$account_id . ' AND game_id=' . $var['game_id'] . ' AND player_name=' . $db->escape_string($player_name) . ' LIMIT 1' );
	if($db->nf())	{
		create_error('Name is already being used in this game!');
	}
	
	$changePlayer =& SmrPlayer::getPlayer(SmrSession::$account_id,$var['game_id']);
	$old_name = $changePlayer->getDisplayName();
	
	if($old_name == $player_name)
	{
		create_error('Your player already has that name!');
	}
	
	if($changePlayer->isNameChanged())
	{
		create_error('You have already changed your name once!');
	}
	
	$changePlayer->setPlayerName($player_name);

	$news = '<span class="blue">ADMIN</span> Please be advised that ' . $old_name . ' has changed their name to ' . $player_name . '</span>';
	$db->query('INSERT INTO news (time, news_message, game_id) VALUES (' . TIME . ',' . $db->escape_string($news, FALSE) . ',' . $var['game_id'] . ')');
}

forward($container);

?>
