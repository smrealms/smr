<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$var = Smr\Session::getInstance()->getCurrentVar();

$raceName = Smr\Race::getName($var['race_id']);
$template->assign('RaceName', $raceName);

$template->assign('PageTopic', 'Send message to Ruling Council of the ' . $raceName);

Menu::messages();

$container = Page::create('council_send_message_processing.php');
$container->addVar('race_id');
$template->assign('SendHREF', $container->href());
