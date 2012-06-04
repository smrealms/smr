<?

// check if our alignment is high enough
if ($player->getAlignment() <= -100) {
	create_error('You are not allowed to enter our Government HQ!');
	return;
}

// get the name of this facility
$db->query('SELECT * FROM location NATURAL JOIN location_type ' .
		   'WHERE game_id = '.$player->getGameID().' AND ' .
		   'sector_id = '.$player->getSectorID().' AND ' .
		   'location.location_type_id >= 103 AND ' .
		   'location.location_type_id <= 110');
if ($db->next_record()) {

	$location_type_id = $db->f('location_type_id');
	$location_name = $db->f('location_name');

	$race_id = $location_type_id - 101;

}

// did we get a result
if (empty($race_id)) {
  create_error('There is no headquarter. Obviously.');
  return;
}

// are we at war?
$db->query('SELECT * FROM race_has_relation WHERE game_id = '.SmrSession::$game_id.' AND race_id_1 = '.$race_id.' AND race_id_2 = '.$player->getRaceID());
if ($db->next_record() && $db->f('relation') <= -300) {
	create_error('We are at WAR with your race! Get outta here before I call the guards!');
	return;
}

// topic
if (isset($location_type_id))
	$smarty->assign('PageTopic',$location_name);
else
	$smarty->assign('PageTopic','FEDERAL HQ');

// header menue
include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_hq_menue();

// secondary db object
$db2 = new SMR_DB();

if (isset($location_type_id)) {

	$PHP_OUTPUT.=('<div align="center">We are at WAR with<br><br>');
	$db->query('SELECT * FROM race_has_relation WHERE game_id = '.$player->getGameID().' AND race_id_1 = '.$race_id);
	while($db->next_record()) {

		$relation = $db->f('relation');
		$race_2 = $db->f('race_id_2');

		$db2->query('SELECT * FROM race WHERE race_id = '.$race_2);
		$db2->next_record();
		$race_name = $db2->f('race_name');
		if ($relation <= -300)
			$PHP_OUTPUT.=('<span style="color:red;">The '.$race_name.'<br></span>');

	}

	$PHP_OUTPUT.=('<br>The government will PAY for the destruction of their ships!');

}

$db->query('SELECT * FROM bounty WHERE game_id = '.$player->getGameID().' AND type = \'HQ\' AND claimer_id = 0 ORDER BY amount DESC');

$PHP_OUTPUT.=('<p>&nbsp;</p>');
if ($db->nf()) {

	$PHP_OUTPUT.=('<div align="center">Most Wanted by Federal Government</div><br>');
	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Player Name</th>');
	$PHP_OUTPUT.=('<th>Bounty Amount</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->next_record()) {

		$id = $db->f('account_id');
		$db2->query('SELECT * FROM player WHERE game_id = '.$player->getGameID().' AND account_id = '.$id);
		if ($db2->next_record())
			$name = stripslashes($db2->f('player_name'));
		$amount = $db->f('amount');
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center"><font color=yellow>'.$name.'</font></td>');
		$PHP_OUTPUT.=('<td align="center"><font color=red> ' . number_format($amount) . ' </font></td>');
		$PHP_OUTPUT.=('</tr>');

	}

	$PHP_OUTPUT.=('</table>');

}

if ($player->getAlignment() >= -99 && $player->getAlignment() <= 100) {

	$PHP_OUTPUT.=create_echo_form(create_container('government_processing.php', ''));
	$PHP_OUTPUT.=create_submit('Become a deputy');
	$PHP_OUTPUT.=('</form>');

}

?>