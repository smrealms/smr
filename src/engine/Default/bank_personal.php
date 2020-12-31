<?php declare(strict_types=1);

// is account validated?
if (!$account->isValidated()) {
	create_error('You are not validated so you cannot use banks.');
}

$template->assign('PageTopic', 'Bank');

Menu::bank();

$container = create_container('bank_personal_processing.php');
$template->assign('ProcessingHREF', SmrSession::getNewHREF($container));
