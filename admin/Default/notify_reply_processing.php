<?php declare(strict_types=1);
$offenderReply = trim(Request::get('offenderReply'));
$offenderBanPoints = Request::getInt('offenderBanPoints');
$offendedReply = trim(Request::get('offendedReply'));
$offendedBanPoints = Request::getInt('offendedBanPoints');
if (Request::get('action') == 'Preview messages') {
	$container = create_container('skeleton.php', 'notify_reply.php');
	transfer('offender');
	transfer('offended');
	transfer('game_id');
	transfer('sender_id');
	$container['PreviewOffender'] = $offenderReply;
	$container['OffenderBanPoints'] = $offenderBanPoints;
	$container['PreviewOffended'] = $offendedReply;
	$container['OffendedBanPoints'] = $offendedBanPoints;
	forward($container);
}


if ($offenderReply != '') {
	SmrPlayer::sendMessageFromAdmin($var['game_id'], $var['offender'], $offenderReply);

	//do we have points?
	if ($offenderBanPoints > 0) {
		$suspicion = 'Inappropriate In-Game Message';
		$offenderAccount = SmrAccount::getAccount($var['offender']);
		$offenderAccount->addPoints($offenderBanPoints, $account, BAN_REASON_BAD_BEHAVIOR, $suspicion);
	}
}

if ($offendedReply != '') {
	//next message
	SmrPlayer::sendMessageFromAdmin($var['game_id'], $var['offended'], $offendedReply);

	//do we have points?
	if ($offendedBanPoints > 0) {
		$suspicion = 'Inappropriate In-Game Message';
		$offenderAccount = SmrAccount::getAccount($var['offended']);
		$offenderAccount->addPoints($offendedBanPoints, $account, BAN_REASON_BAD_BEHAVIOR, $suspicion);
	}
}
forward(create_container('skeleton.php', 'notify_view.php'));
