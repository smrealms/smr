<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Reply To Reported Messages');

require_once(get_file_loc('messages.inc.php'));

$container = Page::create('notify_reply_processing.php');
$container->addVar('game_id');
$container->addVar('offended');
$container->addVar('offender');
$template->assign('NotifyReplyFormHref', $container->href());
$offender = getMessagePlayer($var['offender'], $var['game_id']);
$offended = getMessagePlayer($var['offended'], $var['game_id']);
if (is_object($offender)) {
	$template->assign('OffenderAccount', $offender->getAccount());
}
if (is_object($offended)) {
	$template->assign('OffendedAccount', $offended->getAccount());
}
$template->assign('Offender', $offender);
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
