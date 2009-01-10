<?
require_once(get_file_loc('smr_history_db.inc'));
//offer a back button
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'games_previous.php';
$db = new SMR_HISTORY_DB();
$db->query('SELECT * FROM game WHERE game_id = '.$var[$game_id]);
$db->next_record();
$game_id = $db->f('game_id');
$container['game_id'] = $game_id;
$container['game_name'] = $db->f('game_name');

//get alliance members
$id = $var['alliance_id'];
$PHP_OUTPUT.=($game_id.','. $id);
$db->query('SELECT * FROM alliance WHERE alliance_id = '.$id.' AND game_id = '.$game_id);
$db->next_record();
$PHP_OUTPUT.=('<div align=center>');
$PHP_OUTPUT.=create_link($container, '<b>&lt;&lt;Back</b>');
$smarty->assign('PageTopic','Alliance Roster - ' . stripslashes($db->f('alliance_name')));
$db->query('SELECT * FROM player WHERE alliance_id = '.$id.' AND game_id = '.$game_id.' ORDER BY experience DESC');

$PHP_OUTPUT.= '
<table cellspacing="0" cellpadding="0" class="standard">
	<tr>
		<th>Player Name</th>
		<th>Experience</th>
		<th>Alignment</th>
		<th>Race</th>
		<th>Kills</th>
		<th>Deaths</th>
		<th>Bounty</th>
	</tr>
';

while ($db->next_record()) {
	
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align=center>' . stripslashes($db->f('player_name')) . '</td>');
	$PHP_OUTPUT.=('<td align=center>' . $db->f('experience') . '</td>');
	$PHP_OUTPUT.=('<td align=center>' . $db->f('alignment') . '</td>');
	$PHP_OUTPUT.=('<td align=center>' . $db->f('race') . '</td>');
	$PHP_OUTPUT.=('<td align=center>' . $db->f('kills') . '</td>');
	$PHP_OUTPUT.=('<td align=center>' . $db->f('deaths') . '</td>');
	$PHP_OUTPUT.=('<td align=center>' . $db->f('bounty') . '</td>');
	$PHP_OUTPUT.=('</tr>');
	
}
$PHP_OUTPUT.=('</table></div>');
$db = new SmrMySqlDatabase();
?>