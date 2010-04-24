<?php

print_topic("GALACTIC POST");

$db2 = new SmrMySqlDatabase();
$db3 = new SmrMySqlDatabase();
$db->query("SELECT * FROM galactic_post_online WHERE game_id = $player->game_id");
if ($db->nf()) {
    $db->next_record();
    $paper_id = $db->f("paper_id");
    $db2->query("SELECT * FROM galactic_post_paper WHERE game_id = $player->game_id AND paper_id = $paper_id");
    $db2->next_record();
    $paper_name = stripslashes($db2->f("title"));

    print_topic("READING <i>GALACTIC POST</i> EDITION : $paper_name");
	include(get_file_loc('menue.inc'));
    print_galactic_post_menue();
    $db2->query("SELECT * FROM galactic_post_paper_content WHERE paper_id = $paper_id AND game_id = $player->game_id");
    if (floor($db2->nf() / 2) == $db2->nf() / 2)
        $even = "yes";
    else
        $even = "no";
    $curr_position = 0;
    print("<table align=\"center\" spacepadding=\"20\" cellspacing=\"20\">");
    if ($even == "yes")
        $amount = $db2->nf();
    else
        $amount = $db2->nf() + 1;
    while ($curr_position + 1 <= $amount) {

    	$curr_position += 1;
		if ($db2->nf() + 1 == $curr_position && $even != "yes") {

            print("<td>&nbsp;</td>");
            continue;

        }
        $db2->next_record();
        //now we have the articles in this paper.
        $article_num = $db2->f("article_id");
        $db3->query("SELECT * FROM galactic_post_article WHERE game_id = $player->game_id AND article_id = $article_num");
        $db3->next_record();
        $article_title = stripslashes($db3->f("title"));
        $article_text = stripslashes($db3->f("text"));

        if (floor($curr_position / 2) != $curr_position / 2) {

            //it is odd so we need a new row
            print("<tr>");

        }

        print("<td align=center valign=top width=50%>");
        print("<font size=6>$article_title</font><br><br><br>");
        print("<div align=\"justify\">$article_text</div><br><br><br>");
        print("</td>");
        if (floor($curr_position / 2) == $curr_position / 2) {

            //we have an even article so we need to close the row
            print("</tr>");

        }

    }
    print("</table>");
} else {

	include(get_file_loc('menue.inc'));
    print_galactic_post_menue();
    print("There is no current edition of the Galactic Post for this game.");

}

?>