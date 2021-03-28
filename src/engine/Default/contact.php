<?php declare(strict_types=1);

$template->assign('PageTopic', 'Contact Form');

$container = Page::create('contact_processing.php');
$template->assign('ProcessingHREF', $container->href());

$template->assign('From', $account->getLogin());
