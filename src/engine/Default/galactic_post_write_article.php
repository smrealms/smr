<?php declare(strict_types=1);

$template = Smr\Template::getInstance();
$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

Menu::galacticPost();
$container = Page::create('galactic_post_write_article_processing.php');

if (isset($var['id'])) {
	$container->addVar('id');
	$template->assign('PageTopic', 'Editing An Article');
	if (!isset($var['Preview'])) {
		$db = Smr\Database::getInstance();
		$dbResult = $db->read('SELECT title, text FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($var['id']));
		if ($dbResult->hasRecord()) {
			$dbRecord = $dbResult->record();
			$var['PreviewTitle'] = $dbRecord->getString('title');
			$var['Preview'] = $dbRecord->getString('text');
		}
	}
} else {
	$template->assign('PageTopic', 'Writing An Article');
}
if (isset($var['Preview'])) {
	$template->assign('PreviewTitle', $var['PreviewTitle']);
	$template->assign('Preview', $var['Preview']);
}
$template->assign('SubmitArticleHref', $container->href());
