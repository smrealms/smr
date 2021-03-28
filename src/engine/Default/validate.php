<?php declare(strict_types=1);
if (isset($var['msg'])) {
	$template->assign('Message', $var['msg']);
}

$template->assign('PageTopic', 'Validation Reminder');
$template->assign('ValidateFormHref', Page::create('validate_processing.php')->href());
