<?

$smarty->assign('PageTopic','KILL RANKINGS');

include($ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_ranking_menue(0, 1);

// what rank are we?
$db->query('SELECT * FROM player WHERE game_id = '.SmrSession::$game_id.' AND ' .
                                      '(kills > '.$player->getKills().' OR ' .
                                      '(kills = '.$player->getKills().' AND player_name <= ' . $db->escapeString($player->getPlayerName(), true) . ' ))');
$our_rank = $db->nf();

// how many players are there?
$db->query('SELECT * FROM player WHERE game_id = '.SmrSession::$game_id);
$total_player = $db->nf();

$PHP_OUTPUT.=('<div align="center">');
$PHP_OUTPUT.=('<p>Here are the rankings of players by their kills</p>');
$PHP_OUTPUT.=('<p>You are ranked '.$our_rank.' out of '.$total_player.'</p>');
$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="5" border="0" class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Player</th>');
$PHP_OUTPUT.=('<th>Race</th>');
$PHP_OUTPUT.=('<th>Alliance</th>');
$PHP_OUTPUT.=('<th>Kills</th>');
$PHP_OUTPUT.=('</tr>');

$db->query('SELECT * FROM player WHERE game_id = '.SmrSession::$game_id.' ORDER BY kills DESC, player_name LIMIT 10');

$rank = 0;
while ($db->next_record()) {

    // get current player
    $curr_player =& SmrPlayer::getPlayer($db->f('account_id'), SmrSession::$game_id);

    // increase rank counter
    $rank++;

    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<td valign="top" align="center"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' style="font-weight:bold;"');
    $PHP_OUTPUT.=('>'.$rank.'</td>');
    $PHP_OUTPUT.=('<td valign="top"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' style="font-weight:bold;"');
    $PHP_OUTPUT.=('>'.$curr_player->getLevelName().' ');

    $container = array();
    $container['url']		= 'skeleton.php';
    $container['body']		= 'trader_search_result.php';
    $container['player_id']	= $curr_player->getPlayerID();
    $PHP_OUTPUT.=create_link($container, $curr_player->getDisplayName());

    $PHP_OUTPUT.=('</td>');
    $PHP_OUTPUT.=('<td valign="top"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' style="font-weight:bold;"');
    $PHP_OUTPUT.=('>'.$curr_player->getRaceName().'</td>');

    $PHP_OUTPUT.=('<td valign="top"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' style="font-weight:bold;"');
    $PHP_OUTPUT.=('>');
    if ($curr_player->getAllianceID() > 0) {

        $container = array();
        $container['url']			= 'skeleton.php';
        $container['body']			= 'alliance_roster.php';
        $container['alliance_id']	= $curr_player->getAllianceID();
        $PHP_OUTPUT.=create_link($container, $curr_player->getAllianceName());
    } else
        $PHP_OUTPUT.=('(none)');
    $PHP_OUTPUT.=('</td>');
    $PHP_OUTPUT.=('<td valign="top" align="right"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' style="font-weight:bold;"');
    $PHP_OUTPUT.=('>' . number_format($curr_player->getKills()) . '</td>');
    $PHP_OUTPUT.=('</tr>');

}

$PHP_OUTPUT.=('</table>');
$action = $_REQUEST['action'];
if ($action == 'Show') {

    $min_rank = $_POST['min_rank'];
    $max_rank = $_POST['max_rank'];

} else {

    $min_rank = $our_rank - 5;
    $max_rank = $our_rank + 5;

}

if ($min_rank <= 0) {

    $min_rank = 1;
    $max_rank = 10;

}

if ($max_rank > $total_player)
    $max_rank = $total_player;

$container = array();
$container['url']		= 'skeleton.php';
$container['body']		= 'rankings_player_kills.php';
$container['min_rank']	= $min_rank;
$container['max_rank']	= $max_rank;

$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<p><input type="text" name="min_rank" value="'.$min_rank.'" size="3" id="InputFields" style="text-align:center;">&nbsp;-&nbsp;<input type="text" name="max_rank" value="'.$max_rank.'" size="3" id="InputFields" style="text-align:center;">&nbsp;');
$PHP_OUTPUT.=create_submit('Show');
$PHP_OUTPUT.=('</p></form>');
$PHP_OUTPUT.=('<table cellspacing="0" cellpadding="5" border="0" class="standard" width="95%">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th>Rank</th>');
$PHP_OUTPUT.=('<th>Player</th>');
$PHP_OUTPUT.=('<th>Race</th>');
$PHP_OUTPUT.=('<th>Alliance</th>');
$PHP_OUTPUT.=('<th>Kills</th>');
$PHP_OUTPUT.=('</tr>');

$db->query('SELECT * FROM player WHERE game_id = '.SmrSession::$game_id.' ORDER BY kills DESC, player_name LIMIT ' . ($min_rank - 1) . ', ' . ($max_rank - $min_rank + 1));

$rank = $min_rank - 1;
while ($db->next_record()) {

    // get current player
    $curr_player =& SmrPlayer::getPlayer($db->f('account_id'), $player->getGameID());

    // increase rank counter
    $rank++;

    $PHP_OUTPUT.=('<tr>');
    $PHP_OUTPUT.=('<td valign="top" align="center"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' style="font-weight:bold;"');
    $PHP_OUTPUT.=('>'.$rank.'</td>');
    $PHP_OUTPUT.=('<td valign="top"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' style="font-weight:bold;"');
    $PHP_OUTPUT.=('>'.$curr_player->getLevelName().' ');

    $container = array();
    $container['url']        = 'skeleton.php';
    $container['body']        = 'trader_search_result.php';
    $container['player_id'] = $curr_player->getPlayerID();
    $PHP_OUTPUT.=create_link($container, $curr_player->getDisplayName());

    $PHP_OUTPUT.=('</td>');
    $PHP_OUTPUT.=('<td valign="top"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' style="font-weight:bold;"');
    $PHP_OUTPUT.=('>'.$curr_player->getRaceName().'</td>');

    $PHP_OUTPUT.=('<td valign="top"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' style="font-weight:bold;"');
    $PHP_OUTPUT.=('>');
    if ($curr_player->getAllianceID() > 0) {

        $container = array();
        $container['url']             = 'skeleton.php';
        $container['body']             = 'alliance_roster.php';
        $container['alliance_id']    = $curr_player->getAllianceID();
        $PHP_OUTPUT.=create_link($container, $curr_player->getAllianceName());
    } else
        $PHP_OUTPUT.=('(none)');
    $PHP_OUTPUT.=('</td>');
    $PHP_OUTPUT.=('<td valign="top" align="right"');
    if ($player->getAccountID() == $curr_player->getAccountID())
        $PHP_OUTPUT.=(' style="font-weight:bold;"');
    $PHP_OUTPUT.=('>' . number_format($curr_player->getKills()) . '</td>');
    $PHP_OUTPUT.=('</tr>');

}

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</div>');

?>