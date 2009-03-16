<?

$template->assign('PageTopic','PREFERENCES');

if (isset($var['reason']))
	$PHP_OUTPUT.=('<p><big><b style="color:red;">' . $var['reason'] . '</b></big></p>');

if(SmrSession::$game_id != 0)
{
	$PHP_OUTPUT.=('<p>');
	$PHP_OUTPUT.=('<table cellpadding="5">');
	$PHP_OUTPUT.=('<tr><th colspan="3">Player Preferences (For Current Game)</th></tr>');
	$PHP_OUTPUT.=('<tr>');
	
	$container = array();
	$container['url'] = 'preferences_processing.php';
	$form = create_form($container, 'Change Kamikaze Setting');
	
	$PHP_OUTPUT.= $form['form'];
	
	$PHP_OUTPUT.= '<tr><td>Combat drones kamikaze on mines</td>';
	$PHP_OUTPUT.=('<td>Yes: <input type="radio" name="kamikaze" id="InputFields" value="Yes"');
	if ($player->isCombatDronesKamikazeOnMines()) $PHP_OUTPUT.=(' checked="checked"');
	$PHP_OUTPUT.=('><br />No: <input type="radio" name="kamikaze" id="InputFields" value="No"');
	if (!$player->isCombatDronesKamikazeOnMines()) $PHP_OUTPUT.=(' checked="checked"');
	$PHP_OUTPUT.= '></td>';
	$PHP_OUTPUT.= '<td>';
	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.= '</td></tr></table>';
	$PHP_OUTPUT.=('</form>');
	
	$PHP_OUTPUT.=('</p><br />');
}
$PHP_OUTPUT.=('<p>');
$PHP_OUTPUT.=create_echo_form(create_container('preferences_processing.php', ''));
$PHP_OUTPUT.=('<table cellpadding="5">');
$PHP_OUTPUT.=('<tr><th colspan="3">Account Preferences</th></tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Referral Link:</td>');
$PHP_OUTPUT.=('<td><b>'.URL.'/login_create.php?ref='.$account->getAccountID().'</b></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Login:</td>');
$PHP_OUTPUT.=('<td><b>'.$account->getLogin().'</b></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>ID:</td>');
$PHP_OUTPUT.=('<td>'.$account->getAccountID().'</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>SMR&nbsp;Credits:</td>');
$PHP_OUTPUT.=('<td>'.$account->getSmrCredits().'</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>SMR&nbsp;Reward&nbsp;Credits:</td>');
$PHP_OUTPUT.=('<td>'.$account->getSmrRewardCredits().'</td>');
$PHP_OUTPUT.=('</tr>');

//ban points go here
$points = $account->getPoints();
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Ban Points:</td>');
$PHP_OUTPUT.=('<td>'.$points.'</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>old password:</td>');
$PHP_OUTPUT.=('<td><input type="password" name="old_password" id="InputFields" size="25"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>new password:</td>');
$PHP_OUTPUT.=('<td><input type="password" name="new_password" id="InputFields" size="25"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>retype password:</td>');
$PHP_OUTPUT.=('<td><input type="password" name="retype_password" id="InputFields" size="25"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>&nbsp;</td>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=create_submit('Change Password');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr><td colspan="2">&nbsp;</td></tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>eMail:</td>');
$PHP_OUTPUT.=('<td><input type="text" name="email" value="'.$account->email.'" id="InputFields" size="50"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>&nbsp;</td>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=create_submit('Save and resend validation code');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr><td colspan="2">&nbsp;</td></tr>');

$PHP_OUTPUT.=('<tr><td>Hall of Fame Name:</td>');
$PHP_OUTPUT.=('<td><input type="text" name="HoF_name" value="'.$account->HoF_name.'" id="InputFields" size="50"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>&nbsp;</td>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=create_submit('Change Name');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr><td colspan="2">&nbsp;</td></tr>');

$PHP_OUTPUT.=('<tr><td>Timezone:</td>');
$PHP_OUTPUT.=('<td>');
$time = TIME;
//get current offset
$db->query('SELECT * FROM account WHERE account_id = '.SmrSession::$account_id);
$db->nextRecord();
$offset = $db->getField('offset');
$PHP_OUTPUT.=('<select name="timez" id="InputFields">');
for ($i = -12; $i<= 11; $i++) {
	
	$PHP_OUTPUT.=('<option value='.$i);
	if ($offset == $i) $PHP_OUTPUT.=(' selected');
	$PHP_OUTPUT.=('>' . date(DATE_TIME_SHORT, $time + $i * 3600));
	
}
$PHP_OUTPUT.='</select>';
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>&nbsp;</td>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=create_submit('Change Timezone');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</form>');

$PHP_OUTPUT.=('<tr><td colspan="2">&nbsp;</td></tr>');

$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'preferences_confirm.php'));

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>SMR Credits:</td>');
$PHP_OUTPUT.=('<td><input type="text" name="amount" id="InputFields" style="width:30px;text-align:center;">&nbsp;credits&nbsp;to&nbsp;');

if (SmrSession::$game_id > 0) {

	$PHP_OUTPUT.=('<select name="account_id" id="InputFields">');
	$db->query('SELECT * FROM player WHERE game_id = '.SmrSession::$game_id.' ORDER BY player_name');
	while ($db->nextRecord())
		$PHP_OUTPUT.=('<option value="' . $db->getField('account_id') . '">' . stripslashes($db->getField('player_name')) . ' (' . $db->getField('player_id') . ')</option>');

} else {

	$PHP_OUTPUT.=('the&nbsp;account&nbsp;of&nbsp;<select name="account_id" id="InputFields">');
	$db->query('SELECT * FROM account ORDER BY login');
	while ($db->nextRecord())
		$PHP_OUTPUT.=('<option value="' . $db->getField('account_id') . '">' . $db->getField('login') . '</option>');

}

$PHP_OUTPUT.=('</select>');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr><td>&nbsp;</td>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=create_submit('Transfer');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</form>');

$PHP_OUTPUT.=('<tr><td colspan="2">&nbsp;</td></tr>');

$PHP_OUTPUT.=create_echo_form(create_container('preferences_processing.php', ''));

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Display Ship Images:</td>');
$PHP_OUTPUT.=('<td>Yes: <input type="radio" name="images" id="InputFields" value="Yes"');
if ($account->images == 'Yes') $PHP_OUTPUT.=(' CHECKED');
$PHP_OUTPUT.=('><br />No: <input type="radio" name="images" id="InputFields" value="No"');
if ($account->images == 'No') $PHP_OUTPUT.=(' CHECKED');
$PHP_OUTPUT.=('>');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr><td>&nbsp;</td>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=create_submit('Change');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$container = array();
$container['url'] = 'preferences_processing.php';
$form = create_form($container, 'Change Size');

$PHP_OUTPUT.= $form['form'];

$PHP_OUTPUT.= '<tr><td>Font size</td><td>';
$PHP_OUTPUT.= '<input type="text" size="4" name="fontsize" value="' . $account->fontsize . '">';
$PHP_OUTPUT.= ' Minimum font size is 50%</td><tr><td>&nbsp;</td>';
$PHP_OUTPUT.= '<td>';
$PHP_OUTPUT.= $form['submit'];
$PHP_OUTPUT.= '</td></tr>';
$PHP_OUTPUT.=('</form>');
$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.= '<h2>Account players</h2><br />';
$db->query('select game.game_id as game_id, game.game_name as game_name, player.player_name as player_name, player.name_changed as name_changed from player natural join game where player.account_id=' . SmrSession::$account_id . ' and game.enabled=true and game.end_date>' . TIME);
if($db->getNumRows()) {
	$PHP_OUTPUT.= '<table class="standard"><tr><th>Game</th><th>Name</th></tr>';
	while($db->nextRecord()) {
		$row = $db->getRow();

		$PHP_OUTPUT.= '<tr><td>'.$row['game_name'].'</td><td>';
		if($row['name_changed'] == 'false') {
			$container = array();
			$container['url'] = 'preferences_processing.php';
			$container['game_id'] = $row['game_id'];
			$form = create_form($container,'Alter Player');
			$PHP_OUTPUT.= $form['form'];
	
			$PHP_OUTPUT.= '<input type="text" maxlength="32" name="PlayerName" value="' . $row['player_name'] . '" size="32">&nbsp;&nbsp;';
			$PHP_OUTPUT.= $form['submit'];
			$PHP_OUTPUT.= '</form>';
		}
		else {
			$PHP_OUTPUT.= $row['player_name'];
		}
		$PHP_OUTPUT.= '</td></tr>';	
	}
	$PHP_OUTPUT.= '</table>';
}
else {
	$PHP_OUTPUT.= 'There are no players registered with this account.';
}

?>
