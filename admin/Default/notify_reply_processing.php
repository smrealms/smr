<?php declare(strict_types=1);
$offenderReply = trim($_REQUEST['offenderReply']);
$offendedReply = trim($_REQUEST['offendedReply']);
if ($_REQUEST['action'] == 'Preview messages') {
	$container = create_container('skeleton.php', 'notify_reply.php');
	transfer('offender');
	transfer('offended');
	transfer('game_id');
	transfer('sender_id');
	if (!empty($offenderReply)) {
		$container['PreviewOffender'] = $offenderReply;
	}
	$container['OffenderBanPoints'] = $_REQUEST['offenderBanPoints'];

	if (!empty($offendedReply)) {
		$container['PreviewOffended'] = $offendedReply;
	}
	$container['OffendedBanPoints'] = $_REQUEST['offendedBanPoints'];
	forward($container);
}


if (isset($offenderReply) && $offenderReply != '') {
	SmrPlayer::sendMessageFromAdmin($var['game_id'], $var['offender'], $offenderReply);

	//do we have points?
	if ($_REQUEST['offenderBanPoints']) {
		$suspicion = 'Inappropriate In-Game Message';
		$offenderAccount = SmrAccount::getAccount($var['offender']);
		$offenderAccount->addPoints($_REQUEST['offenderBanPoints'], $account, 7, $suspicion);
	}
}
if (isset($_REQUEST['offendedReply'])) $offendedReply = $_REQUEST['offendedReply'];

if (isset($offendedReply) && $offendedReply != '') {
	//next message
	SmrPlayer::sendMessageFromAdmin($var['game_id'], $var['offended'], $offendedReply);

	//do we have points?
	if ($_REQUEST['offendedBanPoints']) {
		$suspicion = 'Inappropriate In-Game Message';
		$offenderAccount = SmrAccount::getAccount($var['offended']);
		$offenderAccount->addPoints($_REQUEST['offendedBanPoints'], $account, 7, $suspicion);
	}
}
forward(create_container('skeleton.php', 'notify_view.php'));
