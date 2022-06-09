<?php declare(strict_types=1);

$session = Smr\Session::getInstance();
$var = $session->getCurrentVar();
$player = $session->getPlayer();

$db = Smr\Database::getInstance();
$db->write('DELETE FROM galactic_post_paper_content WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($var['article_id']));

$container = Page::create('galactic_post_paper_edit.php');
$container->addVar('id');
$container->go();
