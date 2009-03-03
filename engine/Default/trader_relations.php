<?
$template->assign('PageTopic','TRADER RELATIONS');

include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_trader_menue();

$PHP_OUTPUT.=('<p align="center">');
$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="5" width="60%" border="0" class="standard">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th valign="top" width="50%">Relations (Global)</th>');
$PHP_OUTPUT.=('<th valign="top" width="50%">Relations (Personal)</th>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td valign="top" width="50%">');

$PHP_OUTPUT.=('<p>');
$db->query('SELECT * FROM race');
while ($db->nextRecord()) {

	$race_id = $db->getField('race_id');

	if ($race_id == 1) continue;

	$race_name = $db->getField('race_name');
	$otherRaceRelations = Globals::getRaceRelations(SmrSession::$game_id,$race_id);
	$PHP_OUTPUT.=($race_name.' : ' . get_colored_text($otherRaceRelations[$player->getRaceID()], $otherRaceRelations[$player->getRaceID()]) . '<br />');

}
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('<td valign="top">');

$PHP_OUTPUT.=('<p>');
$db->query('SELECT * FROM race');
while ($db->nextRecord()) {

	$race_id = $db->getField('race_id');

	if ($race_id == 1) continue;

	$race_name = $db->getField('race_name');
	$PHP_OUTPUT.=($race_name.' : ' . get_colored_text($player->getRelation($race_id), $player->getRelation($race_id)) . '<br />');

}
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</p>');

?>