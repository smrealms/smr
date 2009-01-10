<?
require_once(get_file_loc('SmrSector.class.inc'));
$sector =& SmrSector::getSector(SmrSession::$game_id, $player->getSectorID(), SmrSession::$account_id);

$smarty->assign('PageTopic','Place a Bounty');

include(ENGINE . 'global/menue.inc');
if ($sector->has_hq())
	$PHP_OUTPUT.=create_hq_menue();
else
	$PHP_OUTPUT.=create_ug_menue();

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'bounty_place_confirm.php';
$PHP_OUTPUT.=create_echo_form($container);

$PHP_OUTPUT.=('Select the player you want to add the bounty to<br />');
$PHP_OUTPUT.=('<select name="account_id" size="1" id="InputFields">');
$PHP_OUTPUT.=('<option value=0>[Please Select]</option>');
$db->query('SELECT * FROM player WHERE game_id = '.$player->getGameID().' ORDER BY player_name');
while($db->next_record()) {
	$PHP_OUTPUT.=('<option value="' . $db->f('account_id') . '">' . $db->f('player_name') . '</option>');
}
$PHP_OUTPUT.=('</select>');

$PHP_OUTPUT.=('<br /><br />');
$PHP_OUTPUT.=('Enter the amount you wish to place on this player<br />');
$PHP_OUTPUT.=('<input type="text" name="amount" maxlength="10" size="10" id="InputFields">');

$PHP_OUTPUT.=('<br /><br />');
$PHP_OUTPUT.=create_submit('Place');

?>