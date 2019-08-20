<?php declare(strict_types=1);

$location = SmrLocation::getLocation($var['LocationID']);
$template->assign('PageTopic', $location->getName());
$template->assign('ThisLocation', $location);
