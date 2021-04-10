<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$player = $session->getPlayer();

$db = Smr\Database::getInstance();
$db->query('DELETE FROM galactic_post_paper_content WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($var['article_id']));

$container = Page::create('skeleton.php', 'galactic_post_paper_edit.php');
$container->addVar('id');
$container->go();
