<?

include(get_file_loc('council.inc'));
include($ENGINE . 'global/menue.inc');

$smarty->assign('PageTopic','RULING COUNCIL OF '.$player->getRaceName());

$PHP_OUTPUT.=create_council_menue($player->getRaceID(), getPresident($player->getRaceID()));

$PHP_OUTPUT.=('<table border="0" class="standard" cellspacing="0" align="center" width="50%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Race</th>');
$PHP_OUTPUT.=('<th>Treaty</th>');
$PHP_OUTPUT.=('</tr>');

$db2 = new SMR_DB();

$db->query('SELECT * FROM race ' .
		   'WHERE race_id != '.$player->getRaceID().' AND ' .
				 'race_id > 1');
while($db->next_record())
{

	$race_id	= $db->f('race_id');
	$race_name	= $db->f('race_name');

	$db2->query('SELECT * FROM race_has_voting ' .
				'WHERE game_id = '.$player->getGameID().' AND ' .
					  'race_id_1 = '.$player->getRaceID().' AND ' .
					  'race_id_2 = '.$race_id);
	if ($db2->nf() > 0) continue;

	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="center">' . $player->getColoredRaceName($race_id) . '</td>');

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