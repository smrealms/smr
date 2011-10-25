<?php

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . $player->getGameID() . ' AND alliance_id=' . $player->getAllianceID() . ' LIMIT 1');
$db->nextRecord();
$template->assign('PageTopic',$player->getAllianceName() . ' (' . $player->getAllianceID() . ')');
include(get_file_loc('menue.inc'));
create_alliance_menue($player->getAllianceID(),$db->getField('leader_id'));

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
AND alliance_id=' . $player->getAllianceID() //No limit in case they are over limit - ie NHA
);

while ($db->nextRecord()) {
	$PHP_OUTPUT.= '<option value="' . $db->getField('account_id') . '"';
	if ($db->getField('account_id') == $player->getAccountID()) $PHP_OUTPUT.= ' selected="selected"';
	$PHP_OUTPUT.= '>';
	$PHP_OUTPUT.= $db->getField('player_name');
	$PHP_OUTPUT.= ' (';
	$PHP_OUTPUT.= $db->getField('player_id');
	$PHP_OUTPUT.= ')</option>';
}

$PHP_OUTPUT.=('</select><br /><br />');

$PHP_OUTPUT.= $form['submit'];
$PHP_OUTPUT.= '</form>';

?>