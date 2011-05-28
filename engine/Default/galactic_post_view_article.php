<?php
$template->assign('PageTopic','Viewing Articles');
include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_galactic_post_menue();
$db2 = new SmrMySqlDatabase();
if (isset($var['news']))
{
	$db->query('INSERT INTO news (game_id, time, news_message, type) ' .
		'VALUES('.$player->getGameID().', ' . TIME . ', ' . $db->escape_string($var['news'], false) . ', \'BREAKING\')');
}
$db->query('SELECT * FROM galactic_post_article WHERE game_id = '.$player->getGameID());
if ($db->getNumRows())
{
    $PHP_OUTPUT.=('It is your responsibility to make sure ALL HTML tages are closed!<br />');
    $PHP_OUTPUT.=('You have the following articles to view.<br /><br />');
}
else
    $PHP_OUTPUT.=('There are no articles to view');
    
while ($db->nextRecord())
{
    $db2->query('SELECT * FROM galactic_post_paper_content WHERE game_id = '.$player->getGameID().' AND article_id = ' . $db->getField('article_id'));
    if (!$db2->nextRecord())
    {
        $title = stripslashes($db->getField('title'));
        $writter =& SmrPlayer::getPlayer($db->getField('writer_id'), $player->getGameID());
        $container = array();
        $container['url'] = 'skeleton.php';
        $container['body'] = 'galactic_post_view_article.php';
        $container['id'] = $db->getField('article_id');
        $PHP_OUTPUT.=create_link($container, '<span class="yellow">'.$title.'</span> written by '.$writter->getPlayerName());
        $PHP_OUTPUT.=('<br />');
    }
}
$PHP_OUTPUT.=('<br /><br />');
if (isset($var['id']))
{
    $db->query('SELECT * FROM galactic_post_article WHERE game_id = '.$player->getGameID().' AND article_id = '.$var['id']);
    $db->nextRecord();
    $title = stripslashes($db->getField('title'));
    $message = stripslashes($db->getField('text'));
    $PHP_OUTPUT.=($title);
    $PHP_OUTPUT.=('<br /><br />'.$message.'<br />');
    $PHP_OUTPUT.=('<br />');
    $container = array();
    $container['url'] = 'skeleton.php';
    $container['body'] = 'galactic_post_write_article.php';
    transfer('id');
    $PHP_OUTPUT.=create_link($container, '<b>Edit this article</b>');
    $PHP_OUTPUT.=('<br />');
    $container = array();
    $container['url'] = 'skeleton.php';
    $container['body'] = 'galactic_post_delete_confirm.php';
    $container['article'] = 'yes';
    transfer('id');
    $PHP_OUTPUT.=create_link($container, '<b>Delete This article</b>');
    $PHP_OUTPUT.=('<br /><br />');
    $db->query('SELECT * FROM galactic_post_paper WHERE game_id = '.SmrSession::$game_id);
    $container = array();
    $container['url'] = 'galactic_post_add_article_to_paper.php';
    transfer('id');
    if (!$db->getNumRows())
    {
        $PHP_OUTPUT.=('You have no papers made that you can add an article to.');
        $PHP_OUTPUT.=create_link(create_container('skeleton.php', 'galactic_post_make_paper.php'), '<b>Click Here</b>');
        $PHP_OUTPUT.=('To make a new one.');
    }
    while ($db->nextRecord())
    {
        $paper_title = $db->getField('title');
        $paper_id = $db->getField('paper_id');
        $container['paper_id'] = $paper_id;
        $PHP_OUTPUT.=create_link($container, '<b>Add this article to '.$paper_title.'!</b>');
        $PHP_OUTPUT.=('<br />');
    }
    $container = array();
    $container['url'] = 'skeleton.php';
    $container['body'] = 'galactic_post_view_article.php';
    $container['news'] = $message;
    transfer('id');
    $PHP_OUTPUT.=('<small><br />note: breaking news is in the news section.<br /></small>');
    $PHP_OUTPUT.=create_link($container, 'Add to Breaking News');
}

?>