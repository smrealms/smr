<?php

$db2 = new SmrMySqlDatabase();
$db->query('SELECT * FROM galactic_post_paper WHERE paper_id = '.$var['id'].' AND game_id = '.$player->getGameID());
$db->nextRecord();
$paper_title = stripslashes($db->getField('title'));
$db->query('SELECT * FROM galactic_post_paper_content WHERE paper_id = '.$var['id'].' AND game_id = '.$player->getGameID());
include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_galactic_post_menue();
$PHP_OUTPUT.=($paper_title.'<br /><br /><ul>');
while ($db->nextRecord()) {

    $db2->query('SELECT * FROM galactic_post_article WHERE game_id = '.$player->getGameID().' AND article_id = ' . $db->getField('article_id'));
    $db2->nextRecord();
    $article_title = stripslashes($db2->getField('title'));
    $article_text = stripslashes($db2->getField('text'));
    $PHP_OUTPUT.=('<li>'.$article_title.'<br /><li>'.$article_text.'<br /></li>');
    $container = array();
    $container['url'] = 'galactic_post_paper_edit_processing.php';
    $container['article_id'] = $db->getField('article_id');
    transfer('id');
    $PHP_OUTPUT.=create_link($container, 'Remove this article from '.$paper_title.'</li>');
    $PHP_OUTPUT.=('<br /><br />');

}
$PHP_OUTPUT.=('</ul>');
if (!$db->getNumRows())
    $PHP_OUTPUT.=('This paper has no articles');

?>