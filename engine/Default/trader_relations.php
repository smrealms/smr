<?
$template->assign('PageTopic','TRADER RELATIONS');

include(get_file_loc('menue.inc'));
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
$races =& Globals::getRaces();
foreach($races as $race)
{
	if ($race['Race ID'] == 1) continue;
	$otherRaceRelations = Globals::getRaceRelations(SmrSession::$game_id,$race['Race ID']);
	$PHP_OUTPUT.=($race['Race Name'].' : ' . get_colored_text($otherRaceRelations[$player->getRaceID()], $otherRaceRelations[$player->getRaceID()]) . '<br />');

}
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('<td valign="top">');

$PHP_OUTPUT.=('<p>');
$db->query('SELECT * FROM race');
foreach($races as $race)
{
	if ($race['Race ID'] == 1) continue;
	$PHP_OUTPUT.=($race['Race Name'].' : ' . get_colored_text($player->getRelation($race['Race ID']), $player->getRelation($race['Race ID'])) . '<br />');

}
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</p>');

?>