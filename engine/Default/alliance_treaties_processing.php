<?php

//get the alliances
if (!$player->hasAlliance()) create_error('You are not in an alliance!');
$alliance_id_1 = $var['alliance_id_1'];
$alliance_id_2 = $player->getAllianceID();

if ($var['accept']) {
	$db->query('UPDATE alliance_treaties SET official = \'TRUE\' WHERE alliance_id_1 = ' . $db->escapeNumber($alliance_id_1) . ' AND alliance_id_2 = ' . $db->escapeNumber($alliance_id_2) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));

	if ($var['aa_access']) {
		//make an AA role for both alliances, use treaty_created column
		$pairs = [
			$alliance_id_1 => $alliance_id_2,
			$alliance_id_2 => $alliance_id_1,
		];
		foreach ($pairs as $alliance_id_A => $alliance_id_B) {
			// get last id
			$db->query('SELECT MAX(role_id)
						FROM alliance_has_roles
						WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
							AND alliance_id = ' . $db->escapeNumber($alliance_id_A));
			if ($db->nextRecord()) {
				$role_id = $db->getInt('MAX(role_id)') + 1;
			}
			$allianceName = SmrAlliance::getAlliance($alliance_id_B, $player->getGameID())->getAllianceName();
			$db->query('INSERT INTO alliance_has_roles
				(alliance_id, game_id, role_id, role, treaty_created)
				VALUES (' . $db->escapeNumber($alliance_id_A) . ', ' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($role_id) . ', ' . $db->escapeString($allianceName) . ',1)');
		}
	}
}
else {
	$db->query('DELETE FROM alliance_treaties WHERE alliance_id_1 = ' . $db->escapeNumber($alliance_id_1) . ' AND alliance_id_2 = ' . $db->escapeNumber($alliance_id_2) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
}

$container = create_container('skeleton.php', 'alliance_treaties.php');
forward($container);
