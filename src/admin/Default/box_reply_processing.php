<?php declare(strict_types=1);

$message = trim(Request::get('message'));
$banPoints = Request::getInt('BanPoints');
$rewardCredits = Request::getInt('RewardCredits');
if (Request::get('action') == 'Preview message') {
	$container = create_container('skeleton.php', 'box_reply.php');
	$container['BanPoints'] = $banPoints;
	$container['RewardCredits'] = $rewardCredits;
	transfer('game_id');
	transfer('sender_id');
	transfer('box_type_id');
	$container['Preview'] = $message;
	forward($container);
}

SmrPlayer::sendMessageFromAdmin($var['game_id'], $var['sender_id'], $message);

$senderAccount = SmrAccount::getAccount($var['sender_id']);
$senderAccount->increaseSmrRewardCredits($rewardCredits);

//do we have points?
if ($banPoints > 0) {
	$suspicion = 'Inappropriate Actions';
	$senderAccount->addPoints($banPoints, $account, BAN_REASON_BAD_BEHAVIOR, $suspicion);
}

forward(create_container('skeleton.php', 'box_view.php'));
