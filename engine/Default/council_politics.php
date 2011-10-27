<?php
require_once(get_file_loc('menu.inc'));

// echo topic
$raceID = $var['race_id'];
if (empty($raceID))
	$raceID = $player->getRaceID();
$RACES =& Globals::getRaces();
$raceRelations =& Globals::getRaceRelations($player->getGameID(),$raceID);

$template->assign('PageTopic','Ruling Council Of ' . $RACES[$raceID]['Race Name']);

// echo menu
create_council_menu($raceID);

$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<p>We are at War/Peace<br />with the following races:</p>');

$PHP_OUTPUT.=('<table>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th width="150">Peace</th>');
$PHP_OUTPUT.=('<th width="150">War</th>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');

// peace
$PHP_OUTPUT.=('<td align="center" valign="top">');
$PHP_OUTPUT.=('<table>');

foreach ($RACES as $otherRaceID => $raceInfo)
{
	if($raceID != RACE_NEUTRAL && $raceID != $otherRaceID && $raceRelations[$otherRaceID] >= 300)
	{
		$container = create_container('skeleton.php', 'council_send_message.php');
		$container['race_id'] = $otherRaceID;
		$PHP_OUTPUT.=('<tr><td align="center">');
		$PHP_OUTPUT.=create_link($container, get_colored_text($raceRelations[$otherRaceID], $raceInfo['Race Name']));
		$PHP_OUTPUT.=('</td></tr>');
	}
}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</td>');

// war
$PHP_OUTPUT.=('<td align="center" valign="top">');
$PHP_OUTPUT.=('<table>');
foreach ($RACES as $otherRaceID => $raceInfo)
{
	if($raceID != RACE_NEUTRAL && $raceID != $otherRaceID && $raceRelations[$otherRaceID] <= -300)
	{
		$container = create_container('skeleton.php', 'council_send_message.php');
		$container['race_id'] = $otherRaceID;
		$PHP_OUTPUT.=('<tr><td align="center">');
		$PHP_OUTPUT.=create_link($container, get_colored_text($raceRelations[$otherRaceID], $raceInfo['Race Name']));
		$PHP_OUTPUT.=('</td></tr>');
	}
}
$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</td>');

$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');

?>