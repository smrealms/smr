<?php declare(strict_types=1);

$template->assign('PageTopic', 'Create Anonymous Account');
Menu::bank();

$container = create_container('bank_anon_create_processing.php');
$template->assign('CreateHREF', SmrSession::getNewHREF($container));
