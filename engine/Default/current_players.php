<?

$smarty->assign('PageTopic','CURRENT PLAYERS');
$db->query('DELETE FROM cpl_tag WHERE expires > 0 AND expires < ' . TIME);
$db->query('SELECT * FROM active_session
			WHERE last_accessed >= ' . (TIME - 600) . ' AND
				  game_id = '.SmrSession::$game_id);
$count_real_last_active = $db->getNumRows();
if (empty($var['sort'])) $sort = 'experience DESC, player_name';
else $sort = $var['sort'];
if (empty($var['seq'])) $seq = 'DESC';
else $seq = $var['seq'];
$db->query('SELECT * FROM player ' .
		   'WHERE last_cpl_action >= ' . (TIME - 600) . ' AND ' .
				 'game_id = '.SmrSession::$game_id.' ' .
		   'ORDER BY '.$sort.' '.$seq);
//$PHP_OUTPUT.=('.$db->escapeString($sort, $seq<br />');
$count_last_active = $db->getNumRows();
$list = '(0';
while ($db->nextRecord()) $list .= ',' . $db->getField('account_id');
$list .= ')';
$db->query('SELECT * FROM player ' .
		   'WHERE last_cpl_action >= ' . (TIME - 600) . ' AND ' .
				 'game_id = '.SmrSession::$game_id.' ' .
		   'ORDER BY '.$sort.' '.$seq);
//if ($sort == 'experience DESC, player_name' || $sort == 'experience')
//	$db->query('SELECT * FROM player_cache WHERE game_id = '.$player->getGameID().' AND account_id IN $list ORDER BY experience $seq');

// fix it if some1 is using the logoff button
if ($count_real_last_active < $count_last_active)
	$count_real_last_active = $count_last_active;
$exp = array();
while ($db->nextRecord()) {
	$accountID = $db->getField('account_id');
	$curr_player =& SmrPlayer::getPlayer($accountID, SmrSession::$game_id);
	if ($curr_player->getAllianceID() == $player->getAllianceID() && $player->getAllianceID() != 0)
		$exp[$db->getField('account_id')] = $curr_player->getExperience();
	else
		$exp[$db->getField('account_id')] = $db->getField('experience');

}
if ($sort == 'experience DESC, player_name' || ($sort == 'experience' && $seq == 'DESC'))
	arsort($exp, SORT_NUMERIC);
elseif ($sort == 'experience' && $seq == 'ASC')
	asort($exp);
//foreach ($exp as $acc_id => $val) $PHP_OUTPUT.=('.$db->escapeString($acc_id, $val<br />');
$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<p>There ');
if ($count_real_last_active != 1)
	$PHP_OUTPUT.=('are '.$count_real_last_active.' players who have ');
else
	$PHP_OUTPUT.=('is 1 player who has ');
$PHP_OUTPUT.=('accessed the server in the last 10 minutes.<br />');

if ($count_last_active == 0)
	$PHP_OUTPUT.=('No one was moving so your ship computer can\'t intercept any transmissions.<br />');
else {

	if ($count_last_active == $count_real_last_active)
		$PHP_OUTPUT.=('All of them ');
	else
		$PHP_OUTPUT.=('A few of them ');

	$PHP_OUTPUT.=('were moving so your ship computer was able to intercept $count_last_active transmission');

	if ($count_last_active > 1)
		$PHP_OUTPUT.=('s.<br />');
	else
		$PHP_OUTPUT.=('.<br />');
}
	$PHP_OUTPUT.=('The traders listed in <span style="font-style:italic;">italics</span> are still ranked as Newbie or Beginner.</p>');

	$PHP_OUTPUT.=('<p><u>Note:</u> Experience values are updated every 2 minutes.</p>');

if ($count_last_active > 0) {

	$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="5" border="0" class="standard" width="95%">');
	$PHP_OUTPUT.=('<tr>');
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'current_players.php';
	if ($seq == 'DESC')
		$container['seq'] = 'ASC';
	else
		$container['seq'] = 'DESC';
	$container['sort'] = 'player_name';
	$PHP_OUTPUT.=('<th>');
	$PHP_OUTPUT.=create_link($container, '<font color=#80c870>Player</font>');
	$PHP_OUTPUT.=('</th>');
	$container['sort'] = 'race_id';
	$PHP_OUTPUT.=('<th>');
	$PHP_OUTPUT.=create_link($container, '<font color=#80c870>Race</font>');
	$PHP_OUTPUT.=('</th>');
	$container['sort'] = 'alliance_id';
	$PHP_OUTPUT.=('<th>');
	$PHP_OUTPUT.=create_link($container, '<font color=#80c870>Alliance</font>');
	$PHP_OUTPUT.=('</th>');
	$container['sort'] = 'experience';
	$PHP_OUTPUT.=('<th>');
	$PHP_OUTPUT.=create_link($container, '<font color=#80c870>Experience</font>');
	$PHP_OUTPUT.=('</th>');
	$PHP_OUTPUT.=('</tr>');

	//while ($db->nextRecord()) {
	foreach ($exp as $acc_id => $value) {

		$curr_account =& SmrAccount::getAccount($acc_id);
		//reset style
		$style = '';
		$curr_player =& SmrPlayer::getPlayer($acc_id, SmrSession::$game_id);
		if ($curr_account->veteran == 'FALSE' && $curr_account->get_rank() < FLEDGLING)
			$style = 'font-style:italic;';
		if ($curr_player->getAccountID() == $player->getAccountID())
			$style .= 'font-weight:bold;';

		if (!empty($style))
			$style = ' style="'.$style.'"';

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td valign="top"'.$style.'>');
		$rank = $curr_player->getLevelName();
		//$PHP_OUTPUT.=('.$db->escapeString($curr_player->getLevelName() ');
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'trader_search_result.php';
		$container['player_id']	= $curr_player->getPlayerID();
		//$name = $curr_player->getDisplayName();
		$name = $rank . ' ' . $curr_player->getDisplayName();
		$db->query('SELECT * FROM cpl_tag WHERE account_id = '.$curr_player->getAccountID().' ORDER BY custom DESC');
		while ($db->nextRecord()) {
			if ($db->getField('custom')) {
				$name = $db->getField('tag') . ' ' . $curr_player->getDisplayName();
				if ($db->getField('custom_rank')) $name .= ' (' . $db->getField('custom_rank') . ')';
				else $name .= ' (' . $rank . ')';
			} else $name .= ' ' . $db->getField('tag');
		}
		//$name .= ' $add';
		$PHP_OUTPUT.=create_link($container, $name);
		$PHP_OUTPUT.=('</td>');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'council_list.php';
		$container['race_id'] = $curr_player->getRaceID();
		$PHP_OUTPUT.=('<td style="text-align:center" '.$style.'>');
		$PHP_OUTPUT.=create_link($container, $player->getColouredRaceName($curr_player->getRaceID()));
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td '.$style.'>');
		if ($curr_player->getAllianceID() > 0) {


			$container = array();
			$container['url'] 			= 'skeleton.php';
			$container['body'] 			= 'alliance_roster.php';
			$container['alliance_id']	= $curr_player->getAllianceID();
			$PHP_OUTPUT.=create_link($container, $curr_player->getAllianceName());
		} else
			$PHP_OUTPUT.=('(none)');
		$PHP_OUTPUT.=('</td><td style="text-align:right" '.$style.'>');

		if($curr_player->getExperience() > $curr_player->getExperience()) {
			$PHP_OUTPUT.=('<img src="images/cpl_up.gif" style="float:left;height:16px" />');
		}
		else if($curr_player->getExperience() < $curr_player->getExperience()) {
			$PHP_OUTPUT.=('<img src="images/cpl_down.gif" style="float:left;height:16px" />');
		}
		else {
			$PHP_OUTPUT.=('<img src="images/cpl_horizontal.gif" style="float:left;height:16px" />');
		}

		if ($curr_player->getAllianceID() == $player->getAllianceID() && $player->getAllianceID() != 0)
		{
			if ($curr_player->getAccountID() == 2) $PHP_OUTPUT.=('A lot');
			else $PHP_OUTPUT.=(number_format($curr_player->getExperience()) . '</td>');
		}
		else {
			if ($curr_player->getAccountID() == 2) $PHP_OUTPUT.=('A lot');
			else $PHP_OUTPUT.=(number_format($curr_player->getExperience()) . '</td>');
		}

		
		$PHP_OUTPUT.=('</tr>');

	}

	$PHP_OUTPUT.=('	</table>');

}

$PHP_OUTPUT.=('</div>');

?>
