<?

$smarty->assign('PageTopic','VIEWING APPLICATIONS');
include($ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_galactic_post_menue();
$db->query('SELECT * FROM galactic_post_applications WHERE game_id = '.$player->getGameID());
if ($db->nf()) {

    $PHP_OUTPUT.=('You have recieved an application from the following players (click name to view description)<br>');
    $PHP_OUTPUT.=('Becareful when choosing your writters.  Make sure it is someone who will actually help you.<br><br>');

} else
    $PHP_OUTPUT.=('You have no applications to view at the current time.');
while ($db->next_record()) {

    $appliee =& SmrPlayer::getPlayer($db->f('account_id'), $player->getGameID());

    $container = array();
    $container['url'] = 'skeleton.php';
    $container['body'] = 'galactic_post_view_applications.php';
    $container['id'] = $appliee->account_id;
    $PHP_OUTPUT.=create_link($container, '<font color=yellow>'.$appliee->player_name.'</font>');
    $PHP_OUTPUT.=(' who has ');
    if ($db->f('written_before') == 'YES')
        $PHP_OUTPUT.=('written for some kind of a newspaper before.');
    else
        $PHP_OUTPUT.=('not written for a newspaper before.');
    $PHP_OUTPUT.=('<br>');

}
$PHP_OUTPUT.=('<br><br>');
if (isset($var['id'])) {

    $db->query('SELECT * FROM galactic_post_applications WHERE game_id = '.$player->getGameID().' AND account_id = '.$var['id']);
    $db->next_record();
    $desc = stripslashes($db->f('description'));
    $applie =& SmrPlayer::getPlayer($var['id'], $player->getGameID());
    $PHP_OUTPUT.=('Name : '.$applie->player_name.'<br>');
    $PHP_OUTPUT.=('Have you written for some kind of newspaper before? ' . $db->f('written_before'));
    $PHP_OUTPUT.=('<br>');
    $PHP_OUTPUT.=('How many articles are you willing to write per day? ' . $db->f('articles_per_day'));
    $PHP_OUTPUT.=('<br>');
    $PHP_OUTPUT.=('What do you want to tell the editor?<br><br>'.$desc);
    $container = array();
    $container['url'] = 'galactic_post_application_answer.php';
    transfer('id');
    $PHP_OUTPUT.=create_echo_form($container);
    $PHP_OUTPUT.=('<br><br>');
    $PHP_OUTPUT.=create_submit('Accept');
    $PHP_OUTPUT.=create_submit('Reject');
    $PHP_OUTPUT.=('</form>');

}
?>