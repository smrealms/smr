<?php declare(strict_types=1);
if (isset($var['msg'])) {
	$template->assign('Message', $var['msg']);
}

$template->assign('PageTopic', 'Validation Reminder');
$template->assign('ValidateFormHref', SmrSession::getNewHREF(create_container('validate_processing.php')));
