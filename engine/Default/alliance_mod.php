<?
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();
$db->query('SELECT leader_id,`mod`,img_src, alliance_name, alliance_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->nextRecord();
$leader_id = $db->getField('leader_id');
$smarty->assign('PageTopic',stripslashes($db->getField('alliance_name')) . ' (' . $db->getField('alliance_id') . ')');
//$smarty->assign('PageTopic',$player->getAllianceName() . ' (' . $player->getAllianceID() . ')');
include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_alliance_menue($alliance_id,$db->getField('leader_id'));

$PHP_OUTPUT.= '<div align="center">';

if (strlen($db->getField('img_src')) && $db->getField('img_src') != 'http://') {
	$PHP_OUTPUT.= '<img class="alliance" src="';
	$PHP_OUTPUT.= $db->getField('img_src');
	$PHP_OUTPUT.= '" alt="' . stripslashes($db->getField('alliance_name')) . ' Banner"><br /><br />';
}

$PHP_OUTPUT.= '<span class="yellow">Message from your leader</span><br /><br />';
$PHP_OUTPUT.= $db->getField('mod');

$db->query('SELECT * FROM player_has_alliance_role WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
if ($db->nextRecord()) $role_id = $db->getField('role_id');
else $role_id = 0;
$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = '.$player->getAllianceID().' AND game_id = '.$player->getGameID().' AND role_id = '.$role_id);
$db->nextRecord();
if ($db->getField('change_mod') == 'TRUE' || $db->getField('change_pass') == 'TRUE') {
	$PHP_OUTPUT.= '<br /><br />';
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'alliance_stat.php';
	$container['alliance_id'] = $alliance_id;
	$PHP_OUTPUT.=create_button($container,'Edit');
}
$PHP_OUTPUT.= '</div>';

?>