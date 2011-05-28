<?php

include(get_file_loc('menue.inc'));
create_galactic_post_menue();
$container = create_container('galactic_post_write_article_processing.php');

if(isset($var['id']))
{
	$container['id'] = $var['id'];
	$template->assign('PageTopic','Editing An Article');
	$db->query('SELECT title, text FROM galactic_post_article WHERE game_id = '.$player->getGameID().' AND article_id = '.$var['id'].' LIMIT 1');
	if($db->nextRecord())
	{
		$template->assign('PreviewTitle', $db->getField('title'));
		$template->assign('Preview', $db->getField('text'));
	}
}
else
{
	$template->assign('PageTopic','Writing An Article');
	if(isset($var['preview']))
	{
		$template->assign('PreviewTitle', $var['previewTitle']);
		$template->assign('Preview', $var['preview']);
	}
}
$template->assign('SubmitArticleHref',SmrSession::get_new_href($container));
?>