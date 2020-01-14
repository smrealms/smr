<?php declare(strict_types=1);

$title = trim(Request::get('title'));
$message = trim(Request::get('message'));
if (!$player->isGPEditor()) {
	$title = htmlentities($title, ENT_COMPAT, 'utf-8');
	$message = htmlentities($message, ENT_COMPAT, 'utf-8');
}

if (Request::get('action') == 'Preview article') {
	$container = create_container('skeleton.php', 'galactic_post_write_article.php');
	$container['PreviewTitle'] = $title;
	$container['Preview'] = $message;
	if (isset($var['id'])) {
		$container['id'] = $var['id'];
	}
	forward($container);
}

if (isset($var['id'])) {
	// Editing an article
	$db->query('UPDATE galactic_post_article SET last_modified = ' . $db->escapeNumber(TIME) . ', text = ' . $db->escapeString($message) . ', title = ' . $db->escapeString($title) . ' WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($var['id']));
	forward(create_container('skeleton.php', 'galactic_post_view_article.php'));
} else {
	// Adding a new article
	$message = 'Dear Galactic Post editors,<br /><br />[player=' . $player->getPlayerID() . '] has just submitted an article to the Galactic Post!';
	foreach (Globals::getGalacticPostEditorIDs($player->getGameID()) as $editorID) {
		if ($editorID != $player->getAccountID()) {
			SmrPlayer::sendMessageFromAdmin($player->getGameID(), $editorID, $message);
		}
	}

	$db->query('SELECT MAX(article_id) article_id FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' LIMIT 1');
	$db->nextRecord();
	$num = $db->getInt('article_id') + 1;
	$db->query('INSERT INTO galactic_post_article (game_id, article_id, writer_id, title, text, last_modified) VALUES (' . $db->escapeNumber($player->getGameID()) . ', ' . $db->escapeNumber($num) . ', ' . $db->escapeNumber($player->getAccountID()) . ', ' . $db->escapeString($title) . ' , ' . $db->escapeString($message) . ' , ' . $db->escapeNumber(TIME) . ')');
	$db->query('UPDATE galactic_post_writer SET last_wrote = ' . $db->escapeNumber(TIME) . ' WHERE account_id = ' . $db->escapeNumber($account->getAccountID()));
	forward(create_container('skeleton.php', 'galactic_post_read.php'));
}
