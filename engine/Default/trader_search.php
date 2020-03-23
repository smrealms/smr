<?php declare(strict_types=1);
$template->assign('PageTopic', 'Search For Trader');
$template->assign('TraderSearchHREF', SmrSession::getNewHREF(create_container('skeleton.php', 'trader_search_result.php')));

if (isset($var['empty_result'])) {
	$template->assign('EmptyResult', $var['empty_result'] === true);
}
