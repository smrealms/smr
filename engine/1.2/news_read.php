<?php

print_topic("READING THE NEWS");
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "news_read.php";
$container["breaking"] = "yes";

include(get_file_loc('menue.inc'));
print_news_menue();
$var_del = time() - 86400;
$db->query("DELETE FROM news WHERE time < $var_del AND type = 'breaking'");
$db->query("SELECT * FROM news WHERE game_id = $player->game_id AND type = 'breaking' ORDER BY time DESC LIMIT 1");
if ($db->next_record()) {

	$time = $db->f("time");
    print_link($container, "<b>MAJOR NEWS! - " . date("n/j/Y g:i:s A", $time) . "</b>");
	print("<br><br>");

}
if (isset($var["breaking"])) {

	$db->query("SELECT * FROM news WHERE game_id = $player->game_id AND type = 'breaking' ORDER BY time DESC LIMIT 1");
	$text = stripslashes($db->f("news_message"));
	$time = $db->f("time");
	print_table();
	print("<tr>");
	print("<th align=\"center\"><span style=\"color:#80C870;\">Time</span></th>");
	print("<th align=\"center\"><span style=\"color:#80C870;\">Breaking News</span></th>");
	print("</tr>");
	print("<tr>");
	print("<td align=\"center\"> " . date("n/j/Y g:i:s A", $time) . " </td>");
	print("<td align=\"left\">$text</td>");
	print("</tr>");
	print("</table>");
	print("<br><br>");

}
print("<div align=\"center\">View News entries</div><br>");
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "news_read.php";
print_form($container);
print("<div align=\"center\"><input type=\"text\" name=\"min_news\" value=\"1\" size=\"3\" id=\"InputFields\" style=\"text-align:center;\">&nbsp;-&nbsp;<input type=\"text\" name=\"max_news\" value=\"50\" size=\"3\" id=\"InputFields\" style=\"text-align:center;\">&nbsp;<br>");
print_submit("View");
print("</div></form>");
if (isset($_REQUEST['min_news'])) $min_news = $_REQUEST['min_news'];
if (isset($_REQUEST['max_news'])) $max_news = $_REQUEST['max_news'];
if (empty($min_news) || empty($max_news)) {

	$min_news = 1;
	$max_news = 50;

}
elseif ($min_news > $max_news) {

		print_error("The first number must be lower than the second number!");
		return;

}
$db->query("SELECT * FROM news WHERE game_id = $player->game_id AND type != 'breaking' ORDER BY news_id DESC LIMIT " . ($min_news - 1) . ", " . ($max_news - $min_news + 1));
if ($db->nf()) {

	print("<b><big><div align=\"center\"><font color=\"blue\">");
	print("Viewing " . ($max_news - $min_news + 1) . " news entries.</font></div></big></b>");
	print_table();
	print("<tr>");
	print("<th align=\"center\">Time</span>");
	print("<th align=\"center\">News</span>");
	print("</tr>");

	while ($db->next_record()) {

		$time = $db->f("time");
		$news = stripslashes($db->f("news_message"));

		print("<tr>");
		print("<td align=\"center\">" . date("n/j/Y g:i:s A", $time) . "</td>");
		print("<td style=\"text-align:left;vertical-align:middle;\">$news</td>");
		print("</tr>");

	}

	print("</table>");

} else
	print("There is no news");


?>