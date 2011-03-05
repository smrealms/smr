<?php
require_once(get_file_loc('smr_alliance.inc'));
$alliance = new SMR_ALLIANCE($player->getAllianceID(), SmrSession::$game_id);
$account_id = $_REQUEST['account_id'];
foreach ($account_id as $id)
{
	
	if ($id == $alliance->leader_id)
		create_error('You can\'t remove the leader!');
	// generate list of messages that should be deleted
    if ($account_id_list) $account_id_list .= ',';
    $account_id_list .= $id;

	$player->sendMessage($id, 2, 'You were kicked out of the alliance!', false);
	$curr_acc =& SmrAccount::getAccount($id);
	$curr_acc->log(3, 'kicked from alliance: '.$alliance->alliance_name.' by leader', 0);

}

if(empty($account_id_list))
{
	create_error('You have to choose someone to remove them!');
}
$db->query('UPDATE player SET alliance_id = 0, sector_id = 1, newbie_turns = GREATEST(1,newbie_turns) WHERE account_id IN ('.$account_id_list.') AND ' .
												   'game_id = '.$player->getGameID());
$db->query('DELETE FROM player_has_alliance_role WHERE game_id = '.$player->getGameID().' AND account_id IN ('.$account_id_list.')');

forward(create_container('skeleton.php', 'alliance_roster.php'));

?>