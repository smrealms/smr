<?php declare(strict_types=1);

Menu::planet_list($var['alliance_id'], 1);

// Determine if the player can view bonds on the planet list.
// Player can always see them if not in an alliance.
$viewBonds = true;
if ($var['alliance_id'] != 0) {
	$role_id = $player->getAllianceRole($var['alliance_id']);
	$db->query('
		SELECT *
		FROM alliance_has_roles
		WHERE alliance_id = ' . $db->escapeNumber($var['alliance_id']) . '
		AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
		AND role_id = ' . $db->escapeNumber($role_id)
	);
	$db->nextRecord();
	$viewBonds = $db->getBoolean('view_bonds');
}
$template->assign('CanViewBonds', $viewBonds);

require_once('planet_list.inc');
planet_list_common($var['alliance_id'], $viewBonds);
