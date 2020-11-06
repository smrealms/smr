<?php declare(strict_types=1);
$template->assign('PageTopic', 'Reply To Reported Messages');

require_once(get_file_loc('message.functions.inc'));

$container = create_container('notify_reply_processing.php');
transfer('game_id');
transfer('offended_player_id');
transfer('offender_player_id');
$template->assign('NotifyReplyFormHref', SmrSession::getNewHREF($container));
$offender = getMessagePlayer($var['offender_player_id'], $var['game_id']);
$offended = getMessagePlayer($var['offended_player_id'], $var['game_id']);
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
