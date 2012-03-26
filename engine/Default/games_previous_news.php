<?php
require_once(get_file_loc($var['HistoryDatabase'].'.class.inc'));
//games_previous_news.php
if (isset($_REQUEST['min'])) $min = $_REQUEST['min'];
else $min = 1;
if (isset($_REQUEST['max'])) $max = $_REQUEST['max'];
else $max = 50;
$game_id = $var['game_id'];
$container = create_container('skeleton.php', 'games_previous_news.php');
$container['HistoryDatabase'] = $var['HistoryDatabase'];
$container['game_id'] = $game_id;
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<div align=center>Show News<br />Min:<input id="Inputfields" type="text" value="'.$min.'" name="min" size="5"> - Max:<input id="Inputfields" type="text" value="'.$max.'" name="max" size="5"><br />');
$PHP_OUTPUT.=create_submit('Show');
$PHP_OUTPUT.=('</form>');

$db2 = new $var['HistoryDatabase']();
$db2->query('SELECT * FROM news WHERE game_id = '.$db->escapeNumber($game_id).' AND news_id >= '.$db->escapeNumber($min).' AND news_id <= '.$db->escapeNumber($max));
$PHP_OUTPUT.=create_table();
$PHP_OUTPUT.=('<tr><th class="center">Time</th><th class="center">News</th></tr>');
while ($db2->nextRecord()) {
	$time = $db2->getField('time');
	$news = $db2->getField('message');
	$PHP_OUTPUT.=('<tr><td>' . date(DATE_FULL_SHORT, $time) . '</td><td>'.$news.'</td></tr>');
}
$PHP_OUTPUT.=('</table></div>');

$db = new SmrMySqlDatabase();

?>