<?php

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $player->alliance_id . ' LIMIT 1');
$db->next_record();
print_topic($player->alliance_name . ' (' . $player->alliance_id . ')');
include(get_file_loc('menue.inc'));
print_alliance_menue($player->alliance_id,$db->f('leader_id'));

$db->query('
SELECT
account_id,
player_id,
player_name,
last_active
FROM player
WHERE game_id=' . SmrSession::$game_id . '
AND alliance_id=' . $player->alliance_id .'
AND account_id<>' . SmrSession::$old_account_id . '
ORDER BY last_active DESC
');

echo '<div align="center">';

if ($db->nf() != 0) {

	$container=array();
	$container['url'] = 'alliance_remove_member_processing.php';
	$container['body'] = '';
	$form = create_form($container,'Banish \'em!');
	echo $form['form'];
	echo '<table cellspacing="0" cellpadding="0" class="standard inset"><th>Trader Name</th><th>Last Online</th><th>Action</th>';

	while ($db->next_record()) {

		// we won't exile ourself!
		if ($player->account_id != $account_id) {

			// get the amount of time since last_active
			$diff = time() - $db->f('last_active');

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

			echo '<tr><td>';
			echo stripslashes($db->f('player_name'));
			echo '(';
			echo $db->f('player_id');
			echo ')</td><td class="shrink nowrap center" style="color:' . $color;
			echo '">';
			echo date('n/j/Y g:i:s A', $db->f('last_active'));
			echo '</td><td class="shrink center">';

			echo '<input type="checkbox" name="account_id[]" value="';
			echo $db->f("account_id");
			echo '"></td></tr>';

		}

	} // end of while

	echo '</table><br>';

	echo $form['submit'];
	echo '</form>';

} else
	echo 'There is no-one to kick! You are all by yourself!';

echo '</div>';
?>