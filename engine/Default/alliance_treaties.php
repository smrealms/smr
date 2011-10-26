<?php
//treaty info
$types = array	(
				'assistTrader' => array	(
										'Assist - Trader Attacks',
										'Assist your ally in attacking traders.'
										),
				'assistRaids' => array	(
										'Assist - Planet & Port Attacks',
										'Assist your ally in attacking planets and ports.'
										),
				'defendTrader' => array	(
										'Defend - Trader Attacks',
										'Defend your ally when they are attacked.'
										),
				'napTrader' => array	(
										'Non Aggression - Traders',
										'Cease Fire against Traders.'
										),
				'napPlanets' => array(
										'Non Aggression - Planets',
										'Cease Fire against Planets.'
										),
				'napForces' => array(
										'Non Aggression - Forces',
										'Cease Fire against Forces.  Also allows refreshing of allied forces.'
										),
				'aaAccess' => array(
										'Alliance Account Access',
										'Restrictions can be set in the roles section.'
										),
				'mbRead' => array(
										'Message Board Read Rights',
										'Allow your ally to read your message board.'
										),
				'mbWrite' => array(
										'Message Board Write Rights',
										'Allow your ally to post on your message board.'
										),
				'modRead' => array(
										'Message of the Day Read Rights',
										'Allow your ally to read your message of the day.'
										),
				'planetLand' => array(
										'Planet Landing Rights',
										'Allow your ally to land on your planets.'
										)
				);
if (!isset($var['alliance_id']))
	SmrSession::updateVar('alliance_id',$player->getAllianceID());

$alliance =& SmrAlliance::getAlliance($var['alliance_id'], $player->getGameID());
$template->assign('PageTopic',stripslashes($db->getField('alliance_name')) . ' (' . $db->getField('alliance_id') . ')');
include(get_file_loc('menue.inc'));
create_alliance_menue($alliance->getAllianceID(),$alliance->getLeaderID());
$db->query('SELECT * FROM alliance WHERE game_id = '.$player->getGameID().' AND alliance_id != '.$player->getAllianceID().' ORDER BY alliance_name');
while ($db->nextRecord()) $temp[$db->getField('alliance_id')] = stripslashes($db->getField('alliance_name'));
$PHP_OUTPUT.=('<div align="center">');
if (isset($var['message'])) $PHP_OUTPUT.=($var['message'] . '<br />');
$PHP_OUTPUT.=('<br /><br />');
$db->query('SELECT * FROM alliance_treaties WHERE alliance_id_2 = '.$alliance->getAllianceID().' AND game_id = '.$alliance->getGameID().' AND official = \'FALSE\'');
while ($db->nextRecord()) {
	$template->assign('PageTopic','Treaty Offers');
	$PHP_OUTPUT.=('Treaty offer from <span class="yellow">');
	$PHP_OUTPUT.=($temp[$db->getField('alliance_id_1')]);
	$PHP_OUTPUT.=('</span>.  Terms as follows:<br /><ul>');
	if ($db->getField('trader_assist')) $PHP_OUTPUT.=('<li>Assist - Trader Attacks</li>');
	if ($db->getField('trader_defend')) $PHP_OUTPUT.=('<li>Defend - Trader Attacks</li>');
	if ($db->getField('trader_nap')) $PHP_OUTPUT.=('<li>Non Aggression - Traders</li>');
	if ($db->getField('raid_assist')) $PHP_OUTPUT.=('<li>Assist - Planet & Port Attacks</li>');
	if ($db->getField('planet_nap')) $PHP_OUTPUT.=('<li>Non Aggression - Planets</li>');
	if ($db->getField('forces_nap')) $PHP_OUTPUT.=('<li>Non Aggression - Forces</li>');
	if ($db->getField('aa_access')) $PHP_OUTPUT.=('<li>Alliance Account Access</li>');
	if ($db->getField('mb_read')) $PHP_OUTPUT.=('<li>Message Board Read Rights</li>');
	if ($db->getField('mb_write')) $PHP_OUTPUT.=('<li>Message Board Write Rights</li>');
	if ($db->getField('mod_read')) $PHP_OUTPUT.=('<li>Message of the Day Read Rights</li>');
	if ($db->getField('planet_land')) $PHP_OUTPUT.=('<li>Planet Landing Rights</li>');
	$PHP_OUTPUT.=('</ul>');
	$container=create_container('alliance_treaties_processing.php','');
	$container['alliance_id'] = $alliance->getAllianceID();
	$container['alliance_id_1'] = $db->getField('alliance_id_1');
	$container['aa'] = $db->getField('aa_access');
	$container['alliance_name'] = $temp[$db->getField('alliance_id_1')];
	$container['accept'] = TRUE;
	$PHP_OUTPUT.=create_button($container,'Accept');
	$container['accept'] = FALSE;
	$PHP_OUTPUT.=('&nbsp;');
	$PHP_OUTPUT.=create_button($container,'Reject');
	$PHP_OUTPUT.=('<br /><br />');
}
$template->assign('PageTopic','Offer A Treaty');
$PHP_OUTPUT.=('Select the alliance you wish to offer a treaty.<br /><small>Note: Treaties require 24 hours to be canceled once in effect</small><br />');
$container=array();
$container['url'] = 'skeleton.php';
$container['body'] = 'alliance_treaties_processing.php';
$container['alliance_id'] = $alliance->getAllianceID();
$form = create_form($container,'Send the Offer');
$PHP_OUTPUT.= $form['form'];
$PHP_OUTPUT.=('<select name="proposedAlliance" id="InputFields">');
foreach ($temp as $allId => $allName) $PHP_OUTPUT.=('<option value="'.$allId.'">'.$allName.'</option>');
$PHP_OUTPUT.=('</select');
$PHP_OUTPUT.=('<br />Choose the treaty terms<br />');
$PHP_OUTPUT.=create_table();
foreach ($types as $checkName => $displayInfo)
	$PHP_OUTPUT.=('<tr><td>' . $displayInfo[0] . '<br /><small>' . $displayInfo[1] . '</small></td><td><input type="checkbox" name="' . $checkName . '"></td></tr>');
$PHP_OUTPUT.=('<tr><td colspan="2">');
$PHP_OUTPUT.=($form['submit']);
$PHP_OUTPUT.=('</td></tr></table></form></div>');
?>