<?php
$template->assign('PageTopic','Trader Relations');

require_once(get_file_loc('menu.inc'));
create_trader_menu();

$PHP_OUTPUT.=('<p align="center">');
$PHP_OUTPUT.=('<table width="60%" class="standard">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th valign="top" width="50%">Relations (Global)</th>');
$PHP_OUTPUT.=('<th valign="top" width="50%">Relations (Personal)</th>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td valign="top" width="50%">');

$PHP_OUTPUT.=('<p>');
$RACES =& Globals::getRaces();
foreach($RACES as $raceID => $race)
{
	if ($raceID == 1) continue;
	$otherRaceRelations = Globals::getRaceRelations($player->getGameID(),$raceID);
	$PHP_OUTPUT.=($race['Race Name'].' : ' . get_colored_text($otherRaceRelations[$player->getRaceID()], $otherRaceRelations[$player->getRaceID()]) . '<br />');

}
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('<td valign="top">');

$PHP_OUTPUT.=('<p>');
foreach($RACES as $raceID => $race)
{
	if ($raceID == 1) continue;
	$PHP_OUTPUT.=($race['Race Name'].' : ' . get_colored_text($player->getPureRelation($raceID), $player->getPureRelation($raceID)) . '<br />');

}
$PHP_OUTPUT.=('</p>');

$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');
$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</p>');

?>