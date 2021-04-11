<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

if (isset($var['msg'])) {
	$template->assign('Message', $var['msg']);
}

$template->assign('PageTopic', 'Validation Reminder');
$template->assign('ValidateFormHref', Page::create('validate_processing.php')->href());
