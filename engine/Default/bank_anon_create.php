<?php

$template->assign('PageTopic', 'Create Anonymous Account');
Menu::bank();

$container = create_container('skeleton.php', 'bank_anon_create_processing.php');
$template->assign('CreateHREF', SmrSession::getNewHREF($container));
