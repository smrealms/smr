<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

//view anon acct activity.
$template->assign('PageTopic', 'View Anonymous Account Info');

$container = Page::create('skeleton.php', 'admin/anon_acc_view.php');
$template->assign('AnonViewHREF', $container->href());

if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
}
