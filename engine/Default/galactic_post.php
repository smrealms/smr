<?php

$template->assign('PageTopic','Galactic Post');
if ($player->isGPEditor()) {
	$db2 = new SmrMySqlDatabase();
	require_once(get_file_loc('menu.inc'));
	create_galactic_post_menu();
	$PHP_OUTPUT.=('<b>EDITOR OPTIONS<br /></b>');
	$PHP_OUTPUT.=('Welcome '.$player->getPlayerName().' your position is <i>Editor</i><br />');
	$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'galactic_post_view_applications.php'), 'View the applications');
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'galactic_post_view_article.php'), 'View the articles');
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'galactic_post_make_paper.php'), 'Make a paper');
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=create_link(create_container('skeleton.php', 'galactic_post_view_members.php'), 'View Members');
	$PHP_OUTPUT.=('<br />');
	$db->query('SELECT * FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($player->getGameID()));
	if ($db->getNumRows()) {
		$PHP_OUTPUT.=('The following papers are already made (note papers must have 3-8 articles to go to the press)');
	}
	while($db->nextRecord()) {
		$paper_name = $db->getField('title');
		$paper_id = $db->getField('paper_id');
		$PHP_OUTPUT.=('<span class="red">***</span><i>'.$paper_name.'</i>');
		$db2->query('SELECT * FROM galactic_post_paper_content WHERE paper_id = ' . $db2->escapeNumber($paper_id) . ' AND game_id = ' . $db->escapeNumber($player->getGameID()));
		$PHP_OUTPUT.=(' which contains <span class="red"> ' . $db2->getNumRows() . ' </span>articles. ');
		if ($db2->getNumRows() > 2 && $db2->getNumRows() < 9) {
			$container = array();
			$container['url'] = 'galactic_post_make_current.php';
			$container['id'] = $paper_id;
			$PHP_OUTPUT.=create_link($container, '<b>HIT THE PRESS!</b>');

		}
		$PHP_OUTPUT.=('<br />');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'galactic_post_delete_confirm.php';
		$container['paper'] = 'yes';
		$container['id'] = $paper_id;
		$PHP_OUTPUT.=create_link($container, 'Delete '.$paper_name);
		$PHP_OUTPUT.=('<br />');
		$container = array();
		$container['url'] = 'skeleton.php';
		$container['body'] = 'galactic_post_paper_edit.php';
		$container['id'] = $paper_id;
		$PHP_OUTPUT.=create_link($container, 'Edit '.$paper_name);
		$PHP_OUTPUT.=('<br /><br />');
	}
	$PHP_OUTPUT.=('<br />');
	$PHP_OUTPUT.=('<span class="blue">If you wish to edit an article you must first view it.</span>');
	$PHP_OUTPUT.=('<br /><br />');
}

?>