<?php

$template->assign('PageTopic','Writing An Article');
include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_galactic_post_menue();

$template->assign('SubmitArticleHref',SmrSession::get_new_href(create_container('galactic_post_write_article_processing.php')));
if(isset($var['preview']))
{
	$template->assign('PreviewTitle', $var['previewTitle']);
	$template->assign('Preview', $var['preview']);
}
?>