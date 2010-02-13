<?php

$template->assign('PageTopic','Galactic Post');

$db2 = new SmrMySqlDatabase();
$db3 = new SmrMySqlDatabase();
$db->query('SELECT * FROM galactic_post_online WHERE game_id = '.$player->getGameID());
if ($db->getNumRows()) {
    $db->nextRecord();
    $paper_id = $db->getField('paper_id');
    $db2->query('SELECT * FROM galactic_post_paper WHERE game_id = '.$player->getGameID().' AND paper_id = '.$paper_id);
    $db2->nextRecord();
    $paper_name = stripslashes($db2->getField('title'));

    $template->assign('PageTopic','Reading <i>Galactic Post</i> Edition : '.$paper_name);
	include(get_file_loc('menue.inc'));
    $PHP_OUTPUT.=create_galactic_post_menue();
    $db2->query('SELECT * FROM galactic_post_paper_content WHERE paper_id = '.$paper_id.' AND game_id = '.$player->getGameID());
    if (floor($db2->getNumRows() / 2) == $db2->getNumRows() / 2)
        $even = 'yes';
    else
        $even = 'no';
    $curr_position = 0;
    $PHP_OUTPUT.=('<table align="center" spacepadding="20" cellspacing="20">');
    if ($even == 'yes')
        $amount = $db2->getNumRows();
    else
        $amount = $db2->getNumRows() + 1;
    while ($curr_position + 1 <= $amount) {

    	$curr_position += 1;
		if ($db2->getNumRows() + 1 == $curr_position && $even != 'yes') {

            $PHP_OUTPUT.=('<td>&nbsp;</td>');
            continue;

        }
        $db2->nextRecord();
        //now we have the articles in this paper.
        $article_num = $db2->getField('article_id');
        $db3->query('SELECT * FROM galactic_post_article WHERE game_id = '.$player->getGameID().' AND article_id = '.$article_num);
        $db3->nextRecord();
        $article_title = stripslashes($db3->getField('title'));
        $article_text = stripslashes($db3->getField('text'));

        if (floor($curr_position / 2) != $curr_position / 2) {

            //it is odd so we need a new row
            $PHP_OUTPUT.=('<tr>');

        }

        $PHP_OUTPUT.=('<td align=center valign=top width=50%>');
        $PHP_OUTPUT.=('<font size="6">'.$article_title.'</font><br /><br /><br />');
        $PHP_OUTPUT.=('<div align="justify">'.$article_text.'</div><br /><br /><br />');
        $PHP_OUTPUT.=('</td>');
        if (floor($curr_position / 2) == $curr_position / 2) {

            //we have an even article so we need to close the row
            $PHP_OUTPUT.=('</tr>');

        }

    }
    $PHP_OUTPUT.=('</table>');
} else {

	include(get_file_loc('menue.inc'));
    $PHP_OUTPUT.=create_galactic_post_menue();
    $PHP_OUTPUT.=('There is no current edition of the Galactic Post for this game.');

}

?>