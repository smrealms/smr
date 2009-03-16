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
if ($db->nextRecord()) {

	$location_type_id = $db->getField('location_type_id');
	$location_name = $db->getField('location_name');

	$race_id = $location_type_id - 101;

}

// did we get a result
if (empty($race_id)) {
  create_error('There is no headquarter. Obviously.');
  return;
}

// are we at war?
$db->query('SELECT * FROM race_has_relation WHERE game_id = '.SmrSession::$game_id.' AND race_id_1 = '.$race_id.' AND race_id_2 = '.$player->getRaceID());
if ($db->nextRecord() && $db->getField('relation') <= -300) {
	create_error('We are at WAR with your race! Get outta here before I call the guards!');
	return;
}

// topic
if (isset($location_type_id))
	$template->assign('PageTopic',$location_name);
else
	$template->assign('PageTopic','FEDERAL HQ');

// header menue
include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_hq_menue();

// secondary db object
$db2 = new SmrMySqlDatabase();

if (isset($location_type_id))
{
	$PHP_OUTPUT.=('<div align="center">We are at WAR with<br /><br />');
	$db->query('SELECT * FROM race_has_relation WHERE game_id = '.$player->getGameID().' AND race_id_1 = '.$race_id);
	while($db->nextRecord())
	{
		$relation = $db->getField('relation');
		$race_2 = $db->getField('race_id_2');

		$db2->query('SELECT * FROM race WHERE race_id = '.$race_2);
		$db2->nextRecord();
		$race_name = $db2->getField('race_name');
		if ($relation <= -300)
			$PHP_OUTPUT.=('<span style="color:red;">The '.$race_name.'<br /></span>');

	}

	$PHP_OUTPUT.=('<br />The government will PAY for the destruction of their ships!');

}

require_once(get_file_loc('gov.functions.inc'));
displayBountyList($PHP_OUTPUT,'HQ',0);
displayBountyList($PHP_OUTPUT,'HQ',$player->getAccountID());


if ($player->getAlignment() >= -99 && $player->getAlignment() <= 100)
{
	$PHP_OUTPUT.=create_echo_form(create_container('government_processing.php', ''));
	$PHP_OUTPUT.=create_submit('Become a deputy');
	$PHP_OUTPUT.=('</form>');
}

?>