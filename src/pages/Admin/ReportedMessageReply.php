<?php declare(strict_types=1);

use Smr\Messages;

		$template = Smr\Template::getInstance();
		$var = Smr\Session::getInstance()->getCurrentVar();

		$template->assign('PageTopic', 'Reply To Reported Messages');

		$container = Page::create('admin/notify_reply_processing.php');
		$container->addVar('game_id');
		$container->addVar('offended');
		$container->addVar('offender');
		$template->assign('NotifyReplyFormHref', $container->href());

		$offender = Messages::getMessagePlayer($var['offender'], $var['game_id']);
		if (is_object($offender)) {
			$offender = $offender->getDisplayName() . ' (Login: ' . $offender->getAccount()->getLogin() . ')';
		}
		$template->assign('Offender', $offender);

		$offended = Messages::getMessagePlayer($var['offended'], $var['game_id']);
		if (is_object($offended)) {
			$offended = $offended->getDisplayName() . ' (Login: ' . $offended->getAccount()->getLogin() . ')';
		}
		$template->assign('Offended', $offended);

		if (isset($var['PreviewOffender'])) {
			$template->assign('PreviewOffender', $var['PreviewOffender']);
		}
		if (isset($var['OffenderBanPoints'])) {
			$template->assign('OffenderBanPoints', $var['OffenderBanPoints']);
		}

		if (isset($var['PreviewOffended'])) {
			$template->assign('PreviewOffended', $var['PreviewOffended']);
		}
		if (isset($var['OffendedBanPoints'])) {
			$template->assign('OffendedBanPoints', $var['OffendedBanPoints']);
		}
