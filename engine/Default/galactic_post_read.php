<?php

$template->assign('PageTopic','Galactic Post');
require_once(get_file_loc('menu.inc'));
create_galactic_post_menue();

$db2 = new SmrMySqlDatabase();
$db3 = new SmrMySqlDatabase();
$db->query('SELECT * FROM galactic_post_online WHERE game_id = '.$player->getGameID());
if ($db->nextRecord())
{
	$paper_id = $db->getField('paper_id');
	$db2->query('SELECT * FROM galactic_post_paper WHERE game_id = '.$player->getGameID().' AND paper_id = '.$paper_id);
	$db2->nextRecord();
	$paper_name = bbifyMessage($db2->getField('title'));

	$template->assign('PageTopic','Reading <i>Galactic Post</i> Edition : '.$paper_name);
	$db2->query('SELECT * FROM galactic_post_paper_content WHERE paper_id = '.$db2->escapeNumber($paper_id).' AND game_id = '.$db2->escapeNumber($player->getGameID()));
	$even = $db2->getNumRows() % 2 == 0;
	$curr_position = 0;
	$PHP_OUTPUT.=('<table align="center" spacepadding="20" cellspacing="20">');
	$amount = $db2->getNumRows();
	if ($even === false)
	{
		$amount += 1;
	}
	while ($curr_position < $amount)
	{
		$curr_position += 1;
		if ($even === false && $db2->getNumRows() + 1 == $curr_position)
		{
			$PHP_OUTPUT.=('<td>&nbsp;</td>');
			continue;
		}
		$db2->nextRecord();
		//now we have the articles in this paper.
		$db3->query('SELECT * FROM galactic_post_article WHERE game_id = '.$db3->escapeNumber($player->getGameID()).' AND article_id = '.$db3->escapeNumber($db2->getField('article_id')).' LIMIT 1');
		$db3->nextRecord();

		if ($curr_position % 2 == 1)
		{
			//it is odd so we need a new row
			$PHP_OUTPUT.=('<tr>');
		}

		$PHP_OUTPUT.=('<td align=center valign=top width=50%>');
		$PHP_OUTPUT.=('<font size="6">'.bbifyMessage($db3->getField('title')).'</font><br /><br /><br />');
		$PHP_OUTPUT.=('<div align="justify">'.bbifyMessage($db3->getField('text')).'</div><br /><br /><br />');
		$PHP_OUTPUT.=('</td>');
		if (floor($curr_position / 2) == $curr_position / 2)
		{
			//we have an even article so we need to close the row
			$PHP_OUTPUT.=('</tr>');
		}
	}
	$PHP_OUTPUT.=('</table>');
}
else
{
	$PHP_OUTPUT.=('There is no current edition of the Galactic Post for this game.');

}

?>