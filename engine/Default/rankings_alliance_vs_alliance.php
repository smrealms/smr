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

$alliancer = SmrSession::getRequestVar('alliancer');

$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<p>Here are the rankings of alliances vs other alliances<br />');
$PHP_OUTPUT.=('Click on an alliances name for more detailed death stats.</p>');

$PHP_OUTPUT.=('<table class="standard shrink">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td rowspan="2" colspan="2"></td><th colspan="6">Killers</th></tr><tr>');

// Get list of alliances that have kills or deaths
$activeAlliances = [];
$db->query('SELECT alliance_id FROM alliance WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND (alliance_deaths > 0 OR alliance_kills > 0) ORDER BY alliance_kills DESC, alliance_name');
while ($db->nextRecord()) {
	$activeAlliances[] = $db->getField('alliance_id');
}

// Get list of alliances to display (max of 5)
// These must be a subset of the active alliances
if (empty($alliancer)) {
	$alliance_vs = array_slice($activeAlliances, 0, 5);
} else {
	$alliance_vs = $alliancer;
}
$alliance_vs[] = 0;

foreach ($alliance_vs as $curr_id) {
	// get current alliance
	if ($curr_id > 0) {

		$PHP_OUTPUT.=('<td width=15% valign="top"');
		if ($player->getAllianceID() == $curr_id)
			$PHP_OUTPUT.=(' class="bold"');
		$PHP_OUTPUT.=('>');
		$PHP_OUTPUT.=('<select name="alliancer[]" id="InputFields" style="width:105">');
		foreach ($activeAlliances as $activeID) {
			$curr_alliance = SmrAlliance::getAlliance($activeID, $player->getGameID());
			$PHP_OUTPUT.=('<option value=' . $activeID);
			if ($curr_id == $activeID)
				$PHP_OUTPUT.=(' selected');
			$PHP_OUTPUT.=('>' . $curr_alliance->getAllianceName() . '</option>');
		}
		$PHP_OUTPUT.='</select>';
		$PHP_OUTPUT.=('</td>');
	}
}
$PHP_OUTPUT.=('<td width=10% valign="top">None</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr><th rowspan="6">Killed</th></tr>');

foreach ($alliance_vs as $curr_id) {
	$PHP_OUTPUT.=('<tr>');
	// get current alliance
	$curr_alliance = SmrAlliance::getAlliance($curr_id, $player->getGameID());
	$container1 = create_container('skeleton.php', 'rankings_alliance_vs_alliance.php');
	$container1['alliance_id'] = $curr_alliance->getAllianceID();
	if ($curr_id > 0) {

		$PHP_OUTPUT.=('<td width=10% valign="top"');
		if ($player->getAllianceID() == $curr_alliance->getAllianceID())
			$PHP_OUTPUT.=(' class="bold"');
		if ($curr_alliance->hasDisbanded())
			$PHP_OUTPUT.=(' class="red"');
		$PHP_OUTPUT.=('>');
		$PHP_OUTPUT.=create_link($container1, $curr_alliance->getAllianceName());
		$PHP_OUTPUT.=('</td>');
	}
	else {
		$PHP_OUTPUT.=('<td width=10% valign="top">');
		$PHP_OUTPUT.=create_link($container1, 'None');
		$PHP_OUTPUT.=('</td>');
	}

	foreach ($alliance_vs as $id) {
		$row_alliance = SmrAlliance::getAlliance($id, $player->getGameID());
		$showRed = ($curr_alliance->getAllianceID() != 0 && $curr_alliance->hasDisbanded()) ||
		           ($row_alliance->getAllianceID() != 0 && $row_alliance->hasDisbanded());
		$showBold = $curr_id == $player->getAllianceID() || $id == $player->getAllianceID();
		if ($curr_id == $id && $id != 0) {
			if ($showRed)
				$PHP_OUTPUT.=('<td class="red">-');
			elseif ($showBold)
				$PHP_OUTPUT.=('<td class="bold">-');
			else $PHP_OUTPUT.=('<td>-');
		}
		else {
			$db2->query('SELECT kills FROM alliance_vs_alliance
						WHERE alliance_id_2 = ' . $db2->escapeNumber($curr_id) . '
							AND alliance_id_1 = ' . $db2->escapeNumber($id) . '
							AND game_id = ' . $db2->escapeNumber($player->getGameID()));
			$PHP_OUTPUT.=('<td');
			if ($showRed && $showBold)
				$PHP_OUTPUT.=(' class="bold red"');
			elseif ($showRed)
				$PHP_OUTPUT.=(' class="red"');
			elseif ($showBold)
				$PHP_OUTPUT.=(' class="bold"');
			$PHP_OUTPUT.=('>');
			if ($db2->nextRecord()) {
				$PHP_OUTPUT.= $db2->getField('kills');
			} else {
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
	$main_alliance = SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
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
				$killer_alliance = SmrAlliance::getAlliance($id, $player->getGameID());
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
				$killer_alliance = SmrAlliance::getAlliance($id, $player->getGameID());
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
