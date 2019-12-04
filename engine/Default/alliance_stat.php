<?php declare(strict_types=1);
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id', $player->getAllianceID());
}
$alliance_id = $var['alliance_id'];

$alliance = SmrAlliance::getAlliance($alliance_id, $player->getGameID());
$template->assign('PageTopic', $alliance->getAllianceName(false, true));
Menu::alliance($alliance_id, $alliance->getLeaderID());

$container = create_container('alliance_stat_processing.php');
$container['alliance_id'] = $alliance_id;

$form = create_form($container, 'Change');

$role_id = $player->getAllianceRole($alliance->getAllianceID());

$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND role_id = ' . $db->escapeNumber($role_id));
$db->nextRecord();

$template->assign('Form', $form);
$template->assign('Alliance', $alliance);

$template->assign('CanChangeDescription', $db->getBoolean('change_mod') || $account->hasPermission(PERMISSION_EDIT_ALLIANCE_DESCRIPTION));
$template->assign('CanChangePassword', $db->getBoolean('change_pass'));
$template->assign('CanChangeChatChannel', $player->isAllianceLeader());
$template->assign('CanChangeMOTD', $db->getBoolean('change_mod'));
