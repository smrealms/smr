<?
$smarty->assign('PageTopic','TRADER STATUS');

include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_trader_menue();

$container=array();
$container['url'] = 'skeleton.php';

$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0" class="standard fullwidth"><tr><td style="width:50%" class="top">';

$PHP_OUTPUT.= '<span class="yellow bold">Protection</span><br>';
if($player->getNewbieTurns()) {
	$PHP_OUTPUT.= 'You are under <span class="green">NEWBIE</span> protection.<br><br>';

	$container['body'] = 'leave_newbie.php';
	$PHP_OUTPUT.=create_button($container, 'Leave Newbie Protection');
}
else if($player->isFedProtected()) {
	$PHP_OUTPUT.= 'You are under <span class="blue">FEDERAL</span> protection.';
}
else {
	$PHP_OUTPUT.= 'You are <span class="red">NOT</span> under protection.';
}

$PHP_OUTPUT.= '<br><br>';

$container['body'] = 'trader_relations.php';

$PHP_OUTPUT.=create_link($container, '<span class="yellow bold">Relations (Personal)</span>');

$db->query('SELECT
race.race_name as race_name,
player_has_relation.relation as relation
FROM player_has_relation,race
WHERE player_has_relation.game_id=' . SmrSession::$game_id . ' 
AND player_has_relation.account_id=' . SmrSession::$account_id . ' 
AND race.race_id=player_has_relation.race_id
ORDER BY race.race_id LIMIT 8');

$PHP_OUTPUT.= '<br>';
while($db->next_record()) {
	$PHP_OUTPUT.= $db->f('race_name');
	$PHP_OUTPUT.= ' : ';
	$PHP_OUTPUT.= get_colored_text($db->f('relation'), $db->f('relation'));
	$PHP_OUTPUT.= '<br>';
}
$PHP_OUTPUT.= '<br>';

$container['body'] = 'council_list.php';
$PHP_OUTPUT.=create_link($container, '<span class="yellow bold">Politics</span>');
$PHP_OUTPUT.= '<br>';

include(get_file_loc('council.inc'));

if(onCouncil($player->getRaceID())) {
	$president = getPresident($player->getRaceID());
	if($president && $president->getAccountID() == $player->getAccountID()) {
		$PHP_OUTPUT.= 'You are the <span class="red">President</span> of the ruling council.';
	}
	else{
		$PHP_OUTPUT.= 'You are a <span class="blue">member</span> of the ruling council.';
	}
}
else {
	$PHP_OUTPUT.= 'You are <span class="red">NOT</span> a member of the ruling council.';
}

$PHP_OUTPUT.= '<br><br>';

$container['body'] = 'trader_savings.php';
$PHP_OUTPUT.=create_link($container, '<span class="yellow bold">Savings</span>');
$PHP_OUTPUT.= '<br>You have <span class="yellow">';

$PHP_OUTPUT.= number_format($player->getBank());
$PHP_OUTPUT.= '</span> credits in your personal account.';

$PHP_OUTPUT.= '</td><td class="top" style="width:50%">';

$container['body'] = 'trader_bounties.php';
$PHP_OUTPUT.=create_link($container, '<span class="yellow bold">Bounties</span>');

// There should only ever be two outstanding bounties on anyone
$db->query('SELECT type,amount FROM bounty WHERE account_id=' . SmrSession::$account_id . ' AND claimer_id=0 AND game_id=' . SmrSession::$game_id . ' LIMIT 2');

$bounty= array(0,0);
while($db->next_record()) {
	if($db->f('type') == 'HQ') {
		$bounty[0] = $db->f('amount');
	}
	else {
		$bounty[1] = $db->f('amount');
	}
}
$PHP_OUTPUT.= '<br><span class="green">Federal: </span>';
if($bounty[0]) {
	$PHP_OUTPUT.= number_format($bounty[0]);
}
else {
	$PHP_OUTPUT.= 'None';
}
$PHP_OUTPUT.= '<br><span class="red">Underground: </span>';
if($bounty[1]) {
	$PHP_OUTPUT.= number_format($bounty[1]);
}
else {
	$PHP_OUTPUT.= 'None';
}

$PHP_OUTPUT.= '<br><br><span class="yellow bold">Ship</span><br>Name: ';

$PHP_OUTPUT.= $ship->getName();
$PHP_OUTPUT.= '<br>Speed: ';
$PHP_OUTPUT.= ($ship->getSpeed() * Globals::getGameSpeed($player->getGameID()));
$PHP_OUTPUT.= ' turns/hour<br>Max: ';
$PHP_OUTPUT.= $player->getMaxTurns();
$PHP_OUTPUT.= ' turns<br><br><span class="yellow bold">Supported Hardware</span><br>';

if ($ship->canHaveScanner()) $PHP_OUTPUT.= 'Scanner<br>';
if ($ship->canHaveIllusion()) $PHP_OUTPUT.= 'Illusion Generator<br>';
if ($ship->canHaveCloak()) $PHP_OUTPUT.= 'Cloaking Device<br>';
if ($ship->canHaveJump()) $PHP_OUTPUT.= 'Jump Drive<br>';
if ($ship->canHaveDCS()) $PHP_OUTPUT.= 'Drone Scrambler<br>';

if (!$ship->canHaveScanner() &&
    !$ship->canHaveIllusion() &&
    !$ship->canHaveCloak() &&
    !$ship->canHaveJump() &&
    !$ship->canHaveDCS()) $PHP_OUTPUT.= 'none<br>';

$PHP_OUTPUT.= '<br><span class="yellow bold">Next Level</span><br>';
$db->query('SELECT level_name,requirement FROM level WHERE requirement>' . $player->getExperience() . ' ORDER BY requirement ASC LIMIT 1');
$db->next_record();
$PHP_OUTPUT.= $db->f('level_name') . ': ' . number_format($db->f('requirement')) . 'xp';

$PHP_OUTPUT.= '<br><br>';
$container['body'] = 'rankings_view.php';
$PHP_OUTPUT.=create_link($container, '<span class="yellow bold">User Ranking</span>');

$rank_id = $account->get_rank();

$db->query('SELECT rankings_name FROM rankings WHERE rankings_id=' . $rank_id . ' LIMIT 1');
$db->next_record();
$PHP_OUTPUT.= '<br>You are ranked as a <span class="green">';
$PHP_OUTPUT.= $db->f('rankings_name');
$PHP_OUTPUT.= '</span> player.<br><br>';
$PHP_OUTPUT.= '</td></tr></table><br />';

$container = array();
$container['url'] = 'note_delete_processing.php';
$form = create_form($container,'Delete Selected');
$PHP_OUTPUT.= $form['form'];
$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0" class="standard fullwidth"><tr><th colspan="2">Notes</th></tr>';

$db->query('SELECT * FROM player_has_notes WHERE game_id=' . SmrSession::$game_id . ' AND account_id=' . SmrSession::$account_id . ' ORDER BY note_id desc');
if($db->nf() > 0) {
	while($db->next_record()) {
		$PHP_OUTPUT.= '<tr>';
		$PHP_OUTPUT.= '<td class="shrink"><input type="checkbox" name="note_id[]" value="' . $db->f('note_id') . '" /></td>';
		$PHP_OUTPUT.= '<td>' . gzuncompress($db->f('note')) . '</td>';
		$PHP_OUTPUT.= '</tr>';
	}
}

$PHP_OUTPUT.= '</table><br /><div align="center>">';
$PHP_OUTPUT.= $form['submit'];
$PHP_OUTPUT.= '</div></form><br />';

$container = array();
$container['url'] = 'note_add_processing.php';
$form = create_form($container,'New Note');
$PHP_OUTPUT.= $form['form'];
$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0" class="nobord nohpad">
	<tr>
		<td class="top">Note:&nbsp;</td>
		<td colspan="2"><textarea name="note"></textarea></td>
	</tr>
</table><br />';
$PHP_OUTPUT.= $form['submit'];
$PHP_OUTPUT.= '<small>&nbsp;&nbsp;&nbsp;Maximum note length is 1000 characters</small><br /></form>';



?>
