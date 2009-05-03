<?
$template->assign('PageTopic','CURRENT NEWS');
include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_news_menue();

require_once(get_file_loc('bar.functions.inc'));
checkForLottoWinner($player->getGameID());

if(!isset($var['LastNewsUpdate']))
	SmrSession::updateVar('LastNewsUpdate',$player->getLastNewsUpdate());
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'news_read.php';
$container['breaking'] = 'yes';
$var_del = TIME - 86400;
$db->query('DELETE FROM news WHERE time < '.$var_del.' AND type = \'breaking\'');
$db->query('SELECT * FROM news WHERE game_id = '.$player->getGameID().' AND type = \'breaking\' ORDER BY time DESC LIMIT 1');
if ($db->nextRecord())
{
    $PHP_OUTPUT.=create_link($container, '<b>MAJOR NEWS! - ' . date(DATE_FULL_SHORT, $db->getField('time')) . '</b>');
    $PHP_OUTPUT.=('<br /><br />');

}
if (isset($var['breaking']))
{
    $db->query('SELECT * FROM news WHERE game_id = '.$player->getGameID().' AND type = \'breaking\' ORDER BY time DESC LIMIT 1');
    $text = stripslashes($db->getField('news_message'));
    $PHP_OUTPUT.=create_table();
    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<th align="center"><span style="color:#80C870;">Time</span></th>');
    $PHP_OUTPUT.=('<th align="center"><span style="color:#80C870;">Breaking News</span></th>');
    $PHP_OUTPUT.=('</tr>');
    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<td align="center"> ' . date(DATE_FULL_SHORT, $db->getField('time')) . ' </td>');
    $PHP_OUTPUT.=('<td align="left">'.$text.'</td>');
    $PHP_OUTPUT.=('</tr>');
    $PHP_OUTPUT.=('</table>');
    $PHP_OUTPUT.=('<br /><br />');

}
//display lottonews if we have it
$db->query('SELECT * FROM news WHERE game_id = '.$player->getGameID().' AND type = \'lotto\' ORDER BY time DESC');
while ($db->nextRecord())
{
	$PHP_OUTPUT.=create_table();
    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<th align="center"><span style="color:#80C870;">Time</span></th>');
    $PHP_OUTPUT.=('<th align="center"><span style="color:#80C870;">Message</span></th>');
    $PHP_OUTPUT.=('</tr>');
    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<td align="center"> ' . date(DATE_FULL_SHORT, $db->getField('time')) . ' </td>');
    $PHP_OUTPUT.=('<td align="left">' . $db->getField('news_message') . '</td>');
    $PHP_OUTPUT.=('</tr>');
    $PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('<br /><br />');
}
$db->query('SELECT * FROM news WHERE game_id = '.$player->getGameID().' AND time > '.$var['LastNewsUpdate'].' AND type = \'regular\' ORDER BY news_id DESC');
$player->updateLastNewsUpdate();
$player->update();

if ($db->getNumRows())
{
    $PHP_OUTPUT.=('<b><big><div align="center" style="color:blue;">You have ' . $db->getNumRows() . ' news entries.</div></big></b>');
    $PHP_OUTPUT.=create_table();
    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<th align="center">Time</span>');
    $PHP_OUTPUT.=('<th align="center">News</span>');
    $PHP_OUTPUT.=('</tr>');
    while ($db->nextRecord())
    {
        $time = $db->getField('time');
        $news = stripslashes($db->getField('news_message'));

        $PHP_OUTPUT.=('<tr>');
        $PHP_OUTPUT.=('<td align="center">' . date(DATE_FULL_SHORT, $time) . '</td>');
        $PHP_OUTPUT.=('<td align="left">'.$news.'</td>');
        $PHP_OUTPUT.=('</tr>');
    }
    $PHP_OUTPUT.=('</table>');
}
else
    $PHP_OUTPUT.=('You have no current news.');

?>