<?php
require_once(get_file_loc('SmrAlliance.class.inc'));
$template->assign('PageTopic','Alliance VS Alliance Rankings');

require_once(get_file_loc('menu.inc'));
create_ranking_menu(1, 4);
$db2 = new SmrMySqlDatabase();
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'rankings_alliance_vs_alliance.php';

$PHP_OUTPUT.=create_echo_form($container);

if (isset($_REQUEST['alliancer'])) {
	SmrSession::updateVar('alliancer',$_REQUEST['alliancer']);
	$alliancer = $var['alliancer'];
}

$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<p>Here are the rankings of alliances vs other alliances<br />');
$PHP_OUTPUT.=('Click on an alliances name for more detailed death stats.</p>');

$PHP_OUTPUT.=('<table class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th rowspan="9">Killed</th><th colspan="8">Killers</th></tr><tr><td></td>');
if (empty($alliancer)) {
	$alliance_vs = array();
	$db->query('SELECT * FROM alliance WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY alliance_kills DESC, alliance_name LIMIT 5');
	while ($db->nextRecord()) $alliance_vs[] = $db->getField('alliance_id');
	//$PHP_OUTPUT.=('empty '.$alliancer);

} else $alliance_vs = $alliancer;
$alliance_vs[] = 0;

foreach ($alliance_vs as $key => $id) {
	// get current alliance
	$curr_alliance_id = $id;
	if ($id > 0) {
		$db->query('SELECT 1 FROM player WHERE alliance_id = ' . $db->escapeNumber($id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
		$out = $db2->getNumRows() == 0;

		$PHP_OUTPUT.=('<td width=15% valign="top"');
		if ($player->getAllianceID() == $curr_alliance_id)
			$PHP_OUTPUT.=(' class="bold"');
		$PHP_OUTPUT.=('>');
		/*$container = array();
		$container['url']			= 'skeleton.php';
		$container['body']			= 'alliance_roster.php';
		$container['alliance_id']	= $curr_alliance_id;
		$PHP_OUTPUT.=create_link($container, '.$db->escapeString($curr_alliance->getAllianceName()');*/
		$PHP_OUTPUT.=('<select name="alliancer[]" id="InputFields" style="width:105">');
		$db->query('SELECT * FROM alliance WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND (alliance_deaths > 0 OR alliance_kills > 0) ORDER BY alliance_name');
		while ($db->nextRecord()) {
			$curr_alliance =& SmrAlliance::getAlliance($db->getField('alliance_id'), $player->getGameID());
			$PHP_OUTPUT.=('<option value=' . $db->getField('alliance_id'));
			if ($id == $db->getField('alliance_id'))
				$PHP_OUTPUT.=(' selected');
			$PHP_OUTPUT.=('>' . $curr_alliance->getAllianceName() . '</option>');
		}
		$PHP_OUTPUT.='</select>';
		$PHP_OUTPUT.=('</td>');
	}
	//$alliance_vs[] = $curr_alliance_id;
}
$PHP_OUTPUT.=('<td width=10% valign="top">None</td>');
$PHP_OUTPUT.=('</tr>');
//$db->query('SELECT * FROM alliance WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY alliance_kills DESC, alliance_name LIMIT 5');
foreach ($alliance_vs as $key => $id) {
	$PHP_OUTPUT.=('<tr>');
	// get current alliance
	$curr_id = $id;
	if ($id > 0) {
		$curr_alliance =& SmrAlliance::getAlliance($id, $player->getGameID());
		$db2->query('SELECT 1 FROM player WHERE alliance_id = ' . $db2->escapeNumber($curr_id) . ' AND game_id = ' . $db2->escapeNumber($player->getGameID()) . ' LIMIT 1');
		$out = $db2->nextRecord();

		$PHP_OUTPUT.=('<td width=10% valign="top"');
		if ($player->getAllianceID() == $curr_alliance->getAllianceID())
			$PHP_OUTPUT.=(' class="bold"');
		if ($out)
			$PHP_OUTPUT.=(' class="red"');
		$PHP_OUTPUT.=('>');
		$container1 = array();
		$container1['url']			= 'skeleton.php';
		$container1['body']		= 'rankings_alliance_vs_alliance.php';
		$container1['alliance_id']	= $curr_alliance->getAllianceID();
		$PHP_OUTPUT.=create_link($container1, $curr_alliance->getAllianceName());
		//$PHP_OUTPUT.=('.$db->escapeString($curr_alliance->getAllianceName()');
		$PHP_OUTPUT.=('</td>');
	}
	else {
		$container1 = array();
		$container1['url']			= 'skeleton.php';
		$container1['body']		= 'rankings_alliance_vs_alliance.php';
		$container1['alliance_id']	= 0;
		$PHP_OUTPUT.=('<td width=10% valign="top">');
		$PHP_OUTPUT.=create_link($container1, 'None');
		$PHP_OUTPUT.=('</td>');
	}

	foreach ($alliance_vs as $key => $id) {
		$db2->query('SELECT 1 FROM player WHERE alliance_id = ' . $db2->escapeNumber($id) . ' AND game_id = ' . $db2->escapeNumber($player->getGameID()) . ' LIMIT 1');
		if ($db2->nextRecord() == 0) $out2 = TRUE;
		else $out2 = FALSE;
		if ($curr_id == $id && $id != 0) {
			if (($out || $out2))
				$PHP_OUTPUT.=('<td class="red">-');
			elseif ($id == $player->getAllianceID() || $curr_id == $player->getAllianceID())
				$PHP_OUTPUT.=('<td class="bold">-');
			else $PHP_OUTPUT.=('<td>-');
		}
		else {
			$db2->query('SELECT kills FROM alliance_vs_alliance
						WHERE alliance_id_2 = ' . $db2->escapeNumber($curr_id) . '
							AND alliance_id_1 = ' . $db2->escapeNumber($id) . '
							AND game_id = ' . $db2->escapeNumber($player->getGameID()));
			if ($db2->nextRecord()) {
				$PHP_OUTPUT.=('<td');
				if (($out || $out2) && ($id == $player->getAllianceID() || $curr_id == $player->getAllianceID()))
					$PHP_OUTPUT.=(' class="bold red"');
				elseif ($out || $out2)
					$PHP_OUTPUT.=(' class="red"');
				elseif ($id == $player->getAllianceID() || $curr_id == $player->getAllianceID()) $PHP_OUTPUT.=(' class="bold"');
				$PHP_OUTPUT.=('>');
				$PHP_OUTPUT.= $db2->getField('kills');
			}
			else {
				$PHP_OUTPUT.=('<td');
				if (($out || $out2) && ($id == $player->getAllianceID() || $curr_id == $player->getAllianceID()))
					$PHP_OUTPUT.=(' class="bold red"');
				elseif ($out || $out2)
					$PHP_OUTPUT.=(' class="red"');
				elseif ($id == $player->getAllianceID() || $curr_id == $player->getAllianceID()) $PHP_OUTPUT.=(' class="bold"');
				$PHP_OUTPUT.=('>');
				$PHP_OUTPUT.=('0');
			}
		}
		$PHP_OUTPUT.=('</td>');
	}
	$PHP_OUTPUT.=('</tr>');
}

$PHP_OUTPUT.=('</table>');

$PHP_OUTPUT.=('<br />');
$PHP_OUTPUT.=create_submit('Show');
$PHP_OUTPUT.=('</form>');
$PHP_OUTPUT.=('</div>');

if (isset($var['alliance_id'])) {
	$PHP_OUTPUT.=('<table align="center"><tr><td width="45%" align="center" valign="top">');
	$main_alliance =& SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
	$db->query('SELECT * FROM alliance_vs_alliance
				WHERE alliance_id_1 = '.$db->escapeNumber($var['alliance_id']) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY kills DESC');
	if ($db->getNumRows() > 0) {
		$PHP_OUTPUT.=('<div align="center">Kills for '.$main_alliance->getAllianceName());
		$PHP_OUTPUT.=('<table class="standard"><tr><th align=center>Alliance Name</th>');
		$PHP_OUTPUT.=('<th align="center">Amount</th></tr>');
		while ($db->nextRecord()) {
			$kills = $db->getField('kills');
			$id = $db->getField('alliance_id_2');
			if ($id > 0) {
				$killer_alliance =& SmrAlliance::getAlliance($id, $player->getGameID());
				$alliance_name = $killer_alliance->getAllianceName();
			}
			elseif ($id == 0) $alliance_name = '<span class="blue">No Alliance</span>';
			elseif ($id == -1) $alliance_name = '<span class="blue">Forces</span>';
			elseif ($id == -2) $alliance_name = '<span class="blue">Planets</span>';
			elseif ($id == -3) $alliance_name = '<span class="blue">Ports</span>';

			$PHP_OUTPUT.=('<tr><td align="center">'.$alliance_name.'</td><td align="center">'.$kills.'</td></tr>');
		}
		$PHP_OUTPUT.=('</table>');
	}
	else $PHP_OUTPUT.=($main_alliance->getAllianceName().' has no kills!');
	$PHP_OUTPUT.=('</td><td width="10%">&nbsp;</td><td width="45%" align="center" valign="top">');
	$db->query('SELECT * FROM alliance_vs_alliance
				WHERE alliance_id_2 = '.$db->escapeNumber($var['alliance_id']) . '
					AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY kills DESC');
	if ($db->getNumRows() > 0) {
		$PHP_OUTPUT.=('<div align="center">Deaths for '.$main_alliance->getAllianceName());
		$PHP_OUTPUT.=('<table class="standard"><tr><th align=center>Alliance Name</th>');
		$PHP_OUTPUT.=('<th align="center">Amount</th></tr>');
		while ($db->nextRecord()) {
			$kills = $db->getField('kills');
			$id = $db->getField('alliance_id_1');
			if ($id > 0) {
				$killer_alliance =& SmrAlliance::getAlliance($id, $player->getGameID());
				$alliance_name = $killer_alliance->getAllianceName();
			}
			elseif ($id == 0) $alliance_name = '<span class="blue">No Alliance</span>';
			elseif ($id == -1) $alliance_name = '<span class="blue">Forces</span>';
			elseif ($id == -2) $alliance_name = '<span class="blue">Planets</span>';
			elseif ($id == -3) $alliance_name = '<span class="blue">Ports</span>';

			$PHP_OUTPUT.=('<tr><td align="center">'.$alliance_name.'</td><td align="center">'.$kills.'</td></tr>');
		}
		$PHP_OUTPUT.=('</table>');
	}
	else $PHP_OUTPUT.=($main_alliance->getAllianceName().' has no deaths!');
	$PHP_OUTPUT.=('</td></tr></table>');
}
