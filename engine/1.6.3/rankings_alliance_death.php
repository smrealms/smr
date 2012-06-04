<?php
$template->assign('PageTopic','Alliance Death Rankings');
include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_ranking_menue(1, 2);

$db->query('SELECT alliance_id, alliance_name, alliance_deaths, leader_id FROM alliance
			WHERE game_id = '.$player->getGameID().' ORDER BY alliance_deaths DESC, alliance_name');
$alliances = array();
while ($db->nextRecord())
{
	$alliances[$db->getField('alliance_id')] = array(stripslashes($db->getField('alliance_name')), $db->getField('alliance_deaths'), $db->getField('leader_id'));
	if ($db->getField('alliance_id') == $player->getAllianceID()) $ourRank = sizeof($alliances);
}

// how many alliances are there?
$numAlliances = sizeof($alliances);

$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<p>Here are the rankings of alliances by their deaths.</p>');
if ($player->getAllianceID() > 0)
    $PHP_OUTPUT.=('<p>Your alliance is ranked '.$ourRank.' out of '.$numAlliances.' alliances.</p>');

$PHP_OUTPUT.=('<table class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Alliance</th>');
$PHP_OUTPUT.=('<th>Deaths</th>');
$PHP_OUTPUT.=('</tr>');

$rank = 0;
foreach($alliances as $id => $infoArray)
{
    // get current alliance
    $currAllianceName = $infoArray[0];
    $numDeaths = $infoArray[1];
    $out = (!$infoArray[2]);
	$rank++;
	if ($rank > 10) break;
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td valign="top" align="center"');
	if ($player->getAllianceID() == $id)
	    $PHP_OUTPUT.=(' style="font-weight:bold;"');
	elseif ($out)
		$PHP_OUTPUT.=(' style="color:red;"');
	$PHP_OUTPUT.=('>'.$rank.'</td>');
	
	$PHP_OUTPUT.=('<td valign="top"');
	if ($player->getAllianceID() == $id)
	    $PHP_OUTPUT.=(' style="font-weight:bold;"');
	elseif ($out)
		$PHP_OUTPUT.=(' style="color:red;"');
	$PHP_OUTPUT.=('>');
	$container = create_container('skeleton.php','alliance_roster.php');
	$container['alliance_id']    = $id;
	if ($out)
		$PHP_OUTPUT.=($currAllianceName);
	else
		$PHP_OUTPUT.=create_link($container, $currAllianceName);
	$PHP_OUTPUT.=('</td>');
	
	$PHP_OUTPUT.=('<td valign="top" align="right"');
	if ($player->getAllianceID() == $id)
	    $PHP_OUTPUT.=(' style="font-weight:bold;"');
	if ($out)
		$PHP_OUTPUT.=(' style="color:red;"');
	$PHP_OUTPUT.=('>' . number_format($numDeaths) . '</td>');
	
	$PHP_OUTPUT.=('</tr>');

}
$PHP_OUTPUT.=('</table>');

$action = $_REQUEST['action'];
if ($action == 'Show')
{
    $min_rank = min($_POST['min_rank'], $_POST['max_rank']);
    $max_rank = max($_POST['min_rank'], $_POST['max_rank']);
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

$container = create_container('skeleton.php','rankings_alliance_death.php');
$container['min_rank']    = $min_rank;
$container['max_rank']    = $max_rank;

$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<p><input type="text" name="min_rank" value="'.$min_rank.'" size="3" id="InputFields" style="text-align:center;">&nbsp;-&nbsp;<input type="text" name="max_rank" value="'.$max_rank.'" size="3" id="InputFields" style="text-align:center;">&nbsp;');
$PHP_OUTPUT.=create_submit('Show');
$PHP_OUTPUT.=('</p></form>');
$PHP_OUTPUT.=('<table class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Alliance</th>');
$PHP_OUTPUT.=('<th>Deaths</th>');
$PHP_OUTPUT.=('</tr>');

$rank=0;
foreach ($alliances as $id => $infoArray)
{
	$rank++;
	if ($rank < $min_rank) continue;
	elseif ($rank > $max_rank) break;
	// get current alliance
	$currAllianceName = $infoArray[0];
	$numDeaths = $infoArray[1];
	$out = (!$infoArray[2]);
	
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td valign="top" align="center"');
	if ($player->getAllianceID() == $id)
		$PHP_OUTPUT.=(' style="font-weight:bold;"');
	elseif ($out)
		$PHP_OUTPUT.=(' style="color:red;"');
	$PHP_OUTPUT.=('>'.$rank.'</td>');

	$PHP_OUTPUT.=('<td valign="top"');
	if ($player->getAllianceID() == $id)
		$PHP_OUTPUT.=(' style="font-weight:bold;"');
	elseif ($out)
		$PHP_OUTPUT.=(' style="color:red;"');
	$PHP_OUTPUT.=('>');
	$container = create_container('skeleton.php','alliance_roster.php');
	$container['alliance_id']    = $id;
	if ($out)
		$PHP_OUTPUT.=($currAllianceName);
	else
		$PHP_OUTPUT.=create_link($container, $currAllianceName);
	$PHP_OUTPUT.=('</td>');
	
	$PHP_OUTPUT.=('<td valign="top" align="right"');
	if ($player->getAllianceID() == $id)
		$PHP_OUTPUT.=(' style="font-weight:bold;"');
	if ($out)
		$PHP_OUTPUT.=(' style="color:red;"');
	$PHP_OUTPUT.=('>' . number_format($numDeaths) . '</td>');
	$PHP_OUTPUT.=('</tr>');
}
$PHP_OUTPUT.=('</table>');

$PHP_OUTPUT.=('</div>');

?>