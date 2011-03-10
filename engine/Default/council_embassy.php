<?php

if(!$player->isPresident())
{
	create_error('Only the president can view the embassy.');
}

require_once(get_file_loc('council.inc'));
require_once(get_file_loc('menue.inc'));

$template->assign('PageTopic','Ruling Council Of '.$player->getRaceName());

$PHP_OUTPUT.=create_council_menue($player->getRaceID());

$PHP_OUTPUT.=('<table class="standard" align="center" width="50%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Race</th>');
$PHP_OUTPUT.=('<th>Treaty</th>');
$PHP_OUTPUT.=('</tr>');

$db2 = new SmrMySqlDatabase();

$db->query('SELECT * FROM race ' .
		   'WHERE race_id != '.$player->getRaceID().' AND ' .
				 'race_id > 1');
while($db->nextRecord())
{

	$race_id	= $db->getField('race_id');
	$race_name	= $db->getField('race_name');

	$db2->query('SELECT * FROM race_has_voting ' .
				'WHERE game_id = '.$player->getGameID().' AND ' .
					  'race_id_1 = '.$player->getRaceID().' AND ' .
					  'race_id_2 = '.$race_id);
	if ($db2->getNumRows() > 0) continue;

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="center">' . $player->getColouredRaceName($race_id) . '</td>');

	$container = array();
	$container['url']		= 'council_embassy_processing.php';
	$container['race_id']	= $race_id;

	$PHP_OUTPUT.=create_echo_form($container);
	$PHP_OUTPUT.=('<td align="center">');
	$PHP_OUTPUT.=create_submit('Peace');
	$PHP_OUTPUT.=('&nbsp;');
	$PHP_OUTPUT.=create_submit('War');
	$PHP_OUTPUT.=('</td>');
	$PHP_OUTPUT.=('</form>');

	$PHP_OUTPUT.=('</tr>');

}

$PHP_OUTPUT.=('</table>');

?>