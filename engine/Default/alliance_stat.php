<?php
if (!isset($var['alliance_id'])) {
	SmrSession::updateVar('alliance_id',$player->getAllianceID());
}
$alliance_id = $var['alliance_id'];

$alliance =& SmrAlliance::getAlliance($alliance_id,$player->getGameID());
$template->assign('PageTopic',$alliance->getAllianceName() . ' (' . $alliance->getAllianceID() . ')');
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance_id,$alliance->getLeaderID());

$container=create_container('alliance_stat_processing.php');
$container['alliance_id'] = $alliance_id;

$form = create_form($container,'Change');

$role_id = $player->getAllianceRole($alliance->getAllianceID());

$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = ' . $db->escapeNumber($alliance_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND role_id = ' . $db->escapeNumber($role_id));
$db->nextRecord();
$PHP_OUTPUT.= $form['form'];

//$PHP_OUTPUT.=create_echo_form(create_container('alliance_stat_processing.php', ''));
$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0" class="nobord nohpad">';

if ($db->getBoolean('change_pass')) {
	$PHP_OUTPUT.= '<tr><td class="top">Password:&nbsp;</td><td><input type="password" name="password" size="30" value="'.htmlspecialchars($alliance->getPassword()).'"></td></tr>';
}
if ($db->getBoolean('change_mod') || $account->hasPermission(PERMISSION_EDIT_ALLIANCE_DESCRIPTION)) {
	$PHP_OUTPUT.= '<tr><td class="top">Description:&nbsp;</td><td><textarea spellcheck="true" name="description">';
	$PHP_OUTPUT.= $alliance->getDescription();
	$PHP_OUTPUT.= '</textarea></td></tr>';
}
if ($player->isAllianceLeader()) {
	$PHP_OUTPUT.= '<tr><td class="top">IRC Channel:&nbsp;</td><td><input type="text" name="irc" size="30" value="'.htmlspecialchars($alliance->getIrcChannel()).'"> (For Caretaker and autojoining via chat link - works best if you join the channel using the chat link and type "/autoconnect on" as an op)</td></tr>';
}
if ($db->getBoolean('change_mod')) {
	$PHP_OUTPUT.= '<tr><td class="top">Image URL:&nbsp;</td><td><input type="url" name="url" size="30" value="'.htmlspecialchars($alliance->getImageURL()).'"></td></tr>';

	$PHP_OUTPUT.= '<tr><td class="top">Message Of The Day:&nbsp;</td><td><textarea spellcheck="true" name="mod">';
	$PHP_OUTPUT.= $alliance->getMotD();
	$PHP_OUTPUT.= '</textarea></td></tr>';
}
$PHP_OUTPUT.= '</table><br />';
$PHP_OUTPUT.= $form['submit'];
$PHP_OUTPUT.= '</form>';

?>