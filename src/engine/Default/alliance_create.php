<?php declare(strict_types=1);

$template->assign('PageTopic', 'Create Alliance');

$container = Page::create('alliance_create_processing.php');
$template->assign('CreateHREF', $container->href());
