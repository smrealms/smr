<?php

$template->assign('PageTopic', 'Alliance VS Alliance Rankings');

Menu::rankings(1, 4);
$db2 = new SmrMySqlDatabase();
$container = create_container('skeleton.php', 'rankings_alliance_vs_alliance.php');
$template->assign('SubmitHREF', SmrSession::getNewHREF($container));

$alliancer = SmrSession::getRequestVar('alliancer');
$detailsAllianceID = SmrSession::getRequestVar('alliance_id', $player->getAllianceID());

// Get list of alliances that have kills or deaths
$activeAlliances = [];
$db->query('SELECT alliance_id FROM alliance WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND (alliance_deaths > 0 OR alliance_kills > 0) ORDER BY alliance_kills DESC, alliance_name');
while ($db->nextRecord()) {
	$activeAlliances[] = $db->getField('alliance_id');
}
$template->assign('ActiveAlliances', $activeAlliances);

// Get list of alliances to display (max of 5)
// These must be a subset of the active alliances
if (empty($alliancer)) {
	$alliance_vs_ids = array_slice($activeAlliances, 0, 4);
	$alliance_vs_ids[] = 0;
} else {
	$alliance_vs_ids = $alliancer;
}

$alliance_vs = [];
foreach ($alliance_vs_ids as $curr_id) {
	$curr_alliance = SmrAlliance::getAlliance($curr_id, $player->getGameID());
	$container['alliance_id'] = $curr_id;
	$style = '';
	if (!$curr_alliance->isNone() && $curr_alliance->hasDisbanded()) {
		$style = 'class="red"';
	}
	if ($player->getAllianceID() == $curr_id) {
		$style = 'class="bold"';
	}
	$alliance_vs[] = [
		'ID' => $curr_id,
		'DetailsHREF' => SmrSession::getNewHREF($container),
		'Name' => $curr_alliance->isNone() ? 'No Alliance' : $curr_alliance->getAllianceName(),
		'Style' => $style,
	];
}
$template->assign('AllianceVs', $alliance_vs);

$alliance_vs_table = [];
foreach ($alliance_vs_ids as $curr_id) {
	foreach ($alliance_vs_ids as $id) {
		$row_alliance = SmrAlliance::getAlliance($id, $player->getGameID());
		$showRed = (!$curr_alliance->isNone() && $curr_alliance->hasDisbanded()) ||
		           (!$row_alliance->isNone() && $row_alliance->hasDisbanded());
		$showBold = $curr_id == $player->getAllianceID() || $id == $player->getAllianceID();
		$style = '';
		if ($curr_id == $id && !$row_alliance->isNone()) {
			$value = '--';
			if ($showRed) {
				$style = 'class="red"';
			} elseif ($showBold) {
				$style = 'class="bold"';
			}
		}
		else {
			$db2->query('SELECT kills FROM alliance_vs_alliance
						WHERE alliance_id_2 = ' . $db2->escapeNumber($curr_id) . '
							AND alliance_id_1 = ' . $db2->escapeNumber($id) . '
							AND game_id = ' . $db2->escapeNumber($player->getGameID()));
			if ($db2->nextRecord()) {
				$value = $db2->getInt('kills');
			} else {
				$value = 0;
			}
			if ($showRed && $showBold) {
				$style = 'class="bold red"';
			} elseif ($showRed) {
				$style = 'class="red"';
			} elseif ($showBold) {
				$style = 'class="bold"';
			}
		}
		$alliance_vs_table[$curr_id][$id] = [
			'Value' => $value,
			'Style' => $style,
		];
	}
}
$template->assign('AllianceVsTable', $alliance_vs_table);


// Show details for a specific alliance
$main_alliance = SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$mainName = $main_alliance->isNone() ? 'No Alliance' : $main_alliance->getAllianceName();
$template->assign('DetailsName', $mainName);

$kills = [];
$db->query('SELECT * FROM alliance_vs_alliance
			WHERE alliance_id_1 = '.$db->escapeNumber($var['alliance_id']) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY kills DESC');
while ($db->nextRecord()) {
	$id = $db->getField('alliance_id_2');
	if ($id > 0) {
		$killer_alliance = SmrAlliance::getAlliance($id, $player->getGameID());
		$alliance_name = $killer_alliance->getAllianceName();
	}
	elseif ($id == 0) $alliance_name = 'No Alliance';
	elseif ($id == ALLIANCE_VS_FORCES) $alliance_name = '<span class="yellow">Forces</span>';
	elseif ($id == ALLIANCE_VS_PLANETS) $alliance_name = '<span class="yellow">Planets</span>';
	elseif ($id == ALLIANCE_VS_PORTS) $alliance_name = '<span class="yellow">Ports</span>';

	$kills[] = [
		'Name' => $alliance_name,
		'Kills' => $db->getInt('kills'),
	];
}
$template->assign('Kills', $kills);

$deaths = [];
$db->query('SELECT * FROM alliance_vs_alliance
			WHERE alliance_id_2 = '.$db->escapeNumber($var['alliance_id']) . '
				AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' ORDER BY kills DESC');
while ($db->nextRecord()) {
	$id = $db->getField('alliance_id_1');
	if ($id > 0) {
		$killer_alliance = SmrAlliance::getAlliance($id, $player->getGameID());
		$alliance_name = $killer_alliance->getAllianceName();
	}
	elseif ($id == 0) $alliance_name = 'No Alliance';
	elseif ($id == ALLIANCE_VS_FORCES) $alliance_name = '<span class="yellow">Forces</span>';
	elseif ($id == ALLIANCE_VS_PLANETS) $alliance_name = '<span class="yellow">Planets</span>';
	elseif ($id == ALLIANCE_VS_PORTS) $alliance_name = '<span class="yellow">Ports</span>';

	$deaths[] = [
		'Name' => $alliance_name,
		'Deaths' => $db->getField('kills'),
	];
}
$template->assign('Deaths', $deaths);
