<?php
$template->assign('PageTopic','Reply To Reported Messages');

require_once(get_file_loc('message.functions.inc'));

$container = array();
$container['url']        = 'notify_reply_processing.php';
transfer('game_id');
transfer('offended');
transfer('offender');
$template->assign('NotifyReplyFormHref',SmrSession::getNewHREF($container));
$offender =& getMessagePlayer($var['offender'],$var['game_id']);
$offended =& getMessagePlayer($var['offended'],$var['game_id']);
if(is_object($offender))
	$template->assignByRef('OffenderAccount', SmrAccount::getAccount($var['offender']));
if(is_object($offended))
	$template->assignByRef('OffendedAccount', SmrAccount::getAccount($var['offended']));
$template->assignByRef('Offender', $offender);
$template->assignByRef('Offended', $offended);

if(isset($var['PreviewOffender']))
	$template->assign('PreviewOffender', $var['PreviewOffender']);
if(isset($var['OffenderBanPoints']))
	$template->assign('OffenderBanPoints', $var['OffenderBanPoints']);
	
if(isset($var['PreviewOffended']))
	$template->assign('PreviewOffended', $var['PreviewOffended']);
if(isset($var['OffendedBanPoints']))
	$template->assign('OffendedBanPoints', $var['OffendedBanPoints']);
?>