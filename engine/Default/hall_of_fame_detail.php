<?php

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'hall_of_fame.php';

$PHP_OUTPUT.=create_link($container, '<h1>HALL OF FAME</h1>');

//LIMIT in SQL queries
$amount = 25;

//used for SQL queries when using the HoF for ??? per game
$games = 'games_joined';

//used in SQL queries as the main object
$id = $var['id'];

if ($action == 'Experience From Killing')
    $id = 'kill_exp';

if ($action == 'Total Experience of Players Killed' || $action == 'Average Experience of Players Killed')
    $id = 'traders_killed_exp';

//used for displaying what we are looking at
$first_display = $var['display_first'];
if ($action == 'Per Game')
    $first_display .= ' per game';
elseif ($action == 'Per Death')
    $first_display .= ' per death';
elseif ($action == 'Per Good Traded')
    $first_display .= ' per good traded';
elseif ($action == 'Per Kill')
    $first_display .= ' per kill';
elseif ($action == 'Per Planet Bust')
    $first_display .= ' per planet bust';
elseif ($action == 'Per Port Raid')
    $first_display .= ' per port raid';
elseif ($action == 'Experience Gained')
    $first_display .= ' experience gained';
elseif ($action == 'Damage Done')
    $first_display .= ' damage done';
elseif ($action == 'Experience From Killing')
    $first_display .= ' experience gained from killing';
elseif ($action == 'Total Experience of Players Killed')
    $first_display .= ' total experience gained of players killed';
elseif ($action == 'Average Experience of Players Killed')
    $first_display .= ' average experience gained of players killed';

$second_display = $var['display_second'];
if ($action == 'Experience Gained')
    $second_display .= 'Experience Gained';
elseif ($action == 'Damage Done')
    $second_display .= 'Damage Done';
elseif ($action == 'Experience From Killing')
    $second_display .= 'Experience Gained';
elseif ($action == 'Total Experience of Players Killed')
    $second_display .= 'Total Experience';
elseif ($action == 'Average Experience of Players Killed')
    $second_display .= 'Average Experience';

//are the rankings per game? per death? or regular?
if ($action != 'Overall')
    $special = 'yes';

if ($special == 'yes') {

    //find out what it is per
    if ($action == 'Per Game')
        $per = 'games_joined';
    elseif ($action == 'Per Death')
        $per = 'deaths';
    elseif ($action == 'Per Good Traded')
        $per = 'goods_traded';
    elseif ($action == 'Per Kill')
        $per = 'kills';
    elseif ($action == 'Per Planet Bust')
        $per = 'planet_busts';
    elseif ($action == 'Per Port Raid')
        $per .= 'port_raids';
    elseif ($action == 'Average Experience of Players Killed')
        $per .= 'kills';

}

//the table that we are going to be looking in
if (isset($var['table']))
    $table = $var['table'];
elseif ($id == 'donation')
    $table = 'account_donated';
else
    $table = 'account_has_stats_cache';

if (!isset($per) && isset($id)) {

    //we are doing some stat that is not per game or per death
    if ($id != 'donation')
        $db->query('SELECT * FROM '.$table.' WHERE '.$id.' > 0 AND account_id = '.$account->account_id);
    else
        $db->query('SELECT sum(amount), account_id FROM '.$table.' WHERE amount > 0 AND account_id = '.$player->getAccountID().' GROUP BY account_id');
    if ($db->next_record() && $id != 'donation') {
        if ($action != 'Experience Gained')
            $our_stat = $db->f($id);
        else
            $our_stat = $db->f($id) / 4;

    } elseif ($id == 'donation')
        $our_stat = $db->f('sum(amount)');
    else
        $our_stat = 0;

    if ($id != 'donation') {

        if ($action != 'Experience Gained')
            $db->query('SELECT * FROM '.$table.' WHERE '.$id.' > '.$our_stat);
        else
            $db->query('SELECT * FROM '.$table.' WHERE '.$id.' / 4 > '.$our_stat);
        $rank = $db->nf() + 1;

    } else {

        $db->query('SELECT sum(amount), account_id FROM account_donated WHERE amount > 0 GROUP BY account_id ORDER BY \'sum(amount)\' DESC');
        $rank = 1;
        $continue = 1;
        while ($db->next_record() && $continue) {

            if ($player->getAccountID() == $db->f('account_id')) {

                $continue = 0;
                continue;

            }
            $rank += 1;
        }
    }
    if ($id != 'donation')
        $db->query('SELECT * FROM '.$table.' WHERE '.$id.' > 0 ORDER BY '.$id.' DESC LIMIT '.$amount);
    else
        $db->query('SELECT sum(amount), account_id FROM account_donated WHERE amount > 0 GROUP BY account_id ORDER BY \'sum(amount)\' DESC');

    $PHP_OUTPUT.=($first_display.'<br /><br />View your rank at the bottom of the screen<br /><br />');
    if ($db->nf()) {

        //we have people who we can display
        $PHP_OUTPUT.=create_table();
        $PHP_OUTPUT.=('<tr>');
        $PHP_OUTPUT.=('<th align="center">Rank</th>');
        $PHP_OUTPUT.=('<th align="center">Player</th>');
        $PHP_OUTPUT.=('<th align="center">'.$second_display.'</th>');
        $PHP_OUTPUT.=('</tr>');
        $count = 1;
        while ($db->next_record()) {

            //get the account
            $curr_account =& SmrAccount::getAccount($db->f('account_id'));
            $name = stripslashes($curr_account->HoF_name);
            $db2 = new SMR_DB();
            if ($id == 'donation') {

                $db2->query('SELECT sum(amount) FROM account_donated WHERE account_id = '.$curr_account->account_id);
                $db2->next_record();
                $stat = $db2->f('sum(amount)');

            } else
                $stat = $db->f($id);
            $PHP_OUTPUT.=('<tr>');
            $PHP_OUTPUT.=('<td align="center">'.$count.'</td>');
            $PHP_OUTPUT.=('<td align=center>'.$name.'</td>');
            if ($action != 'Experience Gained')
                $PHP_OUTPUT.=('<td align=center> ' . number_format($stat) . ' </td>');
            else
                $PHP_OUTPUT.=('<td align=center> ' . number_format($stat / 4, 2) . ' </td>');
            $count ++;
            $PHP_OUTPUT.=('</tr>');

        }
        $PHP_OUTPUT.=('</table>');
        $PHP_OUTPUT.=('<br /><br />');
        $PHP_OUTPUT.=create_table();
        $PHP_OUTPUT.=('<tr>');
        $PHP_OUTPUT.=('<th align="center">Rank</th>');
        $PHP_OUTPUT.=('<th align="center">Player</th>');
        $PHP_OUTPUT.=('<th align="center">'.$second_display.'</th>');
        $PHP_OUTPUT.=('</tr>');
        $PHP_OUTPUT.=('<tr>');
        $PHP_OUTPUT.=('<td align="center">'.$rank.'</td>');
        $PHP_OUTPUT.=('<td align="center">'.$account->HoF_name.'</td>');
        if (empty($our_stat))
            $our_stat = 0;
        $PHP_OUTPUT.=('<td align="center"> ' . number_format($our_stat) . ' </td>');
        $PHP_OUTPUT.=('</tr>');
        $PHP_OUTPUT.=('</table>');
    } else
        $PHP_OUTPUT.=('There are no players who currently meet the requirements for this category');

} elseif (isset($id)) {

    //we have a stat that contains a per
    $db->query('SELECT * FROM '.$table.' WHERE '.$id.' > 0 AND '.$per.' > 0 AND account_id = '.$account->account_id);
    if ($db->next_record())
        $our_stat = $db->f($id) / $db->f($per);
    else
        $our_stat = 0;
    $db->query('SELECT * FROM '.$table.' WHERE '.$id.' > 0 AND '.$per.' > 0');
    $rank = 1;
    while ($db->next_record()) {

        if ($db->f($id) / $db->f($per) > $our_stat)
            $rank += 1;

    }
    $db->query('SELECT * FROM '.$table.' WHERE '.$id.' > 0 AND '.$per.' > 0 ORDER BY '.$id.' / '.$per.' DESC LIMIT '.$amount);
    $PHP_OUTPUT.=($first_display.'<br /><br />View your rank at the bottom of the screen<br /><br />');
    if ($db->nf()) {

        //we have people who meet this category
        $PHP_OUTPUT.=create_table();
        $PHP_OUTPUT.=('<tr>');
        $PHP_OUTPUT.=('<th align="center">Rank</th>');
        $PHP_OUTPUT.=('<th align="center">Player</th>');
        $PHP_OUTPUT.=('<th align="center">'.$second_display.'</th>');
        $PHP_OUTPUT.=('</tr>');
        $count = 1;
        $db4 = new SMR_DB();
        while ($db->next_record()) {

            //get the account
            $acc_id= $db->f('account_id');
            $db4->query('SELECT * FROM account WHERE account_id = '.$acc_id);
            if (!$db4->next_record())
            	continue;
            $curr_account =& SmrAccount::getAccount($db->f('account_id'));
            $name = stripslashes($curr_account->HoF_name);
            $PHP_OUTPUT.=('<tr>');
            $PHP_OUTPUT.=('<td align="center">'.$count.'</td>');
            $count ++;
            $PHP_OUTPUT.=('<td align=center>'.$name.'</td>');
            $PHP_OUTPUT.=('<td align=center> ' . number_format($db->f($id) / $db->f($per), 2) . ' </td>');
            $PHP_OUTPUT.=('</tr>');

        }
        $PHP_OUTPUT.=('</table>');
        $PHP_OUTPUT.=('<br /><br />');
        $PHP_OUTPUT.=create_table();
        $PHP_OUTPUT.=('<tr>');
        $PHP_OUTPUT.=('<th align="center">Rank</th>');
        $PHP_OUTPUT.=('<th align="center">Player</th>');
        $PHP_OUTPUT.=('<th align="center">'.$second_display.'</th>');
        $PHP_OUTPUT.=('</tr>');
        $PHP_OUTPUT.=('<tr>');
        $PHP_OUTPUT.=('<td align="center">'.$rank.'</td>');
        $PHP_OUTPUT.=('<td align="center">'.$account->HoF_name.'</td>');
        if (empty($our_stat))
            $our_stat = 0;
        $PHP_OUTPUT.=('<td align="center"> ' . number_format($our_stat, 2) . ' </td>');
        $PHP_OUTPUT.=('</tr>');
        $PHP_OUTPUT.=('</table>');
    } else
        $PHP_OUTPUT.=('There are no players that meet the requirements for this category');

}

$PHP_OUTPUT.=('<p>&nbsp;</p>');

$PHP_OUTPUT.=create_link($container, 'Back to Hall Of Fame');

?>