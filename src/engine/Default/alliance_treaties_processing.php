<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

//get the alliances
if (!$player->hasAlliance()) {
	create_error('You are not in an alliance!');
}
$alliance_id_1 = $var['alliance_id_1'];
$alliance_id_2 = $player->getAllianceID();

$db = Smr\Database::getInstance();
if ($var['accept']) {
	$db->write('UPDATE alliance_treaties SET official = \'TRUE\' WHERE alliance_id_1 = ' . $db->escapeNumber($alliance_id_1) . ' AND alliance_id_2 = ' . $db->escapeNumber($alliance_id_2) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));

	if ($var['aa_access']) {
		//make an AA role for both alliances, use treaty_created column
		$pairs = [
			$alliance_id_1 => $alliance_id_2,
			$alliance_id_2 => $alliance_id_1,
		];
		foreach ($pairs as $alliance_id_A => $alliance_id_B) {
			// get last id
			$dbResult = $db->read('SELECT MAX(role_id)
						FROM alliance_has_roles
						WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . '
							AND alliance_id = ' . $db->escapeNumber($alliance_id_A));
			$role_id = $dbResult->record()->getInt('MAX(role_id)') + 1;

			$allianceName = SmrAlliance::getAlliance($alliance_id_B, $player->getGameID())->getAllianceName();
			$db->insert('alliance_has_roles', [
				'alliance_id' => $db->escapeNumber($alliance_id_A),
				'game_id' => $db->escapeNumber($player->getGameID()),
				'role_id' => $db->escapeNumber($role_id),
				'role' => $db->escapeString($allianceName),
				'treaty_created' => 1,
			]);
		}
	}
} else {
	$db->write('DELETE FROM alliance_treaties WHERE alliance_id_1 = ' . $db->escapeNumber($alliance_id_1) . ' AND alliance_id_2 = ' . $db->escapeNumber($alliance_id_2) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
}

$container = Page::create('alliance_treaties.php');
$container->go();
