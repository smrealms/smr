<?

$smarty->assign('PageTopic','HALL OF FAME');

$PHP_OUTPUT.=('Welcome to the Hall of Fame $player->getPlayerName()!<br />The Hall of Fame is a comprehensive ');
$PHP_OUTPUT.=('list of player accomplishments. Here you can view how players rank in many different ');
$PHP_OUTPUT.=('aspects of the game rather than just kills, deaths, and experience with the rankings system.<br />');
$PHP_OUTPUT.=('The Hall of Fame is updated only once every 24 hours on midnight.<br />');

//put em in a table so they look kinda nice
$PHP_OUTPUT.=create_table();
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th align="center">Type</th>');
$PHP_OUTPUT.=('<th align="center">Options</th>');
$PHP_OUTPUT.=('</tr>');

//let them choose what they want to view...all container info is used below
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'hall_of_fame_detail.php';
transfer('type');

$container['id'] = 'donation';
$container['display_first'] = 'Rankings by amount of money donated to Space Merchant Relams';
$container['display_second'] = 'Amount of Money Donated';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center" nowrap><b>Ranks by Money Donated to <i>Space Merchant Realms</i></b></td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'kills';
$container['display_first'] = 'Rankings by number of kills';
$container['display_second'] = 'Number of Kills';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Kills</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Death');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'deaths';
$container['display_first'] = 'Rankings by number of deaths';
$container['display_second'] = 'Number of Deaths';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Deaths</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'planet_busts';
$container['display_first'] = 'Rankings by number of planet busts';
$container['display_second'] = 'Number of Planet Busts';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Planet Busts</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'planet_bust_levels';
$container['display_first'] = 'Rankings by number of planet levels busted';
$container['display_second'] = 'Number of Levels Busted on Planets';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Planet Levels Busted</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'port_raids';
$container['display_first'] = 'Rankings by number of ports raided';
$container['display_second'] = 'Number of Ports Raided';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Port Raids</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'port_raid_levels';
$container['display_first'] = 'Rankings by number of port levels raided';
$container['display_second'] = 'Number of Port Levels Raided';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Port Levels Raided</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'sectors_explored';
$container['display_first'] = 'Rankings by sectors explored';
$container['display_second'] = 'Number of Sectors Explored';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Sectors Explored</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'goods_traded';
$container['display_first'] = 'Rankings by number of goods traded';
$container['display_second'] = 'Number of Goods Traded';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Goods Traded</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'experience_traded';
$container['display_first'] = 'Rankings by amount of experience traded';
$container['display_second'] = 'Amount of Experience Traded';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Experience Traded</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Good Traded');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'bounties_claimed';
$container['display_first'] = 'Rankings by number of bounties claimed.';
$container['display_second'] = 'Number of bounties claimed';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Bounties Collected</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'bounty_amount_claimed';
$container['display_first'] = 'Rankings by amount of bounties collected.';
$container['display_second'] = 'Amount of bounties collected';
$container['per'] = '';
$container['special'] = 'no';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Credits Collected From Bounties</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'bounty_amount_on';
$container['display_first'] = 'Rankings by amount of credits from bounties placed on a player.';
$container['display_second'] = 'Amount of credits';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Bounties Placed on a Player</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'military_claimed';
$container['display_first'] = 'Rankings by amount of military payment claimed.';
$container['display_second'] = 'Amount of military payment claimed';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Military Payment Claimed</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'bonus_turns';
$container['display_first'] = 'Rankings by bonus turns gained by voting for Space Merchant Realms';
$container['display_second'] = 'Number of Bonus Turns Gained';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Bonus Turns Gained by Voting</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'player_damage';
$container['display_first'] = 'Rankings by damage done to other players';
$container['display_second'] = 'Damage Done';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Damage Done to Other Players</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Kill');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'planet_damage';
$container['display_first'] = 'Rankings by damage done to planets';
$container['display_second'] = 'Damage Done';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Damage Done to Planets</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Planet Bust');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'port_damage';
$container['display_first'] = 'Rankings by damage done to ports';
$container['display_second'] = 'Damage Done';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Ranks by Damage Done to Ports</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Game');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('Per Port Raid');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'player_damage';
$container['display_first'] = 'Rankings by';
$container['display_second'] = '';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Attacking other Ships</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Experience Gained');
//duplicate
//$PHP_OUTPUT.=('&nbsp;');
//$PHP_OUTPUT.=create_submit('Damage Done');
$PHP_OUTPUT.=('<br />');
$PHP_OUTPUT.=create_submit('Experience From Killing');
$PHP_OUTPUT.=('<br />');
$PHP_OUTPUT.=create_submit('Total Experience of Players Killed');
$PHP_OUTPUT.=('<br />');
$PHP_OUTPUT.=create_submit('Average Experience of Players Killed');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'port_damage';
$container['display_first'] = 'Rankings by';
$container['display_second'] = '';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Attacking Ports</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Experience Gained');
//duplicate
//$PHP_OUTPUT.=('&nbsp;');
//$PHP_OUTPUT.=create_submit('Damage Done');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'planet_damage';
$container['display_first'] = 'Rankings by';
$container['display_second'] = '';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Attacking Planets</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Experience Gained');
//duplicate
//$PHP_OUTPUT.=('&nbsp;');
//$PHP_OUTPUT.=create_submit('Damage Done');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$container['id'] = 'turns_used';
$container['display_first'] = 'Rankings by turns used since last death';
$container['display_second'] = 'Turns Used';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td align="center">Turns Used Since Last Death</td>');
$PHP_OUTPUT.=('<td align="center">');
$PHP_OUTPUT.=create_submit('Overall');
$PHP_OUTPUT.=('</td></tr>');
$PHP_OUTPUT.=('</form>');

$PHP_OUTPUT.=('</table>');

?>