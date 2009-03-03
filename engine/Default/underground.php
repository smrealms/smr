<?

if ($player->getAlignment() >= 100) {

	create_error('You are not allowed to come in here!');
	return;

}

$template->assign('PageTopic','Underground HQ');

include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_ug_menue();

$db2 = new SmrMySqlDatabase();
$db->query('SELECT * FROM bounty WHERE game_id = '.$player->getGameID().' AND type = \'UG\' AND claimer_id = 0 ORDER BY amount DESC');
if ($db->getNumRows())
{
	$PHP_OUTPUT.=('Most Wanted by the Underground<br /><br />');
	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Player Name</th>');
	$PHP_OUTPUT.=('<th>Bounty Amount</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->nextRecord())
	{
		$id = $db->getField('account_id');
		$db2->query('SELECT * FROM player WHERE game_id = '.$player->getGameID().' AND account_id = '.$id);
		if ($db2->nextRecord())
		{
			$name = stripslashes($db2->getField('player_name'));
			$amount = $db->getField('amount');
			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<td align="center"><font color=yellow>'.$name.'</font></td>');
			$PHP_OUTPUT.=('<td align="center"><font color=red> ' . number_format($amount) . ' </font></td>');
			$PHP_OUTPUT.=('</tr>');
		}
	}
	$PHP_OUTPUT.=('</table>');
}

$db->query('SELECT * FROM bounty WHERE game_id = '.$player->getGameID().' AND type = \'UG\' AND claimer_id = '.$player->getAccountID().' ORDER BY amount DESC');
$PHP_OUTPUT.=('<p>&nbsp;</p>');
if ($db->getNumRows())
{
	$PHP_OUTPUT.=('<div align="center">Claimable Bounties</div><br />');
	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Player Name</th>');
	$PHP_OUTPUT.=('<th>Bounty Amount</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->nextRecord())
	{
		$id = $db->getField('account_id');
		$db2->query('SELECT * FROM player WHERE game_id = '.$player->getGameID().' AND account_id = '.$id);
		if ($db2->nextRecord())
			$name = stripslashes($db2->getField('player_name'));
		$amount = $db->getField('amount');
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center"><font color=yellow>'.$name.'</font></td>');
		$PHP_OUTPUT.=('<td align="center"><font color=red> ' . number_format($amount) . ' </font></td>');
		$PHP_OUTPUT.=('</tr>');

	}
	$PHP_OUTPUT.=('</table>');
}

if ($player->getAlignment() <= 99 && $player->getAlignment() >= -100) {

	$PHP_OUTPUT.=create_echo_form(create_container('government_processing.php', ''));
	$PHP_OUTPUT.=create_submit('Become a gang member');
	$PHP_OUTPUT.=('</form>');

}
?>