<?php

$template->assign('PageTopic', 'Show Map');

if (isset($_REQUEST['game_id'])) {

    SmrSession::updateVar('game_id', $_REQUEST['game_id']);
    $game_id = $var['game_id'];

    $container = array();
    $container['url'] = 'map_show_processing.php';
    $container['game_id'] = $game_id;

    $PHP_OUTPUT .= create_echo_form($container);
    $PHP_OUTPUT .= ('<select name="account_id" size="1" id="InputFields">');
    $PHP_OUTPUT .= ('<option value="0">[Please Select]</option>');
    $PHP_OUTPUT .= ('<option value="all">All Players</option>');

    $db->query('SELECT * FROM player WHERE game_id = ' . $game_id . ' ORDER BY player_id');

    while ($db->nextRecord())
        $PHP_OUTPUT .= ('<option value="' . $db->getField('account_id') . '">' . stripslashes($db->getField('player_name')) . ' (' . $db->getField('player_id') . ')</option>');

    $PHP_OUTPUT .= ('</select>&nbsp;&nbsp;');
    $PHP_OUTPUT .= create_submit('Reveal Map');
    $PHP_OUTPUT .= ('</form>');

} else {

    $PHP_OUTPUT .= create_echo_form(create_container('skeleton.php', 'map_show.php'));
    $PHP_OUTPUT .= ('<p>Please select a game:</p>');
    $PHP_OUTPUT .= ('<select name="game_id" size="1" id="InputFields">');
    $PHP_OUTPUT .= ('<option value="0">[Please Select]</option>');

    $db->query('SELECT * FROM game ORDER BY game_id');

    while ($db->nextRecord())
        $PHP_OUTPUT .= ('<option value="' . $db->getField('game_id') . '">' . $db->getField('game_name') . '</option>');

    $PHP_OUTPUT .= ('</select>&nbsp;&nbsp;');
    $PHP_OUTPUT .= create_submit('Select');
    $PHP_OUTPUT .= ('</form>');

}

?>