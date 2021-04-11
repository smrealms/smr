<?php declare(strict_types=1);

$template = Smr\Template::getInstance();

$template->assign('PageTopic', 'Making A Paper');
Menu::galactic_post();

$container = Page::create('galactic_post_make_paper_processing.php');
$template->assign('SubmitHREF', $container->href());
