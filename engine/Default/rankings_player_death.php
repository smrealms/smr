<?php

$template->assign('PageTopic','Death Rankings');

include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_ranking_menue(0, 2);

// what rank are we?
$db->query('SELECT * FROM player WHERE game_id = '.SmrSession::$game_id.' AND ' .
                                      '(deaths > '.$player->getDeaths().' OR ' .
                                      '(deaths = '.$player->getDeaths().' AND player_name <= ' . $db->escapeString($player->getPlayerName(), true) . ' ))');
$our_rank = $db->getNumRows();

// how many players are there?
$db->query('SELECT * FROM player WHERE game_id = '.$player->getGameID());
$total_player = $db->getNumRows();

$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<p>Here are the rankings of players by their deaths</p>');
$PHP_OUTPUT.=('<p>You are ranked '.$our_rank.' out of '.$total_player.'</p>');
$PHP_OUTPUT.=('<table class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Player</th>');
$PHP_OUTPUT.=('<th>Race</th>');
$PHP_OUTPUT.=('<th>Alliance</th>');
$PHP_OUTPUT.=('<th>Deaths</th>');
$PHP_OUTPUT.=('</tr>');

$db->query('SELECT * FROM player WHERE game_id = '.$player->getGameID().' ORDER BY deaths DESC, player_name LIMIT 10');

$rank = 0;
while ($db->nextRecord())
{
    // get current player
    $curr_player =& SmrPlayer::getPlayer($db->getField('account_id'), SmrSession::$game_id);

    // increase rank counter
    $rank++;

    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<td valign="top" align="center"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' class="bold"');
    $PHP_OUTPUT.=('>'.$rank.'</td>');
    $PHP_OUTPUT.=('<td valign="top"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' class="bold"');
    $PHP_OUTPUT.=('>'.$curr_player->getLevelName().' ');

    $container = array();
    $container['url']        = 'skeleton.php';
    $container['body']        = 'trader_search_result.php';
    $container['player_id'] = $curr_player->getPlayerID();
    $PHP_OUTPUT.=create_link($container, $curr_player->getDisplayName());

    $PHP_OUTPUT.=('</td>');
    $PHP_OUTPUT.=('<td valign="top"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' class="bold"');
    $PHP_OUTPUT.=('>'.$curr_player->getRaceName().'</td>');

    $PHP_OUTPUT.=('<td valign="top"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' class="bold"');
    $PHP_OUTPUT.=('>');
    if ($curr_player->hasAlliance())
    {
        $PHP_OUTPUT.=create_link($curr_player->getAllianceRosterHREF(), $curr_player->getAllianceName());
    }
    else
        $PHP_OUTPUT.=('(none)');
    $PHP_OUTPUT.=('</td>');
    $PHP_OUTPUT.=('<td valign="top" align="right"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' class="bold"');
    $PHP_OUTPUT.=('>' . number_format($curr_player->getDeaths()) . '</td>');
    $PHP_OUTPUT.=('</tr>');
}

$PHP_OUTPUT.=('</table>');
$action = $_REQUEST['action'];
if ($action == 'Show' && is_numeric($_REQUEST['min_rank'])&&is_numeric($_REQUEST['max_rank']))
{
    $min_rank = min($_REQUEST['min_rank'], $_REQUEST['max_rank']);
    $max_rank = max($_REQUEST['min_rank'], $_REQUEST['max_rank']);
	SmrSession::updateVar('MinRank',$min_rank);
	SmrSession::updateVar('MaxRank',$max_rank);
}
elseif(isset($var['MinRank'])&&isset($var['MaxRank']))
{
    $min_rank = $var['MinRank'];
    $max_rank = $var['MaxRank'];
}
else
{
    $min_rank = $our_rank - 5;
    $max_rank = $our_rank + 5;
}

if ($min_rank <= 0)
{
    $min_rank = 1;
    $max_rank = 10;
}

if ($max_rank > $total_player)
    $max_rank = $total_player;

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'rankings_player_death.php';
$container['min_rank']	= $min_rank;
$container['max_rank']	= $max_rank;

$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<p><input type="text" name="min_rank" value="'.$min_rank.'" size="3" id="InputFields" class="center">&nbsp;-&nbsp;<input type="text" name="max_rank" value="'.$max_rank.'" size="3" id="InputFields" class="center">&nbsp;');
$PHP_OUTPUT.=create_submit('Show');
$PHP_OUTPUT.=('</p></form>');
$PHP_OUTPUT.=('<table class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Player</th>');
$PHP_OUTPUT.=('<th>Race</th>');
$PHP_OUTPUT.=('<th>Alliance</th>');
$PHP_OUTPUT.=('<th>Deaths</th>');
$PHP_OUTPUT.=('</tr>');

$db->query('SELECT * FROM player WHERE game_id = '.SmrSession::$game_id.' ORDER BY deaths DESC, player_name LIMIT ' . ($min_rank - 1) . ', ' . ($max_rank - $min_rank + 1));

$rank = $min_rank - 1;
while ($db->nextRecord())
{
    // get current player
    $curr_player =& SmrPlayer::getPlayer($db->getField('account_id'), $player->getGameID());

    // increase rank counter
    $rank++;

    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<td valign="top" align="center"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' class="bold"');
    $PHP_OUTPUT.=('>'.$rank.'</td>');
    $PHP_OUTPUT.=('<td valign="top"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' class="bold"');
    $PHP_OUTPUT.=('>'.$curr_player->getLevelName().' ');

    $container = array();
    $container['url']        = 'skeleton.php';
    $container['body']        = 'trader_search_result.php';
    $container['player_id'] = $curr_player->getPlayerID();
    $PHP_OUTPUT.=create_link($container, $curr_player->getDisplayName());

    $PHP_OUTPUT.=('</td>');
    $PHP_OUTPUT.=('<td valign="top"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' class="bold"');
    $PHP_OUTPUT.=('>'.$curr_player->getRaceName().'</td>');

    $PHP_OUTPUT.=('<td valign="top"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' class="bold"');
    $PHP_OUTPUT.=('>');
    if ($curr_player->hasAlliance())
    {
        $PHP_OUTPUT.=create_link($curr_player->getAllianceRosterHREF(), $curr_player->getAllianceName());
    }
    else
        $PHP_OUTPUT.=('(none)');
    $PHP_OUTPUT.=('</td>');
    $PHP_OUTPUT.=('<td valign="top" align="right"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' class="bold"');
    $PHP_OUTPUT.=('>' . number_format($curr_player->getDeaths()) . '</td>');
    $PHP_OUTPUT.=('</tr>');
}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');

?>