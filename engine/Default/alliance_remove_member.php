<?

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $player->getAllianceID() . ' LIMIT 1');
$db->next_record();
$smarty->assign('PageTopic',$player->getAllianceName() . ' (' . $player->getAllianceID() . ')');
include(ENGINE . 'global/menue.inc');
$PHP_OUTPUT.=create_alliance_menue($player->getAllianceID(),$db->f('leader_id'));

$db->query('
SELECT
account_id,
player_id,
player_name,
last_cpl_action
FROM player
WHERE game_id=' . SmrSession::$game_id . '
AND alliance_id=' . $player->getAllianceID() .'
AND account_id<>' . SmrSession::$account_id . '
ORDER BY last_cpl_action DESC
');

$PHP_OUTPUT.= '<div align="center">';

if ($db->nf() != 0) {

	$container=array();
	$container['url'] = 'alliance_remove_member_processing.php';
	$container['body'] = '';
	$form = create_form($container,'Banish \'em!');
	$PHP_OUTPUT.= $form['form'];
	$PHP_OUTPUT.= '<table cellspacing="0" cellpadding="0" class="standard inset"><th>Trader Name</th><th>Last Online</th><th>Action</th>';

	while ($db->next_record()) {

		// we won't exile ourself!
		if ($player->getAccountID() != $account_id) {

			// get the amount of time since last_active
			$diff = time() - $db->f('last_cpl_action');

			if ($diff > 864000)
				$diff = 864000;

			// green
			if ($diff < 432000) {

				// scale it down to 255
				$dec = round($diff / 1694);

				// make it hex and add leading zero if necessary
				($dec < 16) ? $color = '0' . dechex($dec) : $color = dechex($dec);

				// make it a full color code
				$color = '#' . $color . 'FF00';

			// red
			} else {

				// scale it down to 255
				$dec = round((864000 - $diff) / 1694);

				// make it hex and add leading zero if necessary
				($dec < 16) ? $color = '0' . dechex($dec) : $color = dechex($dec);

				// make it a full color code
				$color = '#FF' . $color . '00';

			}

			$PHP_OUTPUT.= '<tr><td>';
			$PHP_OUTPUT.= stripslashes(echof('player_name'));
			$PHP_OUTPUT.= '(';
			$PHP_OUTPUT.= $db->f('player_id');
			$PHP_OUTPUT.= ')</td><td class="shrink nowrap center" style="color:' . $color;
			$PHP_OUTPUT.= '">';
			$PHP_OUTPUT.= date('n/j/Y g:i:s A', $db->f('last_cpl_action'));
			$PHP_OUTPUT.= '</td><td class="shrink center">';

			$PHP_OUTPUT.= '<input type="checkbox" name="account_id[]" value="';
			$PHP_OUTPUT.= $db->f('account_id');
			$PHP_OUTPUT.= '"></td></tr>';

		}

	} // end of while

	$PHP_OUTPUT.= '</table><br />';

	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.= '</form>';

} else
	$PHP_OUTPUT.= 'There is no-one to kick! You are all by yourself!';

$PHP_OUTPUT.= '</div>';
?>