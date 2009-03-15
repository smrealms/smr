<?

//first message
if (isset($_REQUEST['offenderReply'])) $offenderReply = $_REQUEST['offenderReply'];

if (isset($offenderReply) && $offenderReply != '')
{
	SmrPlayer::sendMessageFromAdmin($var['game_id'], $var['offender'], $offenderReply);
	
	//do we have points?
	if ($_REQUEST['offenderBanPoints'])
	{
		$reasonID = 7;
		$suspicion = 'Inappropriate In-Game Message';
		$offenderAccount =& SmrAccount::getAccount($var['offender']);
		$offenderAccount->addPoints($_REQUEST['offenderBanPoints'],$account,$reasonID,$suspicion);
	}
}
if (isset($_REQUEST['offendedReply'])) $offendedReply = $_REQUEST['offendedReply'];

if (isset($offendedReply) && $offendedReply != '')
{
	//next message
	SmrPlayer::sendMessageFromAdmin($var['game_id'], $var['offended'], $offendedReply);

	//do we have points?
	if ($_REQUEST['offendedBanPoints'])
	{
		$reasonID = 7;
		$suspicion = 'Inappropriate In-Game Message';
		$offenderAccount =& SmrAccount::getAccount($var['offended']);
		$offenderAccount->addPoints($_REQUEST['offendedBanPoints'],$account,$reasonID,$suspicion);
	}
}
forward(create_container('skeleton.php', 'notify_view.php'));

?>