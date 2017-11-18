<?php

$template->assign('PageTopic','Current Players');
$db->query('DELETE FROM cpl_tag WHERE expires > 0 AND expires < ' . $db->escapeNumber(TIME));
$db->query('SELECT count(*) count FROM active_session
			WHERE last_accessed >= ' . $db->escapeNumber(TIME - 600) . ' AND
				game_id = ' . $db->escapeNumber($player->getGameID()));
$count_real_last_active = 0;
if($db->nextRecord())
	$count_real_last_active = $db->getField('count');
if(SmrSession::$last_accessed < TIME - 600)
	++$count_real_last_active;


if (empty($var['sort'])) $sort = 'experience DESC, player_name';
else $sort = $var['sort'];
if (empty($var['seq'])) $seq = 'DESC';
else $seq = $var['seq'];

$db->query('SELECT * FROM player
		WHERE last_cpl_action >= ' . $db->escapeNumber(TIME - 600) . '
			AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
		ORDER BY '.$sort.' '.$seq);
$count_last_active = $db->getNumRows();

// fix it if some1 is using the logoff button
if ($count_real_last_active < $count_last_active)
	$count_real_last_active = $count_last_active;
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

	$PHP_OUTPUT.=('were moving so your ship computer was able to intercept '.$count_last_active.' transmission');

	if ($count_last_active > 1)
		$PHP_OUTPUT.=('s');
	$PHP_OUTPUT.=('.<br />');
}

$PHP_OUTPUT.=('The traders listed in <span class="italic">italics</span> are still ranked as Newbie or Beginner.</p>');

if ($count_last_active > 0) {
	$PHP_OUTPUT.=('<table class="standard" width="95%">');
	$PHP_OUTPUT.=('<tr>');
	$container = create_container('skeleton.php', 'current_players.php');
	if ($seq == 'DESC') {
		$container['seq'] = 'ASC';
	}
	else {
		$container['seq'] = 'DESC';
	}
	$container['sort'] = 'player_name';
	$PHP_OUTPUT.=('<th>');
	$PHP_OUTPUT.=create_link($container, '<span class="lgreen">Player</span>');
	$PHP_OUTPUT.=('</th>');
	$container['sort'] = 'race_id';
	$PHP_OUTPUT.=('<th>');
	$PHP_OUTPUT.=create_link($container, '<span class="lgreen">Race</span>');
	$PHP_OUTPUT.=('</th>');
	$container['sort'] = 'alliance_id';
	$PHP_OUTPUT.=('<th>');
	$PHP_OUTPUT.=create_link($container, '<span class="lgreen">Alliance</span>');
	$PHP_OUTPUT.=('</th>');
	$container['sort'] = 'experience';
	$PHP_OUTPUT.=('<th>');
	$PHP_OUTPUT.=create_link($container, '<span class="lgreen">Experience</span>');
	$PHP_OUTPUT.=('</th>');
	$PHP_OUTPUT.=('</tr>');

	$db2 = new SmrMySqlDatabase();
	while ($db->nextRecord()) {
		$accountID = $db->getField('account_id');
		$curr_player =& SmrPlayer::getPlayer($accountID, $player->getGameID());
		$curr_account =& SmrAccount::getAccount($accountID);

		$class='';
		if ($player->equals($curr_player))
			$class .= 'bold';
		if($curr_account->isNewbie())
			$class.= ' newbie';
		if($class!='')
			$class = ' class="'.trim($class).'"';
		$PHP_OUTPUT.= '<tr'.$class.'>';

		$PHP_OUTPUT.=('<td valign="top">');
		$container = array();
		$container['url']		= 'skeleton.php';
		$container['body']		= 'trader_search_result.php';
		$container['player_id']	= $curr_player->getPlayerID();
		$name = $curr_player->getLevelName() . ' ' . $curr_player->getDisplayName();
		$db2->query('SELECT * FROM cpl_tag WHERE account_id = ' . $db2->escapeNumber($curr_player->getAccountID()) . ' ORDER BY custom DESC');
		while ($db2->nextRecord()) {
			if ($db2->getField('custom')) {
				$name = $db2->getField('tag') . ' ' . $curr_player->getDisplayName();
				if ($db2->getField('custom_rank')) $name .= ' (' . $db2->getField('custom_rank') . ')';
				else $name .= ' (' . $curr_player->getLevelName() . ')';
			}
			else $name .= ' ' . $db2->getField('tag');
		}
		$PHP_OUTPUT.=create_link($container, $name);
		$PHP_OUTPUT.=('</td>');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'council_list.php';
		$container['race_id'] = $curr_player->getRaceID();
		$PHP_OUTPUT.=('<td class="center">');
		$PHP_OUTPUT.=create_link($container, $player->getColouredRaceName($curr_player->getRaceID()));
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td>');
		if ($curr_player->hasAlliance()) {
			$PHP_OUTPUT.=create_link($curr_player->getAllianceRosterHREF(), $curr_player->getAllianceName());
		}
		else
			$PHP_OUTPUT.=('(none)');
		$PHP_OUTPUT.= '</td><td class="right">'. number_format($curr_player->getExperience()) . '</td>';
		$PHP_OUTPUT.=('</tr>');
	}
	$PHP_OUTPUT.=('	</table>');
}

$PHP_OUTPUT.='<br /><div class="buttonA"><a class="buttonA" href="'.Globals::getSendGlobalMessageHREF().'">&nbsp;Send Global Message&nbsp;</a></div>';

$PHP_OUTPUT.=('</div>');

?>
