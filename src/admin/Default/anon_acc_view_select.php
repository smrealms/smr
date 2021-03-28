<?php declare(strict_types=1);

//view anon acct activity.
$template->assign('PageTopic', 'View Anonymous Account Info');

$container = Page::create('skeleton.php', 'anon_acc_view.php');
$template->assign('AnonViewHREF', $container->href());

if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
}
