<?php

$template->assign('PageTopic', 'Create Multi Account');

$container = create_container('switch_account_create_processing.php');
$container['action'] = 'Create';
$template->assign('CreateHREF', SmrSession::getNewHREF($container));
$container['action'] = 'Link';
$template->assign('LinkHREF', SmrSession::getNewHREF($container));
