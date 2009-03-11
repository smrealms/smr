<?
if (!$player->isLandedOnPlanet())
	create_error('You are not on a planet!');
require_once(get_file_loc('SmrPlanet.class.inc'));

// create planet object
$planet =& SmrPlanet::getPlanet($player->getGameID(),$player->getSectorID());
$template->assign('PageTopic','PLANET : '.$planet->planet_name.' [SECTOR #'.$player->getSectorID().']');

include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_planet_menue();

//echo the dump cargo message or other message.
if (isset($var['errorMsg']))
   $PHP_OUTPUT.=($var['errorMsg'] . '<br />');
if (isset($var['msg']))
   $PHP_OUTPUT.=($var['msg'] . '<br />');


$PHP_OUTPUT.=('<table class="standard">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th width="125">&nbsp;</th>');
$PHP_OUTPUT.=('<th width="75">Current</th>');
$PHP_OUTPUT.=('<th width="75">Max</th>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Generator</td><td align="center">');
$PHP_OUTPUT.=($planet->getBuilding(1));
$PHP_OUTPUT.=('</td><td align="center">');
$PHP_OUTPUT.=($planet->max_construction[1]);
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Hangar</td><td align="center">');
$PHP_OUTPUT.=($planet->getBuilding(2));
$PHP_OUTPUT.=('</td><td align="center">');
$PHP_OUTPUT.=($planet->max_construction[2]);
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Turret</td><td align="center">');
$PHP_OUTPUT.=($planet->getBuilding(3));
$PHP_OUTPUT.=('</td><td align="center">');
$PHP_OUTPUT.=($planet->max_construction[3]);
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('</table>');

$PHP_OUTPUT.=('<br />');

$PHP_OUTPUT.=('<table class="standard">');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th width="125">&nbsp;</th>');
$PHP_OUTPUT.=('<th width="75">Amount</th>');
$PHP_OUTPUT.=('<th width="75">Accuracy</th>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Shields</td><td align="center">'.$planet->shields.'</td><td>&nbsp;</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Combat Drones</td><td align="center">'.$planet->drones.'</td><td>&nbsp;</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td>Turrets</td><td align="center">' . $planet->getBuilding(3) . '</td><td align="center">' . $planet->accuracy() . ' %</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('<br />');

$db->query('SELECT * FROM player WHERE sector_id = '.$player->getSectorID().' AND ' .
                                      'game_id = '.SmrSession::$game_id.' AND ' .
                                      'account_id != '.SmrSession::$account_id.' AND ' .
                                      'land_on_planet = \'TRUE\' ' .
                                'ORDER BY last_cpl_action DESC');

while ($db->nextRecord()) {

    $planet_player =& SmrPlayer::getPlayer($db->getField('account_id'), SmrSession::$game_id);

    $container = array();
    $container['url']            = 'planet_kick_processing.php';
    $container['account_id']    = $planet_player->getAccountID();

    $PHP_OUTPUT.=create_echo_form($container);

    $container = array();
    $container['url']        = 'skeleton.php';
    $container['body']        = 'trader_search_result.php';
    $container['player_id']    = $planet_player->getPlayerID();

    $PHP_OUTPUT.=create_link($container, '<span style="color:yellow;">'.$planet_player->getPlayerName().'</span>');
    $PHP_OUTPUT.=('&nbsp;');

    // should we be able to kick this player from our rock?
    if ($planet->getOwnerID() == $player->getAccountID())
        $PHP_OUTPUT.=create_submit('Kick');

    $PHP_OUTPUT.=('</form>');

}
if($db->getNumRows() > 0 ) $PHP_OUTPUT.=('<br />');

$PHP_OUTPUT.=create_echo_form(create_container('planet_launch_processing.php', ''));
$PHP_OUTPUT.=create_submit('Launch');
$PHP_OUTPUT.=('</form>');

?>