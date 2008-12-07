<?
if (isset($var['alliance_id'])) $alliance_id = $var['alliance_id'];
else $alliance_id = '.$player->getAllianceID().';
$db->query('SELECT leader_id,img_src,alliance_password,alliance_description,`mod`,alliance_name,alliance_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $alliance_id . ' LIMIT 1');
$db->next_record();
$pw = $db->f('alliance_password');
$desc = strip_tags($db->f('alliance_description'));
$img = $db->f('img_src');
$mod = strip_tags($db->f('mod'));
$smarty->assign('PageTopic',stripslashes($db->f('alliance_name')) . ' (' . $db->f('alliance_id') . ')');
include($ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_alliance_menue($alliance_id,$db->f('leader_id'));

$container=array();
$container['url'] = 'alliance_stat_processing.php';
$container['body'] = '';
$container['alliance_id'] = $alliance_id;

$form = create_form($container,'Change');
$db->query('SELECT * FROM player_has_alliance_role WHERE account_id = '.$player->getAccountID().' AND game_id = '.$player->getGameID());
if ($db->next_record()) $role_id = $db->f('role_id');
else $role_id = 0;
$db->query('SELECT * FROM alliance_has_roles WHERE alliance_id = '.$alliance_id.' AND game_id = '.$player->getGameID().' AND role_id = '.$role_id);
$db->next_record();
$PHP_OUTPUT.= $form['form'];

//$PHP_OUTPUT.=create_echo_form(create_container('alliance_stat_processing.php', ''));
$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0" class="nobord nohpad">';

if ($db->f('change_pass')) {
	$PHP_OUTPUT.= '<tr><td class="top">Password:&nbsp;</td><td><input type="password" name="password" size="30" value="';
	$PHP_OUTPUT.= $pw;
	$PHP_OUTPUT.= '"></td></tr>';
} if ($db->f('change_mod')) {
	$PHP_OUTPUT.= '<tr><td class="top">Description:&nbsp;</td><td><textarea name="description">';
	$PHP_OUTPUT.= $desc;
	$PHP_OUTPUT.= '</textarea></td></tr>';
	
	$PHP_OUTPUT.= '<tr><td class="top">Image URL:&nbsp;</td><td><input type="text" name="url" size="30" value="';
	$PHP_OUTPUT.= $img;
	$PHP_OUTPUT.= '"></td></tr>';
	
	$PHP_OUTPUT.= '<tr><td class="top">Message Of The Day:&nbsp;</td><td><textarea name="mod">';
	$PHP_OUTPUT.= $mod;
	$PHP_OUTPUT.= '</textarea></td></tr>';
}
$PHP_OUTPUT.= '</table><br />';
$PHP_OUTPUT.= $form['submit'];
$PHP_OUTPUT.= '</form>';

?>