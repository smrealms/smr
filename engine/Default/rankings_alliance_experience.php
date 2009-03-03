<?
$template->assign('PageTopic','ALLIANCE EXPERIENCE RANKINGS');
include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_ranking_menue(1, 0);

$db->query('SELECT player.alliance_id as alliance_id, sum( experience ) AS alliance_exp, count( * ) AS members, alliance_name AS name
				FROM player, alliance
				WHERE player.game_id = ' . $player->getGameID() . ' 
				AND player.game_id = alliance.game_id
				AND alliance.alliance_id = player.alliance_id
				GROUP BY player.alliance_id
				ORDER BY alliance_exp DESC');
$alliances = array();
while ($db->nextRecord()) {
	$alliances[$db->getField('alliance_id')] = array(stripslashes($db->getField('name')), $db->getField('alliance_exp'), $db->getField('members'));
	if ($db->getField('alliance_id') == $player->getAllianceID()) $ourRank = sizeof($alliances);
}
// how many alliances are there?
$numAlliances = sizeof($alliances);

$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<p>Here are the rankings of alliances by their experience.</p>');
if ($player->getAllianceID() > 0)
	$PHP_OUTPUT.=('<p>Your alliance is ranked '.$ourRank.' out of '.$numAlliances.' alliances.</p>');

$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="5" border="0" class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Alliance</th>');
$PHP_OUTPUT.=('<th>Total Experience</th>');
$PHP_OUTPUT.=('<th>Average Experience</th>');
$PHP_OUTPUT.=('<th>Total Traders</th>');
$PHP_OUTPUT.=('</tr>');

$rank = 0;
foreach ($alliances as $id => $infoArray) {
	$rank++;
	$currAllianceName = $infoArray[0];
	$totalExp = $infoArray[1];
	$members = $infoArray[2];
	if ($rank > 10) break;
	$PHP_OUTPUT.=('<tr>');
	$style = 'style="vertical-align:top;text-align:center;';
	$style2 = '';
	if($player->getAllianceID() == $id)
		$style2 .= 'font-weight:bold;';
	$style .= $style2 . '"';

	$PHP_OUTPUT.=('<td '.$style.'>'.$rank.'</td>');

	$PHP_OUTPUT.= '<td style="vertical-align:top;' . $style2 . '">';
	$container = create_container('skeleton.php','alliance_roster.php');
	$container['alliance_id']	= $id;
	$PHP_OUTPUT.=create_link($container, $currAllianceName);
	$PHP_OUTPUT.=('</td>');

	$PHP_OUTPUT.=('<td '.$style.'>' . number_format($totalExp) . '</td>');
	$PHP_OUTPUT.=('<td '.$style.'>' . number_format(round($totalExp / $members)) . '</td>');
	$PHP_OUTPUT.=('<td '.$style.'>' . $members . '</td>');
	$PHP_OUTPUT.=('</tr>');
}
$PHP_OUTPUT.=('</table>');

$action = $_REQUEST['action'];
if ($action == 'Show') {
    $min_rank = $_POST['min_rank'];
    $max_rank = $_POST['max_rank'];
} else {
    $min_rank = $ourRank - 5;
    $max_rank = $ourRank + 5;
}
if ($min_rank <= 0) {
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

$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="5" border="0" class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Alliance</th>');
$PHP_OUTPUT.=('<th>Total Experience</th>');
$PHP_OUTPUT.=('<th>Average Experience</th>');
$PHP_OUTPUT.=('<th>Total Traders</th>');
$PHP_OUTPUT.=('</tr>');

$rank = 0;
foreach ($alliances as $id => $infoArray) {
	$rank++;
	if ($rank < $min_rank) continue;
	elseif ($rank > $max_rank) break;
	$currAllianceName = $infoArray[0];
	$totalExp = $infoArray[1];
	$members = $infoArray[2];
	
	$PHP_OUTPUT.=('<tr>');
	$style = 'style="vertical-align:top;text-align:center;';
	$style2 = '';
	if($player->getAllianceID() == $id)
		$style2 .= 'font-weight:bold;';
	$style .= $style2 . '"';

	$PHP_OUTPUT.=('<td '.$style.'>'.$rank.'</td>');

	$PHP_OUTPUT.= '<td style="vertical-align:top;' . $style2 . '">';
	$container = create_container('skeleton.php','alliance_roster.php');
	$container['alliance_id']	= $id;
	$PHP_OUTPUT.=create_link($container, $currAllianceName);
	$PHP_OUTPUT.=('</td>');

	$PHP_OUTPUT.=('<td '.$style.'>' . number_format($totalExp) . '</td>');
	$PHP_OUTPUT.=('<td '.$style.'>' . number_format(round($totalExp / $members)) . '</td>');
	$PHP_OUTPUT.=('<td '.$style.'>' . $members . '</td>');
	$PHP_OUTPUT.=('</tr>');

}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');

?>