<?php declare(strict_types=1);

$template->assign('PageTopic', 'Create Alliance');

$container = create_container('alliance_create_processing.php');
$template->assign('CreateHREF', SmrSession::getNewHREF($container));
