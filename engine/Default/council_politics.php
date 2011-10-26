<?php

require_once(get_file_loc('council.inc'));
require_once(get_file_loc('menu.inc'));

// echo topic
$race_id = $var['race_id'];
if (empty($race_id))
	$race_id = $player->getRaceID();

$db->query('SELECT * FROM race ' .
		   'WHERE race_id = '.$race_id);
if ($db->nextRecord())
	$template->assign('PageTopic','Ruling Council Of ' . $db->getField('race_name'));

// echo menue
$PHP_OUTPUT.=create_council_menue($race_id);

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
$db->query('SELECT race_name, race.race_id as race_id, relation FROM race_has_relation, race ' .
		   'WHERE race_has_relation.race_id_2 = race.race_id AND ' .
				 'race_has_relation.race_id_1 = '.$race_id.' AND ' .
				 'race_has_relation.race_id_1 != race_has_relation.race_id_2 AND ' .
				 'race_has_relation.relation >= 300 AND ' .
				 'race_has_relation.game_id = '.$player->getGameID());
while ($db->nextRecord())
{
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'council_send_message.php';
	$container['race_id'] = $db->getField('race_id');
	$PHP_OUTPUT.=('<tr><td align="center">');
	$PHP_OUTPUT.=create_link($container, get_colored_text($db->getField('relation'), $db->getField('race_name')));
	$PHP_OUTPUT.=('</td></tr>');
}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</td>');

// war
$PHP_OUTPUT.=('<td align="center" valign="top">');
$PHP_OUTPUT.=('<table>');
$db->query('SELECT race_name, race.race_id as race_id, relation FROM race_has_relation, race ' .
		   'WHERE race_has_relation.race_id_2 = race.race_id AND ' .
				 'race_has_relation.race_id_1 = '.$race_id.' AND ' .
				 'race_has_relation.race_id_1 != race_has_relation.race_id_2 AND ' .
				 'race_has_relation.relation <= -300 AND ' .
				 'race_has_relation.game_id = '.$player->getGameID());
while ($db->nextRecord())
{
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'council_send_message.php';
	$container['race_id'] = $db->getField('race_id');
	$PHP_OUTPUT.=('<tr><td align="center">');
	$PHP_OUTPUT.=create_link($container, get_colored_text($db->getField('relation'), $db->getField('race_name')));
	$PHP_OUTPUT.=('</td></tr>');
}
$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</td>');

$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');

?>