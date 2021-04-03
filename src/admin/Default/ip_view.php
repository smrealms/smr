<?php declare(strict_types=1);

$template->assign('PageTopic', 'IP Search');

$template->assign('IpFormHref', Page::create('skeleton.php', 'ip_view_results.php')->href());
