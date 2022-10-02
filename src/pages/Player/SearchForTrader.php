<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();

$template->assign('PageTopic', 'Search For Trader');
$template->assign('TraderSearchHREF', Page::create('trader_search_result.php')->href());

if (isset($var['empty_result'])) {
	$template->assign('EmptyResult', $var['empty_result'] === true);
}
