<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template = Smr\Template::getInstance();
$template->assign('PageTopic', 'Error');
$template->assign('Message', $var['message']);
