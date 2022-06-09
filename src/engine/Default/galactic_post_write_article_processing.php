<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$title = Smr\Request::get('title');
$message = Smr\Request::get('message');
if (!$player->isGPEditor()) {
	$title = htmlentities($title, ENT_COMPAT, 'utf-8');
	$message = htmlentities($message, ENT_COMPAT, 'utf-8');
}

if (Smr\Request::get('action') == 'Preview article') {
	$container = Page::create('galactic_post_write_article.php');
	$container['PreviewTitle'] = $title;
	$container['Preview'] = $message;
	if (isset($var['id'])) {
		$container->addVar('id');
	}
	$container->go();
}

$db = Smr\Database::getInstance();
if (isset($var['id'])) {
	// Editing an article
	$db->write('UPDATE galactic_post_article SET last_modified = ' . $db->escapeNumber(Smr\Epoch::time()) . ', text = ' . $db->escapeString($message) . ', title = ' . $db->escapeString($title) . ' WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($var['id']));
	Page::create('galactic_post_view_article.php')->go();
} else {
	// Adding a new article
	$message = 'Dear Galactic Post editors,<br /><br />[player=' . $player->getPlayerID() . '] has just submitted an article to the Galactic Post!';
	foreach (Globals::getGalacticPostEditorIDs($player->getGameID()) as $editorID) {
		if ($editorID != $player->getAccountID()) {
			SmrPlayer::sendMessageFromAdmin($player->getGameID(), $editorID, $message);
		}
	}

	$dbResult = $db->read('SELECT IFNULL(MAX(article_id)+1, 0) AS next_article_id FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
	$num = $dbResult->record()->getInt('next_article_id');

	$db->insert('galactic_post_article', [
		'game_id' => $db->escapeNumber($player->getGameID()),
		'article_id' => $db->escapeNumber($num),
		'writer_id' => $db->escapeNumber($player->getAccountID()),
		'title' => $db->escapeString($title),
		'text' => $db->escapeString($message),
		'last_modified' => $db->escapeNumber(Smr\Epoch::time()),
	]);
	$db->write('UPDATE galactic_post_writer SET last_wrote = ' . $db->escapeNumber(Smr\Epoch::time()) . ' WHERE account_id = ' . $db->escapeNumber($player->getAccountID()));
	Page::create('galactic_post_read.php')->go();
}
