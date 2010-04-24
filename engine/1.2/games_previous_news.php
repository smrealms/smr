<?php
require_once(get_file_loc("smr_history_db.inc"));
//games_previous_news.php
if (isset($_REQUEST['min'])) $min = $_REQUEST['min'];
else $min = 1;
if (isset($_REQUEST['max'])) $max = $_REQUEST['max'];
else $max = 50;
$game_id = $var["game_id"];
$container = array();
$container["url"] = "skeleton.php";
$container["body"] = "games_previous_news.php";
$container["game_id"] = $game_id;
print_form($container);
print("<div align=center>Show News<br>Min:<input id=Inputfields type=text value=$min name=min size=5> - Max:<input id=Inputfields type=text value=$max name=max size=5><br>");
print_submit("Show");
print("</form>");

$db2 = new SMR_HISTORY_DB();
$db2->query("SELECT * FROM news WHERE game_id = $game_id AND news_id >= $min AND news_id <= $max");
print_table();
print("<tr><th align=center>Time</th><th align=center>News</th></tr>");
while ($db2->next_record()) {

	$time = $db2->f("time");
	$news = $db2->f("message");
	print("<tr><td>" . date("n/j/Y g:i:s A", $time) . "</td><td>$news</td></tr>");

}
print("</table></div>");

$db = new SmrMySqlDatabase();

?>