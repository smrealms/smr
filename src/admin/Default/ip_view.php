<?php declare(strict_types=1);

$template->assign('PageTopic', 'IP Search');

$template->assign('IpFormHref', SmrSession::getNewHREF(create_container('skeleton.php', 'ip_view_results.php')));
