<?
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = $player->getAllianceID();
$db->query('SELECT leader_id,`mod`,img_src, alliance_name, alliance_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
$leader_id = $db->f('leader_id');
$smarty->assign('PageTopic',stripslashes($db->f('alliance_name')) . ' (' . $db->f('alliance_id') . ')');
//$smarty->assign('PageTopic',$player->getAllianceName() . ' (' . $player->getAllianceID() . ')');
include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_alliance_menue($alliance_id,$db->f('leader_id'));

$PHP_OUTPUT.= '<div align="center">';

if (strlen($db->f('img_src')) && $db->f('img_src') != 'http://') {
	$PHP_OUTPUT.= '<img class="alliance" src="';
	$PHP_OUTPUT.= $db->f('img_src');
	$PHP_OUTPUT.= '" alt="' . stripslashes($db->f('alliance_name')) . ' Banner"><br /><br />';
}

$PHP_OUTPUT.= '<span class="yellow">Message from your leader</span><br /><br />';
$PHP_OUTPUT.= $db->f('mod');

$db->query('SELECT * FROM player_has_alliance_role WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
if ($db->next_record()) $role_id = $db->f('role_id');
else $role_id = 0;
$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = '.$player->getAllianceID().' AND game_id = '.$player->getGameID().' AND role_id = '.$role_id);
$db->next_record();
if ($db->f('change_mod') == 'TRUE' || $db->f('change_pass') == 'TRUE') {
	$PHP_OUTPUT.= '<br /><br />';
	$container=array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'alliance_stat.php';
	$container['alliance_id'] = $alliance_id;
	$PHP_OUTPUT.=create_button($container,'Edit');
}
$PHP_OUTPUT.= '</div>';

?>