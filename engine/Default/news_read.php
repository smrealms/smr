<?

$smarty->assign('PageTopic','READING THE NEWS');
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'news_read.php';
$container['breaking'] = 'yes';

include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_news_menue();
$var_del = time() - 86400;
$db->query('DELETE FROM news WHERE time < '.$var_del.' AND type = \'breaking\'');
$db->query('SELECT * FROM news WHERE game_id = '.$player->getGameID().' AND type = \'breaking\' ORDER BY time DESC LIMIT 1');
if ($db->nextRecord()) {

	$time = $db->getField('time');
    $PHP_OUTPUT.=create_link($container, '<b>MAJOR NEWS! - ' . date('n/j/Y g:i:s A', $time) . '</b>');
	$PHP_OUTPUT.=('<br /><br />');

}
if (isset($var['breaking'])) {

	$db->query('SELECT * FROM news WHERE game_id = '.$player->getGameID().' AND type = \'breaking\' ORDER BY time DESC LIMIT 1');
	$text = stripslashes($db->getField('news_message'));
	$time = $db->getField('time');
	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center"><span style="color:#80C870;">Time</span></th>');
	$PHP_OUTPUT.=('<th align="center"><span style="color:#80C870;">Breaking News</span></th>');
	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align="center"> ' . date('n/j/Y g:i:s A', $time) . ' </td>');
	$PHP_OUTPUT.=('<td align="left">$text</td>');
	$PHP_OUTPUT.=('</tr>');
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('<br /><br />');

}
$PHP_OUTPUT.=('<div align="center">View News entries</div><br />');
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'news_read.php';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<div align="center"><input type="text" name="min_news" value="1" size="3" id="InputFields" style="text-align:center;">&nbsp;-&nbsp;<input type="text" name="max_news" value="50" size="3" id="InputFields" style="text-align:center;">&nbsp;<br />');
$PHP_OUTPUT.=create_submit('View');
$PHP_OUTPUT.=('</div></form>');
if (isset($_REQUEST['min_news'])) $min_news = $_REQUEST['min_news'];
if (isset($_REQUEST['max_news'])) $max_news = $_REQUEST['max_news'];
if (empty($min_news) || empty($max_news)) {

	$min_news = 1;
	$max_news = 50;

}
elseif ($min_news > $max_news) {

		create_error('The first number must be lower than the second number!');
		return;

}
$db->query('SELECT * FROM news WHERE game_id = '.$player->getGameID().' AND type != \'breaking\' ORDER BY news_id DESC LIMIT ' . ($min_news - 1) . ', ' . ($max_news - $min_news + 1));
if ($db->getNumRows()) {

	$PHP_OUTPUT.=('<b><big><div align="center"><font color="blue">');
	$PHP_OUTPUT.=('Viewing ' . ($max_news - $min_news + 1) . ' news entries.</font></div></big></b>');
	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th align="center">Time</span>');
	$PHP_OUTPUT.=('<th align="center">News</span>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->nextRecord()) {

		$time = $db->getField('time');
		$news = stripslashes($db->getField('news_message'));

		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center">' . date('n/j/Y g:i:s A', $time) . '</td>');
		$PHP_OUTPUT.=('<td style="text-align:left;vertical-align:middle;">'.$news.'</td>');
		$PHP_OUTPUT.=('</tr>');

	}

	$PHP_OUTPUT.=('</table>');

} else
	$PHP_OUTPUT.=('There is no news');


?>