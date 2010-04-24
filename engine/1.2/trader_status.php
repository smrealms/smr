<?php
print_topic("TRADER STATUS");

include(get_file_loc('menue.inc'));
print_trader_menue();

$container=array();
$container['url'] = 'skeleton.php';

echo '<table cellspacing="0" cellpadding="0" class="standard fullwidth"><tr><td style="width:50%" class="top">';

echo '<span class="yellow bold">Protection</span><br>';
if($player->newbie_turns) {
	echo 'You are under <span class="green">NEWBIE</span> protection.<br><br>';

	$container['body'] = 'leave_newbie.php';
	print_button($container, 'Leave Newbie Protection');
}
else if($player->is_fed_protected()) {
	echo 'You are under <span class="blue">FEDERAL</span> protection.';
}
else {
	echo 'You are <span class="red">NOT</span> under protection.';
}

echo '<br><br>';

$container['body'] = 'trader_relations.php';

print_link($container, '<span class="yellow bold">Relations (Personal)</span>');

$db->query('SELECT
race.race_name as race_name,
player_has_relation.relation as relation
FROM player_has_relation,race
WHERE player_has_relation.game_id=' . SmrSession::$game_id . ' 
AND player_has_relation.account_id=' . SmrSession::$old_account_id . ' 
AND race.race_id=player_has_relation.race_id
ORDER BY race.race_id LIMIT 8');

echo '<br>';
while($db->next_record()) {
	echo $db->f('race_name');
	echo ' : ';
	echo get_colored_text($db->f('relation'), $db->f('relation'));
	echo '<br>';
}
echo '<br>';

$container['body'] = 'council_list.php';
print_link($container, '<span class="yellow bold">Politics</span>');
echo '<br>';

include(get_file_loc("council.inc"));

if(onCouncil($player->race_id)) {
	$president = getPresident($player->race_id);
	if($president->account_id == $player->account_id) {
		echo 'You are the <span class="red">President</span> of the ruling council.';
	}
	else{
		echo 'You are a <span class="blue">member</span> of the ruling council.';
	}
}
else {
	echo 'You are <span class="red">NOT</span> a member of the ruling council.';
}

echo '<br><br>';

$container['body'] = 'trader_savings.php';
print_link($container, '<span class="yellow bold">Savings</span>');
echo '<br>You have <span class="yellow">';

echo number_format($player->bank);
echo '</span> credits in your personal account.';

echo '</td><td class="top" style="width:50%">';

$container['body'] = 'trader_bounties.php';
print_link($container, '<span class="yellow bold">Bounties</span>');

// There should only ever be two outstanding bounties on anyone
$db->query('SELECT type,amount FROM bounty WHERE account_id=' . SmrSession::$old_account_id . ' AND claimer_id=0 AND game_id=' . SmrSession::$game_id . ' LIMIT 2');

$bounty= array(0,0);
while($db->next_record()) {
	if($db->f('type') == 'HQ') {
		$bounty[0] = $db->f('amount');
	}
	else {
		$bounty[1] = $db->f('amount');
	}
}
echo '<br><span class="green">Federal: </span>';
if($bounty[0]) {
	echo number_format($bounty[0]);
}
else {
	echo 'None';
}
echo '<br><span class="red">Underground: </span>';
if($bounty[1]) {
	echo number_format($bounty[1]);
}
else {
	echo 'None';
}

echo '<br><br><span class="yellow bold">Ship</span><br>Name: ';

echo $ship->ship_name;
echo '<br>Speed: ';
echo ($ship->speed * $player->game_speed);
echo ' turns/hour<br>Max: ';
echo (400 * $player->game_speed);
echo ' turns<br><br><span class="yellow bold">Supported Hardware</span><br>';

if (!empty($ship->max_hardware[7])) echo 'Scanner<br>';
if (!empty($ship->max_hardware[9])) echo 'Illusion Generator<br>';
if (!empty($ship->max_hardware[8])) echo 'Cloaking Device<br>';
if (!empty($ship->max_hardware[10])) echo 'Jump Drive<br>';
if (!empty($ship->max_hardware[11])) echo 'Drone Scrambler<br>';

if (empty($ship->max_hardware[7]) &&
    empty($ship->max_hardware[9]) &&
    empty($ship->max_hardware[8]) &&
    empty($ship->max_hardware[10]) &&
    empty($ship->max_hardware[11])) echo 'none<br>';

echo '<br><span class="yellow bold">Next Level</span><br>';
$db->query('SELECT level_name,requirement FROM level WHERE requirement>' . $player->experience . ' ORDER BY requirement ASC LIMIT 1');
$db->next_record();
echo $db->f('level_name') . ': ' . number_format($db->f('requirement')) . 'xp';

echo '<br><br>';
$container['body'] = 'rankings_view.php';
print_link($container, '<span class="yellow bold">User Ranking</span>');

$rank_id = $account->get_rank();

$db->query('SELECT rankings_name FROM rankings WHERE rankings_id=' . $rank_id . ' LIMIT 1');
$db->next_record();
echo '<br>You are ranked as a <span class="green">';
echo $db->f('rankings_name');
echo '</span> player.<br><br>';
echo '</td></tr></table><br />';

$container = array();
$container['url'] = 'note_delete_processing.php';
$form = create_form($container,'Delete Selected');
echo $form['form'];
echo '<table cellspacing="0" cellpadding="0" class="standard fullwidth"><tr><th colspan="2">Notes</th></tr>';

$db->query('SELECT * FROM player_has_notes WHERE game_id=' . SmrSession::$game_id . ' AND account_id=' . SmrSession::$old_account_id . ' ORDER BY note_id desc');
if($db->nf() > 0) {
	while($db->next_record()) {
		echo '<tr>';
		echo '<td class="shrink"><input type="checkbox" name="note_id[]" value="' . $db->f('note_id') . '" /></td>';
		echo '<td>' . gzuncompress($db->f('note')) . '</td>';
		echo '</tr>';
	}
}

echo '</table><br /><div align="center>">';
echo $form['submit'];
echo '</div></form><br />';

$container = array();
$container['url'] = 'note_add_processing.php';
$form = create_form($container,'New Note');
echo $form['form'];
echo '<table cellspacing="0" cellpadding="0" class="nobord nohpad">
	<tr>
		<td class="top">Note:&nbsp;</td>
		<td colspan="2"><textarea name="note"></textarea></td>
	</tr>
</table><br />';
echo $form['submit'];
echo '<small>&nbsp;&nbsp;&nbsp;Maximum note length is 1000 characters</small><br /></form>';



?>
