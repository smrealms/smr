<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

Menu::planet_list($var['alliance_id'], 1);

// Determine if the player can view bonds on the planet list.
// Player can always see them if not in an alliance.
$viewBonds = true;
if ($var['alliance_id'] != 0) {
	$role_id = $player->getAllianceRole($var['alliance_id']);
	$db = Smr\Database::getInstance();
	$dbResult = $db->read('
		SELECT 1
		FROM alliance_has_roles
		WHERE alliance_id = ' . $db->escapeNumber($var['alliance_id']) . '
		AND game_id = ' . $db->escapeNumber($player->getGameID()) . '
		AND role_id = ' . $db->escapeNumber($role_id) . '
		AND view_bonds = 1'
	);
	$viewBonds = $dbResult->hasRecord();
}
$template->assign('CanViewBonds', $viewBonds);

require_once(get_file_loc('planet_list.inc.php'));
planet_list_common($var['alliance_id'], $viewBonds);
