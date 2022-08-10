<?php declare(strict_types=1);

use Smr\Request;

		$session = Smr\Session::getInstance();
		$var = $session->getCurrentVar();
		$account = $session->getAccount();

		$message = Request::get('message');
		$banPoints = Request::getInt('BanPoints');
		$rewardCredits = Request::getInt('RewardCredits');
		if (Request::get('action') == 'Preview message') {
			$container = Page::create('admin/box_reply.php');
			$container['BanPoints'] = $banPoints;
			$container['RewardCredits'] = $rewardCredits;
			$container->addVar('game_id');
			$container->addVar('sender_id');
			$container->addVar('box_type_id');
			$container['Preview'] = $message;
			$container->go();
		}

		SmrPlayer::sendMessageFromAdmin($var['game_id'], $var['sender_id'], $message);

		$senderAccount = SmrAccount::getAccount($var['sender_id']);
		$senderAccount->increaseSmrRewardCredits($rewardCredits);

		//do we have points?
		if ($banPoints > 0) {
			$suspicion = 'Inappropriate Actions';
			$senderAccount->addPoints($banPoints, $account, BAN_REASON_BAD_BEHAVIOR, $suspicion);
		}

		Page::create('admin/box_view.php')->go();
