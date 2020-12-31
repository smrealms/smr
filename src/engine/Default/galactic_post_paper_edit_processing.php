<?php declare(strict_types=1);

$db->query('DELETE FROM galactic_post_paper_content WHERE game_id = ' . $db->escapeNumber($player->getGameID()) . ' AND article_id = ' . $db->escapeNumber($var['article_id']));

$container = create_container('skeleton.php', 'galactic_post_paper_edit.php');
transfer('id');
forward($container);
