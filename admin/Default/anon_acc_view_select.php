<?php declare(strict_types=1);

//view anon acct activity.
$template->assign('PageTopic', 'View Anonymous Account Info');

$container = create_container('skeleton.php', 'anon_acc_view.php');
$template->assign('AnonViewHREF', SmrSession::getNewHREF($container));

if (isset($var['message'])) {
	$template->assign('Message', $var['message']);
}
