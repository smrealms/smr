<?
require_once(get_file_loc('SmrHistoryMySqlDatabase.class.inc'));
//offer a back button
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'games_previous.php';
$db = new SmrHistoryMySqlDatabase();
$db->query('SELECT * FROM game WHERE game_id = '.$var[$game_id]);
$db->nextRecord();
$game_id = $db->getField('game_id');
$container['game_id'] = $game_id;
$container['game_name'] = $db->getField('game_name');

//get alliance members
$id = $var['alliance_id'];
$PHP_OUTPUT.=($game_id.','. $id);
$db->query('SELECT * FROM alliance WHERE alliance_id = '.$id.' AND game_id = '.$game_id);
$db->nextRecord();
$PHP_OUTPUT.=('<div align=center>');
$PHP_OUTPUT.=create_link($container, '<b>&lt;&lt;Back</b>');
$smarty->assign('PageTopic','Alliance Roster - ' . stripslashes($db->getField('alliance_name')));
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

while ($db->nextRecord()) {
	
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align=center>' . stripslashes($db->getField('player_name')) . '</td>');
	$PHP_OUTPUT.=('<td align=center>' . $db->getField('experience') . '</td>');
	$PHP_OUTPUT.=('<td align=center>' . $db->getField('alignment') . '</td>');
	$PHP_OUTPUT.=('<td align=center>' . $db->getField('race') . '</td>');
	$PHP_OUTPUT.=('<td align=center>' . $db->getField('kills') . '</td>');
	$PHP_OUTPUT.=('<td align=center>' . $db->getField('deaths') . '</td>');
	$PHP_OUTPUT.=('<td align=center>' . $db->getField('bounty') . '</td>');
	$PHP_OUTPUT.=('</tr>');
	
}
$PHP_OUTPUT.=('</table></div>');
$db = new SmrMySqlDatabase();
?>