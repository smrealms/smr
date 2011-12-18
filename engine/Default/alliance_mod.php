<?php
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id',$player->getAllianceID());
}

$alliance =& SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());

Globals::canAccessPage('AllianceMOTD', $player, array('AllianceID' => $alliance->getAllianceID()));

$template->assign('PageTopic',$alliance->getAllianceName() . ' (' . $alliance->getAllianceID() . ')');
//$template->assign('PageTopic',$player->getAllianceName() . ' (' . $player->getAllianceID() . ')');
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

$PHP_OUTPUT.= '<div align="center">';

if ($alliance->hasImageURL()) {
	$PHP_OUTPUT.= '<img class="alliance" src="' . $alliance->getImageURL() . '" alt="' . htmlspecialchars($alliance->getAllianceName()) . ' Banner"><br /><br />';
}

$PHP_OUTPUT.= '<span class="yellow">Message from your leader</span><br /><br />';
$PHP_OUTPUT.= bbifyMessage($alliance->getMotD());

$db->query('SELECT * FROM player_has_alliance_role WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND alliance_id=' . $db->escapeNumber($alliance->getAllianceID()));
if ($db->nextRecord()) {
	$role_id = $db->getInt('role_id');
}
else {
	$role_id = 0;
}
$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($player->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND role_id = ' . $db->escapeNumber($role_id));
$db->nextRecord();
if ($db->getBoolean('change_mod') || $db->getBoolean('change_pass')) {
	$PHP_OUTPUT.= '<br /><br />';
	$container=create_container('skeleton.php','alliance_stat.php');
	$container['alliance_id'] = $alliance->getAllianceID();
	$PHP_OUTPUT.=create_button($container,'Edit');
}
$PHP_OUTPUT.= '</div>';

?>