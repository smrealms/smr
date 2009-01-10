<?

header('Content-Type: text/plain; charset=ISO-8859-1'.EOL);
header('Content-Disposition: attachment; filename=Game.ini'.EOL);
header('Content-transfer-encoding: base64'.EOL);

$db2 = new SmrMySqlDatabase();
$game_id = $_REQUEST['game_id'];
$db->query('SELECT * FROM game WHERE game_id = '.$game_id);
$db->next_record();

$PHP_OUTPUT.=('[Settings]'.EOL);
$PHP_OUTPUT.=('Name=' . $db->f('game_name') . EOL);
$PHP_OUTPUT.=('ID=' . $db->f('game_id') . EOL.EOL);

$PHP_OUTPUT.=('[Galaxy]'.EOL);
$db->query('SELECT galaxy_name, count(sector_id) as num FROM sector NATURAL JOIN galaxy WHERE game_id = '.$game_id.' GROUP BY sector.galaxy_id ORDER BY sector.sector_id');
while ($db->next_record()) {

	$name = $db->f('galaxy_name');
	$size = sqrt($db->f('num'));
	for ($i = strlen($size); $i < 3; $i++) $size = '0' . $size;
		$PHP_OUTPUT.=($name.'='.$size.';'.$size.EOL);

}

// expire all forces first
$db->query('DELETE FROM sector_has_forces WHERE expire_time < \'' . time() . '\'');

$PHP_OUTPUT.=(EOL.'[Marks]'.EOL);
$db->query('SELECT * FROM sector_has_forces WHERE game_id = '.$game_id.' GROUP BY sector_id ORDER BY sector_id');
while ($db->next_record()) {

	$owner =& SmrPlayer::getPlayer($db->f('owner_id'), $game_id);
	$user =& SmrPlayer::getPlayer($account->account_id, $game_id);
	$sector = $db->f('sector_id');

	if ($owner->alliance_id == $user->alliance_id)
		$PHP_OUTPUT.=($sector.'=2'.EOL);
	else
		$PHP_OUTPUT.=($sector.'=1'.EOL);

}

?>