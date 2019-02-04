<?php

$raceName = Globals::getRaceName($var['race_id']);
$template->assign('RaceName', $raceName);

$template->assign('PageTopic','Send message to Ruling Council of the '.$raceName);

Menu::messages();

$container = create_container('council_send_message_processing.php');
transfer('race_id');
$template->assign('SendHREF', SmrSession::getNewHREF($container));
