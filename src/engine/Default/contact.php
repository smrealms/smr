<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$account = $session->getAccount();

$template->assign('PageTopic', 'Contact Form');

$container = Page::create('contact_processing.php');
$template->assign('ProcessingHREF', $container->href());

$template->assign('From', $account->getLogin());
