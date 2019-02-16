<?php

Menu::galactic_post();

$db2 = new SmrMySqlDatabase();
$db3 = new SmrMySqlDatabase();

if (!empty($var['paper_id'])) {
	if (!isset($var['game_id'])) {
		create_error('Must specify a game ID!');
	}

	// Create link back to past editions
	if (isset($var['back']) && $var['back']) {
		$container = create_container('skeleton.php', 'galactic_post_past.php');
		$container['game_id'] = $var['game_id'];
		$PHP_OUTPUT .= create_link($container, '<b>&lt;&lt;Back</b>');
	}

	$db2->query('SELECT * FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($var['game_id']) . ' AND paper_id = '.$var['paper_id']);
	$db2->nextRecord();
	$paper_name = bbifyMessage($db2->getField('title'));

	$template->assign('PageTopic','Reading <i>Galactic Post</i> Edition : '.$paper_name);
	$db2->query('SELECT * FROM galactic_post_paper_content WHERE paper_id = '.$db2->escapeNumber($var['paper_id']).' AND game_id = '.$db2->escapeNumber($var['game_id']));
	$even = $db2->getNumRows() % 2 == 0;
	$curr_position = 0;
	$PHP_OUTPUT.=('<table align="center" spacepadding="20" cellspacing="20">');
	$amount = $db2->getNumRows();
	if ($even === false) {
		$amount += 1;
	}
	while ($curr_position < $amount) {
		$curr_position += 1;
		if ($even === false && $db2->getNumRows() + 1 == $curr_position) {
			$PHP_OUTPUT.=('<td>&nbsp;</td>');
			continue;
		}
		$db2->nextRecord();
		//now we have the articles in this paper.
		$db3->query('SELECT * FROM galactic_post_article WHERE game_id = '.$db3->escapeNumber($var['game_id']).' AND article_id = '.$db3->escapeNumber($db2->getField('article_id')).' LIMIT 1');
		$db3->nextRecord();

		if ($curr_position % 2 == 1) {
			//it is odd so we need a new row
			$PHP_OUTPUT.=('<tr>');
		}

		$PHP_OUTPUT.=('<td align=center valign=top width=50%>');
		$PHP_OUTPUT.=('<font size="6">'.bbifyMessage($db3->getField('title')).'</font><br /><br /><br />');
		$PHP_OUTPUT.=('<div align="justify">'.bbifyMessage($db3->getField('text')).'</div><br /><br /><br />');
		$PHP_OUTPUT.=('</td>');
		if (floor($curr_position / 2) == $curr_position / 2) {
			//we have an even article so we need to close the row
			$PHP_OUTPUT.=('</tr>');
		}
	}
	$PHP_OUTPUT.=('</table>');
}
else {
	$template->assign('PageTopic','Galactic Post');
	$PHP_OUTPUT.=('There is no current edition of the Galactic Post for this game.');
}
