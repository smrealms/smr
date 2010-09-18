<?php
$template->assign('PageTopic','Alliance Experience Rankings');
include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_ranking_menue(1, 0);

$db->query('SELECT alliance_id, sum( experience ) AS alliance_exp, count( account_id ) AS members, alliance_name AS name
				FROM alliance
				LEFT JOIN player USING (game_id,alliance_id)
				WHERE alliance.game_id = ' . $player->getGameID() . ' 
				GROUP BY alliance_id
				ORDER BY alliance_exp DESC');
$alliances = array();
while ($db->nextRecord())
{
	$alliances[$db->getField('alliance_id')] = array(stripslashes($db->getField('name')), $db->getField('alliance_exp'), $db->getField('members'));
	if ($db->getField('alliance_id') == $player->getAllianceID()) $ourRank = sizeof($alliances);
}
// how many alliances are there?
$numAlliances = sizeof($alliances);

$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<p>Here are the rankings of alliances by their experience.</p>');
if ($player->hasAlliance())
	$PHP_OUTPUT.=('<p>Your alliance is ranked '.$ourRank.' out of '.$numAlliances.' alliances.</p>');

$PHP_OUTPUT.=('<table class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Alliance</th>');
$PHP_OUTPUT.=('<th>Total Experience</th>');
$PHP_OUTPUT.=('<th>Average Experience</th>');
$PHP_OUTPUT.=('<th>Total Traders</th>');
$PHP_OUTPUT.=('</tr>');

$rank = 0;
foreach ($alliances as $id => $infoArray)
{
	$rank++;
	$currAllianceName = $infoArray[0];
	$totalExp = $infoArray[1];
	$members = $infoArray[2];
	if ($rank > 10) break;
	$PHP_OUTPUT.=('<tr>');
	$style = 'style="vertical-align:top;text-align:center;"';
	$style2 = '';
	if($player->getAllianceID() == $id)
		$style2 .= ' class="bold"';
	elseif (!$members)
		$style2.=' class="red"';
	$style .= $style2;

	$PHP_OUTPUT.=('<td '.$style.'>'.$rank.'</td>');

	$PHP_OUTPUT.= '<td style="vertical-align:top;"' . $style2 . '>';
	$container = create_container('skeleton.php','alliance_roster.php');
	$container['alliance_id']	= $id;
	if ($members)
		$PHP_OUTPUT.=create_link($container, $currAllianceName);
	else
		$PHP_OUTPUT.= $currAllianceName;
	$PHP_OUTPUT.=('</td>');

	$PHP_OUTPUT.=('<td '.$style.'>' . number_format($totalExp) . '</td>');
	$PHP_OUTPUT.=('<td '.$style.'>' . number_format(round($totalExp / max($members,1))) . '</td>');
	$PHP_OUTPUT.=('<td '.$style.'>' . $members . '</td>');
	$PHP_OUTPUT.=('</tr>');
}
$PHP_OUTPUT.=('</table>');

$action = $_REQUEST['action'];
if ($action == 'Show')
{
    $min_rank = min($_REQUEST['min_rank'], $_REQUEST['max_rank']);
    $max_rank = max($_REQUEST['min_rank'], $_REQUEST['max_rank']);
	SmrSession::updateVar('MinRank',$min_rank);
	SmrSession::updateVar('MaxRank',$max_rank);
}
elseif(isset($var['MinRank'])&&isset($var['MaxRank']))
{
    $min_rank = $var['MinRank'];
    $max_rank = $var['MaxRank'];
}
else
{
    $min_rank = $ourRank - 5;
    $max_rank = $ourRank + 5;
}
if ($min_rank <= 0)
{
    $min_rank = 1;
    $max_rank = 10;
}
if ($max_rank > $numAlliances)
    $max_rank = $numAlliances;

$container = create_container('skeleton.php','rankings_alliance_experience.php');
$container['min_rank']	= $min_rank;
$container['max_rank']	= $max_rank;

$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<p><input type="text" name="min_rank" value="'.$min_rank.'" size="3" id="InputFields" style="text-align:center;">&nbsp;-&nbsp;<input type="text" name="max_rank" value="'.$max_rank.'" size="3" id="InputFields" style="text-align:center;">&nbsp;');
$PHP_OUTPUT.=create_submit('Show');
$PHP_OUTPUT.=('</p></form>');

$PHP_OUTPUT.=('<table class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Alliance</th>');
$PHP_OUTPUT.=('<th>Total Experience</th>');
$PHP_OUTPUT.=('<th>Average Experience</th>');
$PHP_OUTPUT.=('<th>Total Traders</th>');
$PHP_OUTPUT.=('</tr>');

$rank = 0;
foreach ($alliances as $id => $infoArray)
{
	$rank++;
	if ($rank < $min_rank) continue;
	elseif ($rank > $max_rank) break;
	$currAllianceName = $infoArray[0];
	$totalExp = $infoArray[1];
	$members = $infoArray[2];
	
	$PHP_OUTPUT.=('<tr>');
	$style = 'style="vertical-align:top;text-align:center;"';
	$style2 = '';
	if($player->getAllianceID() == $id)
		$style2 .= ' class="bold"';
	else if (!$members)
		$style2.=(' class="red"');
	$style .= $style2;

	$PHP_OUTPUT.=('<td '.$style.'>'.$rank.'</td>');

	$PHP_OUTPUT.= '<td style="vertical-align:top;"' . $style2 . '>';
	$container = create_container('skeleton.php','alliance_roster.php');
	$container['alliance_id']	= $id;
	if ($members)
		$PHP_OUTPUT.=create_link($container, $currAllianceName);
	else
		$PHP_OUTPUT.= $currAllianceName;
	$PHP_OUTPUT.=('</td>');

	$PHP_OUTPUT.=('<td '.$style.'>' . number_format($totalExp) . '</td>');
	$PHP_OUTPUT.=('<td '.$style.'>' . number_format(round($totalExp / max($members,1))) . '</td>');
	$PHP_OUTPUT.=('<td '.$style.'>' . $members . '</td>');
	$PHP_OUTPUT.=('</tr>');

}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');

?>