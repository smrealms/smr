<?

if ($player->getAlignment() >= 100) {

	$PHP_OUTPUT.=create_echo_error('You are not allowed to come in here!');
	return;

}

$smarty->assign('PageTopic','Underground HQ');

include($ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_ug_menue();

$db2 = new SMR_DB();
$db->query('SELECT * FROM bounty WHERE game_id = '.$player->getGameID().' AND type = \'UG\' AND claimer_id = 0 ORDER BY amount DESC');

if ($db->nf()) {

	$PHP_OUTPUT.=('Most Wanted by the Underground<br><br>');
	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Player Name</th>');
	$PHP_OUTPUT.=('<th>Bounty Amount</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->next_record()) {

		$id = $db->f('account_id');
		$db2->query('SELECT * FROM player WHERE game_id = '.$player->getGameID().' AND account_id = '.$id);
		if ($db2->next_record()) {

			$name = stripslashes($db2->f('player_name'));
			$amount = $db->f('amount');
			$PHP_OUTPUT.=('<tr>');
			$PHP_OUTPUT.=('<td align="center"><font color=yellow>'.$name.'</font></td>');
			$PHP_OUTPUT.=('<td align="center"><font color=red> ' . number_format($amount) . ' </font></td>');
			$PHP_OUTPUT.=('</tr>');

		}

	}

	$PHP_OUTPUT.=('</table>');

}


if ($player->getAlignment() <= 99 && $player->getAlignment() >= -100) {

	$PHP_OUTPUT.=create_echo_form(create_container('government_processing.php', ''));
	$PHP_OUTPUT.=create_submit('Become a gang member');
	$PHP_OUTPUT.=('</form>');

}
?>