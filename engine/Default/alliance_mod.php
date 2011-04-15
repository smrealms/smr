<?php
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();

Globals::canAccessPage('AllianceMOTD', $player, array('AllianceID' => $alliance_id));

$db->query('SELECT leader_id,`mod`,img_src, alliance_name, alliance_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->nextRecord();
$leader_id = $db->getField('leader_id');
$template->assign('PageTopic',$db->getField('alliance_name') . ' (' . $db->getField('alliance_id') . ')');
//$template->assign('PageTopic',$player->getAllianceName() . ' (' . $player->getAllianceID() . ')');
include(get_file_loc('menue.inc'));
create_alliance_menue($alliance_id,$db->getField('leader_id'));

$PHP_OUTPUT.= '<div align="center">';

if (strlen($db->getField('img_src')) && $db->getField('img_src') != 'http://') {
	$PHP_OUTPUT.= '<img class="alliance" src="';
	$PHP_OUTPUT.= $db->getField('img_src');
	$PHP_OUTPUT.= '" alt="' . htmlspecialchars($db->getField('alliance_name')) . ' Banner"><br /><br />';
}

$PHP_OUTPUT.= '<span class="yellow">Message from your leader</span><br /><br />';
$PHP_OUTPUT.= bbifyMessage($db->getField('mod'));

$db->query('SELECT * FROM player_has_alliance_role WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID().' AND alliance_id='.$alliance_id);
if ($db->nextRecord()) $role_id = $db->getField('role_id');
else $role_id = 0;
$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = '.$player->getAllianceID().' AND game_id = '.$player->getGameID().' AND role_id = '.$role_id);
$db->nextRecord();
if ($db->getBoolean('change_mod') || $db->getBoolean('change_pass')) {
	$PHP_OUTPUT.= '<br /><br />';
	$container=create_container('skeleton.php','alliance_stat.php');
	$container['alliance_id'] = $alliance_id;
	$PHP_OUTPUT.=create_button($container,'Edit');
}
$PHP_OUTPUT.= '</div>';

?>