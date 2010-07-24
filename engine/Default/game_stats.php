<?php

//get game id
$game_id = $var['game_id'];

$db->query('SELECT * FROM player ' .
			'WHERE last_cpl_action >= ' . (TIME - 600) . ' AND ' .
				  'game_id = '.$game_id);
$current = $db->getNumRows();
$PHP_OUTPUT.=('<div align="center">');
$template->assign('PageTopic','Game Stats for '.Globals::getGameName($game_id));
$PHP_OUTPUT.=create_table();
$PHP_OUTPUT.='<tr><td align="center">General Info</td><td align="center">Other Info</td></tr>
<tr>
<td valign="top" align="center">
<table class="nobord">
<tr><td align="right">Name</td>					<td>&nbsp;</td><td align="left">'.Globals::getGameName($game_id).'</td></tr>
<tr><td align="right">Description</td>			<td>&nbsp;</td><td align="left">'.bbifyMessage(Globals::getGameDescription($game_id)).'</td></tr>
<tr><td align="right">Start Date</td>			<td>&nbsp;</td><td align="left">'.date(DATE_DATE_SHORT,Globals::getGameStartDate($game_id)).'</td></tr>
<tr><td align="right">End Date</td>				<td>&nbsp;</td><td align="left">'.date(DATE_DATE_SHORT,Globals::getGameEndDate($game_id)).'</td></tr>
<tr><td align="right">Current Players</td>		<td>&nbsp;</td><td align="left">'.$current.'</td></tr>
<tr><td align="right">Max Turns</td>			<td>&nbsp;</td><td align="left">'.Globals::getGameMaxTurns($game_id).'</td></tr>
<tr><td align="right">Max Players</td>			<td>&nbsp;</td><td align="left">'.Globals::getGameMaxPlayers($game_id).'</td></tr>
<tr><td align="right">Alliance Max Players</td>	<td>&nbsp;</td><td align="left">'.Globals::getAllianceMaxPlayers($game_id).'</td></tr>
<tr><td align="right">Alliance Max Vets</td>	<td>&nbsp;</td><td align="left">'.Globals::getAllianceMaxVets($game_id).'</td></tr>
<tr><td align="right">Game Type</td>			<td>&nbsp;</td><td align="left">'.Globals::getGameType($game_id).'</td></tr>
<tr><td align="right">Game Speed</td>			<td>&nbsp;</td><td align="left">'.Globals::getGameSpeed($game_id).'</td></tr>
<tr><td align="right">Credits Needed</td>		<td>&nbsp;</td><td align="left">'.Globals::getGameCreditsRequired($game_id).'</td></tr>
<tr><td align="right">Stats Ignored</td>		<td>&nbsp;</td><td align="left">'.(Globals::getGameIgnoreStats($game_id)?'Yes':'No').'</td></tr>
<tr><td align="right">Starting Credits</td>		<td>&nbsp;</td><td align="left">'.Globals::getStartingCredits($game_id).'</td></tr>
</table>
</td>';
$db->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY experience DESC');
if ($db->nextRecord()) {
	
	$players = $db->getNumRows();
	$max_exp = $db->getField('experience');
	
}
$db->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY alignment DESC');
if ($db->nextRecord()) $align = $db->getField('alignment');
$db->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY alignment ASC');
if ($db->nextRecord()) $align_low = $db->getField('alignment');
$db->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY kills DESC');
if ($db->nextRecord()) $kills = $db->getField('kills');

	
$db->query('SELECT * FROM alliance WHERE game_id = '.$game_id);
if ($db->nextRecord()) $alliances = $db->getNumRows();
$PHP_OUTPUT.='
<td valign="top" align="center">
<table class="nobord">
<tr><td align="right">Players</td>           <td>&nbsp;</td><td align="left">'.$players.'</td></tr>
<tr><td align="right">Alliances</td>          <td>&nbsp;</td><td align="left">'.$alliances.'</td></tr>
<tr><td align="right">Highest Experience</td><td>&nbsp;</td><td align="left">'.$max_exp.'</td></tr>
<tr><td align="right">Highest Alignment</td> <td>&nbsp;</td><td align="left">'.$align.'</td></tr>
<tr><td align="right">Lowest Alignment</td><td>&nbsp;</td><td align="left">'.$align_low.'</td></tr>
<tr><td align="right">Highest Kills</td>     <td>&nbsp;</td><td align="left">'.$kills.'</td></tr>
</table>
</td>
</tr>
</table><br />';
$PHP_OUTPUT.=create_table();
$PHP_OUTPUT.='
<tr>
<td align="center">Top 10 Players in Experience</td>
<td align="center">Top 10 Players in Kills</td>
</tr>
<tr>
<td class="center" style="border:none">';
$rank = 0;
$db->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY experience DESC LIMIT 10');
if ($db->getNumRows() > 0) {
	$PHP_OUTPUT.=('<table class="nobord"><tr><th align="center">Rank</th><th align="center">Player</th><th align="center">Experience</th></tr>');
	while ($db->nextRecord()) {
		
		$exp = $db->getField('experience');
		$db_player =& SmrPlayer::getPlayer($db->getField('account_id'), $game_id);
		$PHP_OUTPUT.=('<tr><td align="center">' . ++$rank . '</td><td align="center">'.$db_player->getPlayerName().'</td><td align="center">'.$exp.'</td></tr>');
		
	}
	$PHP_OUTPUT.=('</table>');
	
}
$PHP_OUTPUT.='
</td><td align="center">';
$rank = 0;
$db->query('SELECT * FROM player WHERE game_id = '.$game_id.' ORDER BY kills DESC LIMIT 10');
if ($db->getNumRows() > 0) {
	$PHP_OUTPUT.=('<table class="nobord"><tr><th align="center">Rank</th><th align="center">Player</th><th align="center">Kills</th></tr>');
	while ($db->nextRecord()) {
		
		$kills = $db->getField('kills');
		$db_player =& SmrPlayer::getPlayer($db->getField('account_id'), $game_id);
		$PHP_OUTPUT.=('<tr><td align="center">' . ++$rank . '</td><td align="center">'.$db_player->getPlayerName().'</td><td align="center">'.$kills.'</td></tr>');
		
	}
	$PHP_OUTPUT.=('</table>');
	
}
$PHP_OUTPUT.='
</td>
</tr>
</table>';

$PHP_OUTPUT .='<h1>Current Players</h1>';

$db->query('SELECT * FROM active_session
			WHERE last_accessed >= ' . (TIME - 600) . ' AND
				  game_id = '.$game_id);
$count_real_last_active = $db->getNumRows();

$db->query('SELECT * FROM player ' .
		   'WHERE last_cpl_action >= ' . (TIME - 600) . ' AND ' .
				 'game_id = '.$game_id.' ' .
		   'ORDER BY experience DESC, player_name');
$count_last_active = $db->getNumRows();

// fix it if some1 is using the logoff button
if ($count_real_last_active < $count_last_active)
	$count_real_last_active = $count_last_active;

$PHP_OUTPUT.=('<p>There ');
if ($count_real_last_active != 1)
	$PHP_OUTPUT.=('are '.$count_real_last_active.' players who have ');
else
	$PHP_OUTPUT.=('is 1 player who has ');
$PHP_OUTPUT.=('accessed the server in the last 10 minutes.<br />');

if ($count_last_active == 0)
	$PHP_OUTPUT.=('Noone was moving so your ship computer can\'t intercept any transmissions.<br />');
else {

	if ($count_last_active == $count_real_last_active)
		$PHP_OUTPUT.=('All of them ');
	else
		$PHP_OUTPUT.=('A few of them ');

	$PHP_OUTPUT.=('were moving so your ship computer was able to intercept '.$count_last_active.' transmission');

	if ($count_last_active > 1)
		$PHP_OUTPUT.=('s.<br />');
	else
		$PHP_OUTPUT.=('.<br />');
}
	$PHP_OUTPUT.=('The traders listed in <span class="italic">italics</span> are still ranked as Newbie or Beginner.</p>');

$player =& SmrPlayer::getPlayer($account->account_id, $game_id);
if ($count_last_active > 0) {

	$PHP_OUTPUT.=('<table class="standard" width="95%">');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Player</th>');
	$PHP_OUTPUT.=('<th>Race</th>');
	$PHP_OUTPUT.=('<th>Alliance</th>');
	$PHP_OUTPUT.=('<th>Experience</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->nextRecord()) {

		$curr_account =& SmrAccount::getAccount($db->getField('account_id'));
		//reset style
		$style = '';
		$curr_player =& SmrPlayer::getPlayer($db->getField('account_id'), $game_id);

		if ($curr_account->isNewbie())
			$style = 'italic';
		if ($curr_player->getAccountID() == $account->account_id)
			$style .= ' bold';

		if (!empty($style))
			$style = ' class="'.$style.'"';

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td valign="top"'.$style.'>'.$curr_player->getLevelName().' ');
		$name = $curr_player->getDisplayName();
		$PHP_OUTPUT.=($name);
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td align="center"'.$style.'>');
		$race = $player->getColouredRaceName($curr_player->getRaceID());
		$PHP_OUTPUT.=($race);
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td'.$style.'>');
		if ($curr_player->getAllianceID() > 0) $PHP_OUTPUT.=($curr_player->getAllianceName());
		else $PHP_OUTPUT.=('(none)');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('<td align="right"'.$style.'>' . number_format($curr_player->getExperience()) . '</td>');
		$PHP_OUTPUT.=('</tr>');

	}

	$PHP_OUTPUT.=('	</table>');

}

$PHP_OUTPUT.=('</div>');

?>