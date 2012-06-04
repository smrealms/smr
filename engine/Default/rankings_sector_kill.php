<?
require_once(get_file_loc('SmrSector.class.inc'));
$template->assign('PageTopic','SECTOR DEATH RANKINGS');

include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_ranking_menue(3,0);

$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<p>Here are the most deadly Sectors!</p>');
$PHP_OUTPUT.=('<table class="standard" width="60%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Sector</th>');
$PHP_OUTPUT.=('<th>Battles</th>');
$PHP_OUTPUT.=('</tr>');

$db->query('SELECT * FROM sector WHERE game_id = '.$player->getGameID().' ORDER BY battles DESC, sector_id LIMIT 10');

$rank = 0;
while ($db->nextRecord()) {

	// get current player
	$curr_sector =& SmrSector::getSector($player->getGameID(), $db->getField('sector_id'), $player->getAccountID());

	// increase rank counter
	$rank++;

	$PHP_OUTPUT.=('<tr>');

	$PHP_OUTPUT.=('<td valign="top" align="center"');
	if ($player->getSectorID() == $curr_sector->getSectorID())
		$PHP_OUTPUT.=(' style="font-weight:bold;"');
	$PHP_OUTPUT.=('>'.$rank.'</td>');

	$PHP_OUTPUT.=('<td valign="top" align="center"');
	if ($player->getSectorID() == $curr_sector->getSectorID())
		$PHP_OUTPUT.=(' style="font-weight:bold;"');
	$PHP_OUTPUT.=('>'.$curr_sector->getSectorID().'</td>');

	$PHP_OUTPUT.=('<td valign="top" align="center"');
	if ($player->getSectorID() == $curr_sector->getSectorID())
		$PHP_OUTPUT.=(' style="font-weight:bold;"');
	$PHP_OUTPUT.=('>' . number_format($curr_sector->getBattles()) . '</td>');

	$PHP_OUTPUT.=('</tr>');

}

$PHP_OUTPUT.=('</table>');
$action = $_REQUEST['action'];
if ($action == 'Show') {

	$min_rank = $_POST['min_rank'];
	$max_rank = $_POST['max_rank'];

} else {

	$min_rank = $our_rank - 5;
	$max_rank = $our_rank + 5;

}

if ($min_rank < 0) {

	$min_rank = 1;
	$max_rank = 10;

}

// how many alliances are there?
$db->query('SELECT max(sector_id) FROM sector WHERE game_id = '.$player->getGameID());
if ($db->nextRecord())
	$total_sector = $db->getField('max(sector_id)');

if ($max_rank > $total_sector)
	$max_rank = $total_sector;

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'rankings_sector_kill.php';
$container['min_rank']	= $min_rank;
$container['max_rank']	= $max_rank;

$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<p><input type="text" name="min_rank" value="'.$min_rank.'" size="3" id="InputFields" style="text-align:center;">&nbsp;-&nbsp;<input type="text" name="max_rank" value="'.$max_rank.'" size="3" id="InputFields" style="text-align:center;">&nbsp;');
$PHP_OUTPUT.=create_submit('Show');
$PHP_OUTPUT.=('</p></form>');
$PHP_OUTPUT.=('<table class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Sector</th>');
$PHP_OUTPUT.=('<th>Battles</th>');
$PHP_OUTPUT.=('</tr>');

$db->query('SELECT * FROM sector WHERE game_id = '.$player->getGameID().' ORDER BY battles DESC, sector_id LIMIT ' . ($min_rank - 1) . ', ' . ($max_rank - $min_rank + 1));

$rank = $min_rank - 1;
while ($db->nextRecord()) {

	// get current player
	$curr_sector =& SmrSector::getSector($player->getGameID(), $db->getField('sector_id'), $player->getAccountID());

	// increase rank counter
	$rank++;

	$PHP_OUTPUT.=('<tr>');

	$PHP_OUTPUT.=('<td valign="top" align="center"');
	if ($player->getSectorID() == $curr_sector->getSectorID())
		$PHP_OUTPUT.=(' style="font-weight:bold;"');
	$PHP_OUTPUT.=('>'.$rank.'</td>');

	$PHP_OUTPUT.=('<td valign="top" align="center"');
	if ($player->getSectorID() == $curr_sector->getSectorID())
		$PHP_OUTPUT.=(' style="font-weight:bold;"');
	$PHP_OUTPUT.=('>'.$curr_sector->getSectorID().'</td>');

	$PHP_OUTPUT.=('<td valign="top" align="center"');
	if ($player->getSectorID() == $curr_sector->getSectorID())
		$PHP_OUTPUT.=(' style="font-weight:bold;"');
	$PHP_OUTPUT.=('>' . number_format($curr_sector->getBattles()) . '</td>');

	$PHP_OUTPUT.=('</tr>');

}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');

?>