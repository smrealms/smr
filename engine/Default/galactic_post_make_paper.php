<?php

$template->assign('PageTopic', 'Making A Paper');
Menu::galactic_post();

$container = create_container('galactic_post_make_paper_processing.php');
$template->assign('SubmitHREF', SmrSession::getNewHREF($container));
