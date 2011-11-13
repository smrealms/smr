<?php
$template->assign('PageTopic','Trader Status');

require_once(get_file_loc('menu.inc'));
create_trader_menu();

$container=array();
$container['url'] = 'skeleton.php';

$PHP_OUTPUT.= '<table class="standard fullwidth"><tr><td style="width:50%" class="top">';

$PHP_OUTPUT.= '<span class="yellow bold">Protection</span><br />';
if($player->getNewbieTurns())
{
	$PHP_OUTPUT.= 'You are under <span class="green">NEWBIE</span> protection.<br /><br />';

	$container['body'] = 'leave_newbie.php';
	$PHP_OUTPUT.=create_button($container, 'Leave Newbie Protection');
}
else if($player->hasFederalProtection())
{
	$PHP_OUTPUT.= 'You are under <span class="blue">FEDERAL</span> protection.';
}
else
{
	$PHP_OUTPUT.= 'You are <span class="red">NOT</span> under protection.';
}

$PHP_OUTPUT.= '<br /><br />';

$container['body'] = 'trader_relations.php';

$PHP_OUTPUT.=create_link($container, '<span class="yellow bold">Relations (Personal)</span>');

$PHP_OUTPUT.= '<br />';
$RACES = Globals::getRaces();
foreach($RACES as $raceID => $raceInfo)
{
	if($player->getPureRelation($raceID) != 0)
		$PHP_OUTPUT.= $raceInfo['Race Name'] . ' : ' . get_colored_text($player->getPureRelation($raceID), $player->getPureRelation($raceID)) . '<br />';
}
$PHP_OUTPUT.= '<br />';

$container['body'] = 'council_list.php';
$PHP_OUTPUT.=create_link($container, '<span class="yellow bold">Politics</span>');
$PHP_OUTPUT.= '<br />';

require_once(get_file_loc('council.inc'));

if($player->isOnCouncil())
{
	if($player->isPresident())
	{
		$PHP_OUTPUT.= 'You are the <span class="red">President</span> of the ruling council.';
	}
	else
	{
		$PHP_OUTPUT.= 'You are a <span class="blue">member</span> of the ruling council.';
	}
}
else
{
	$PHP_OUTPUT.= 'You are <span class="red">NOT</span> a member of the ruling council.';
}

$PHP_OUTPUT.= '<br /><br />';

$container['body'] = 'trader_savings.php';
$PHP_OUTPUT.=create_link($container, '<span class="yellow bold">Savings</span>');
$PHP_OUTPUT.= '<br />You have <span class="yellow">';

$PHP_OUTPUT.= number_format($player->getBank());
$PHP_OUTPUT.= '</span> credits in your personal account.';

$PHP_OUTPUT.= '</td><td class="top" style="width:50%">';

$container['body'] = 'trader_bounties.php';
$PHP_OUTPUT.=create_link($container, '<span class="yellow bold">Bounties</span>');

$PHP_OUTPUT.= '<br /><span class="green">Federal: </span>';
$bounty = $player->getCurrentBounty('HQ');
if($bounty['Amount']>0||$bounty['SmrCredits']>0)
	$PHP_OUTPUT.= number_format($bounty['Amount']).' credits and '.number_format($bounty['SmrCredits']).' SMR credits';
else
	$PHP_OUTPUT.= 'None';
$PHP_OUTPUT.= '<br /><span class="red">Underground: </span>';
$bounty = $player->getCurrentBounty('UG');
if($bounty['Amount']>0||$bounty['SmrCredits']>0)
	$PHP_OUTPUT.= number_format($bounty['Amount']).' credits and '.number_format($bounty['SmrCredits']).' SMR credits';
else
	$PHP_OUTPUT.= 'None';

$PHP_OUTPUT.= '<br /><br /><span class="yellow bold">Ship</span><br />Name: ';

$PHP_OUTPUT.= $ship->getName();
$PHP_OUTPUT.= '<br />Speed: ';
$PHP_OUTPUT.= $ship->getRealSpeed();
$PHP_OUTPUT.= ' turns/hour<br />Max: ';
$PHP_OUTPUT.= $player->getMaxTurns();
$PHP_OUTPUT.= ' turns<br /><br /><span class="yellow bold">Supported Hardware</span><br />';

if (!$ship->canHaveScanner() &&
	!$ship->canHaveIllusion() &&
	!$ship->canHaveCloak() &&
	!$ship->canHaveJump() &&
	!$ship->canHaveDCS()) $PHP_OUTPUT.= 'none<br />';
else
{
	if ($ship->canHaveScanner()) $PHP_OUTPUT.= 'Scanner<br />';
	if ($ship->canHaveIllusion()) $PHP_OUTPUT.= 'Illusion Generator<br />';
	if ($ship->canHaveCloak()) $PHP_OUTPUT.= 'Cloaking Device<br />';
	if ($ship->canHaveJump()) $PHP_OUTPUT.= 'Jump Drive<br />';
	if ($ship->canHaveDCS()) $PHP_OUTPUT.= 'Drone Scrambler<br />';
}

$PHP_OUTPUT.= '<br /><a href="'.URL.'/level_requirements.php" target="levelRequirements"><span class="yellow bold">Next Level</span></a><br />';
$db->query('SELECT level_name,requirement FROM level WHERE requirement>' . $player->getExperience() . ' ORDER BY requirement ASC LIMIT 1');
if(!$db->nextRecord())
{
	$db->query('SELECT level_name,requirement FROM level ORDER BY requirement DESC LIMIT 1');
	$db->nextRecord();
}
$PHP_OUTPUT.= $db->getField('level_name') . ': ' . number_format($db->getInt('requirement')) . 'xp';

$PHP_OUTPUT.= '<br /><br />';
$container['body'] = 'rankings_view.php';
$PHP_OUTPUT.=create_link($container, '<span class="yellow bold">User Ranking</span>');

$PHP_OUTPUT.= '<br />You are ranked as a <span class="green">';
$PHP_OUTPUT.= $account->getRankName();
$PHP_OUTPUT.= '</span> player.<br /><br />';
$PHP_OUTPUT.= '</td></tr></table><br />';

$container = array();
$container['url'] = 'note_delete_processing.php';
$form = create_form($container,'Delete Selected');
$PHP_OUTPUT.= $form['form'];
$PHP_OUTPUT.= '<table class="standard fullwidth"><tr><th colspan="2">Notes</th></tr>';

$db->query('SELECT * FROM player_has_notes WHERE game_id=' . $player->getGameID() . ' AND account_id=' . $player->getAccountID() . ' ORDER BY note_id DESC');
if($db->getNumRows() > 0)
{
	while($db->nextRecord())
	{
		$PHP_OUTPUT.= '<tr>';
		$PHP_OUTPUT.= '<td class="shrink"><input type="checkbox" name="note_id[]" value="' . $db->getField('note_id') . '" /></td>';
		$PHP_OUTPUT.= '<td>' . bbifyMessage(gzuncompress($db->getField('note'))) . '</td>';
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