<?php

$db2 = new SmrMySqlDatabase();
$db->query('SELECT * FROM galactic_post_paper WHERE paper_id = '.$db->escapeNumber($var['id']).' AND game_id = ' . $db->escapeNumber($player->getGameID()));
$db->nextRecord();
$paper_title = bbifyMessage($db->getField('title'));
$db->query('SELECT * FROM galactic_post_paper_content WHERE paper_id = '.$db->escapeNumber($var['id']).' AND game_id = ' . $db->escapeNumber($player->getGameID()));
require_once(get_file_loc('menu.inc'));
create_galactic_post_menu();
$PHP_OUTPUT.=($paper_title.'<br /><br /><ul>');
while ($db->nextRecord()) {
	$db2->query('SELECT * FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($db->getInt('article_id')));
	$db2->nextRecord();
	$PHP_OUTPUT.='<li>'.bbifyMessage($db2->getField('title')).'<br /><li>'.bbifyMessage($db2->getField('text')).'<br /></li>';
	$container = create_container('galactic_post_paper_edit_processing.php');
	$container['article_id'] = $db->getField('article_id');
	transfer('id');
	$PHP_OUTPUT.=create_link($container, 'Remove this article from '.$paper_title.'</li>');
	$PHP_OUTPUT.=('<br /><br />');

}
$PHP_OUTPUT.=('</ul>');
if (!$db->getNumRows())
	$PHP_OUTPUT.=('This paper has no articles');
