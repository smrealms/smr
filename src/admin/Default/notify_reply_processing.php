<?php declare(strict_types=1);
$offenderReply = trim(Request::get('offenderReply'));
$offenderBanPoints = Request::getInt('offenderBanPoints');
$offendedReply = trim(Request::get('offendedReply'));
$offendedBanPoints = Request::getInt('offendedBanPoints');
if (Request::get('action') == 'Preview messages') {
	$container = Page::create('skeleton.php', 'notify_reply.php');
	$container->addVar('offender');
	$container->addVar('offended');
	$container->addVar('game_id');
	$container->addVar('sender_id');
	$container['PreviewOffender'] = $offenderReply;
	$container['OffenderBanPoints'] = $offenderBanPoints;
	$container['PreviewOffended'] = $offendedReply;
	$container['OffendedBanPoints'] = $offendedBanPoints;
	$container->go();
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
Page::create('skeleton.php', 'notify_view.php')->go();
