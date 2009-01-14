<?
require_once(get_file_loc('SmrHistoryMySqlDatabase.class.inc'));
//get vars
$action = $_REQUEST['action'];
$row = $var['row'];
$rank = 1;
$cat = $var['category'];
$mod = $_REQUEST['mod'];
if (isset($var['game_id'])) $game_id = $var['game_id'];
//do we need to mod stat?
if (is_array($mod))
	foreach($mod as $mod1) {

		if (!stristr($mod1,$action)) continue;
		list($one, $two) = split (',', $mod1);
		$row .= $two;
		break;
	}

//for future when we have curr game stats
if (isset($game_id)) {

	$table = 'player_has_stats WHERE game_id = '.$game_id.' AND';

	$db2 = new SmrHistoryMySqlDatabase();
	$db2->query('SELECT * FROM game WHERE game_id = '.$game_id);
	//if next record we have an old game so we query the hist db
	if ($db2->nextRecord()) {

		$db = new SmrHistoryMySqlDatabase();
		$past = 'Yes';
		$table = 'player_has_stats WHERE game_id = '.$game_id.' AND';

	} else $db = new SmrMySqlDatabase();

}
else $table = 'account_has_stats WHERE';
$PHP_OUTPUT.=('<div align=center>');
$smarty->assign('PageTopic','Hall of Fame - '.$cat.' '.$action);
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'hall_of_fame_new.php';
if (isset($game_id))
	$container['game_id'] = $game_id;
$PHP_OUTPUT.=create_link($container, '<b>&lt;&lt;Back</b>');
$PHP_OUTPUT.=('<br />');
$PHP_OUTPUT.=('Here are the ranks of players by '.$cat.' '.$action.'<br /><br />');
$PHP_OUTPUT.=create_table();
$PHP_OUTPUT.=('<tr><th align=center>Rank</th><th align=center>Player</th><th align=center>'.$cat.' '.$action.'</th></tr>');
if ($cat == '<b>Money Donated to SMR</b>')
	$db->query('SELECT account_id, sum(amount) as amount FROM account_donated ' .
			'GROUP BY account_id ORDER BY amount DESC LIMIT 25');
else
	$db->query('SELECT account_id, '.$row.' as amount FROM '.$table.' '.$row.' > 0 ORDER BY amount DESC LIMIT 25');

while ($db->nextRecord()) {

	$db_acc =& SmrAccount::getAccount($db->getField('account_id'));
	if ($db->getField('account_id') == SmrSession::$account_id) $bold = ' style="font-weight:bold;"';
	else $bold = '';
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align=center'.$bold.'>' . $rank++ . '</td>');
	//link to stat page
	$container = array();
	$container['url'] = 'skeleton.php';
	$container['body'] = 'hall_of_fame_player_detail.php';
	$container['acc_id'] = $db->getField('account_id');
	
	if (isset($game_id)) {
		$container['game_id'] = $game_id;
		$container['sending_page'] = 'current_hof';
	} else {
		$container['game_id'] = $player->getGameID();
		$container['sending_page'] = 'hof';
	}
	$PHP_OUTPUT.=('<td align=center$bold>');
	$hof_name = stripslashes($db_acc->HoF_name);
	$PHP_OUTPUT.=create_link($container, $hof_name);
	$PHP_OUTPUT.=('</td>');
	if($cat == 'Turns Since Last Death') $PHP_OUTPUT.=('<td align=center'.$bold.'>' . $db->getField('amount') . '</td>');
	else $PHP_OUTPUT.=('<td align=center'.$bold.'>' . $db->getField('amount') . '</td>');
	$PHP_OUTPUT.=('</tr>');

}

//our rank goes here if we aren't shown...first get our value
if (isset($past)) $db = new SmrHistoryMySqlDatabase();
if ($cat == '<b>Money Donated to SMR</b>')
	$db->query('SELECT account_id, sum(amount) as amount FROM account_donated ' .
			'WHERE account_id = '.SmrSession::$account_id.' GROUP BY account_id');
else
	$db->query('SELECT account_id, '.$row.' as amount FROM '.$table.' ' .
			$row.' > 0 AND account_id = '.SmrSession::$account_id.' ORDER BY amount DESC');
if ($db->nextRecord()) {

	$my_stat = $db->getField('amount');
	if ($cat == '<b>Money Donated to SMR</b>')
		$db->query('SELECT account_id, sum(amount) as amount FROM account_donated ' .
				'WHERE amount > '.$my_stat.' GROUP BY account_id ORDER BY amount DESC');
	else
		$db->query('SELECT account_id, '.$row.' as amount FROM '.$table.' ' .
				$row.' > '.$my_stat.' ORDER BY amount DESC');

	$better = $db->getNumRows();

} else {

	$my_stat = 0;
	if ($cat == '<b>Money Donated to SMR</b>')
		$db->query('SELECT account_id, sum(amount) as amount FROM account_donated ' .
				'GROUP BY account_id ORDER BY amount DESC');
	else
		$db->query('SELECT account_id, '.$row.' as amount FROM '.$table.' '.$row.' > 0 ORDER BY amount DESC');

	$better = $db->getNumRows();

}
if ($better >= 25) {

	if (isset($past)) $sql = 'game_id = '.$game_id.' ';
	else $sql = '';
	if(isset($past)) {
		$db->query('SELECT * FROM player_has_stats WHERE '.$sql.' AND account_id = '.$account->account_id);
	}
	else {
		$db->query('SELECT * FROM player_has_stats WHERE '.$sql.' AND account_id = '.$account->account_id);
	}
	if ($db->nextRecord()) {

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align=center style="font-weight:bold;">' . ++$better . '</td>');
		$PHP_OUTPUT.=('<td align=center style="font-weight:bold;">' . stripslashes($account->HoF_name) . '</td>');
		$PHP_OUTPUT.=('<td align=center style="font-weight:bold;">'.$my_stat.'</td>');
		$PHP_OUTPUT.=('</tr>');

	}

}
$PHP_OUTPUT.=('</table>');

$PHP_OUTPUT.=('</div>');
$db = new SmrMySqlDatabase();
?>
