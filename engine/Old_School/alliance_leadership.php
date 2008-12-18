<?

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $player->getAllianceID() . ' LIMIT 1');
$db->next_record();
$smarty->assign('PageTopic',$player->getAllianceName() . ' (' . $player->getAllianceID() . ')');
include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_alliance_menue($player->getAllianceID(),$db->f('leader_id'));

$container = array();
$container['url'] = 'alliance_leadership_processing.php';
$container['body'] = '';
$form = create_form($container,'Handover Leadership');

$PHP_OUTPUT.= $form['form'];

$PHP_OUTPUT.= 'Please select the new Leader:&nbsp;&nbsp;&nbsp;<select name="leader_id" size="1">';

$db->query('
SELECT account_id,player_id,player_name 
FROM player 
WHERE game_id=' . $player->getGameID() . '
AND alliance_id=' . $player->getAllianceID() . '
LIMIT 30'
);

while ($db->next_record()) {
	$PHP_OUTPUT.= '<option value="' . $db->f('account_id') . '"';
	if ($db->f('account_id') == $player->getAccountID()) $PHP_OUTPUT.= ' selected="selected"';
	$PHP_OUTPUT.= '>';
	$PHP_OUTPUT.= stripslashes($db->f('player_name'));
	$PHP_OUTPUT.= ' (';
	$PHP_OUTPUT.= $db->f('player_id');
	$PHP_OUTPUT.= ')</option>';
}

$PHP_OUTPUT.=('</select><br><br>');

$PHP_OUTPUT.= $form['submit'];
$PHP_OUTPUT.= '</form>';

?>