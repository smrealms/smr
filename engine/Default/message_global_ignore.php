<?php

$value = strtoupper($_POST['action']);

$player->setIgnoreGlobals($value == 'YES');

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'message_view.php';
forward($container);
