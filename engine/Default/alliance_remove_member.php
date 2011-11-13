<?php
$alliance =&$player->getAlliance();
$template->assign('PageTopic',$alliance->getAllianceName() . ' (' . $alliance->getAllianceID() . ')');
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

$db->query('
SELECT
account_id,
player_id,
player_name,
last_cpl_action
FROM player
WHERE game_id = ' . $alliance->getGameID() . '
AND alliance_id = ' . $alliance->getAllianceID() .'
AND account_id != ' . $player->getAccountID() . '
ORDER BY last_cpl_action DESC
');

$PHP_OUTPUT.= '<div align="center">';

if ($db->getNumRows() != 0)
{
	$container=create_container('alliance_remove_member_processing.php');
	$form = create_form($container,'Banish \'em!');
	$PHP_OUTPUT.= $form['form'];
	$PHP_OUTPUT.= '<table class="standard inset"><th>Trader Name</th><th>Last Online</th><th>Action</th>';

	while ($db->nextRecord())
	{
		// we won't exile ourself!
		if ($player->getAccountID() != $account_id)
		{
			// get the amount of time since last_active
			$diff = TIME - $db->getInt('last_cpl_action');

			if ($diff > 864000)
				$diff = 864000;

			// green
			if ($diff < 432000)
			{
				// scale it down to 255
				$dec = round($diff / 1694);

				// make it hex and add leading zero if necessary
				($dec < 16) ? $color = '0' . dechex($dec) : $color = dechex($dec);

				// make it a full color code
				$color = '#' . $color . 'FF00';

			// red
			}
			else
			{
				// scale it down to 255
				$dec = round((864000 - $diff) / 1694);

				// make it hex and add leading zero if necessary
				($dec < 16) ? $color = '0' . dechex($dec) : $color = dechex($dec);

				// make it a full color code
				$color = '#FF' . $color . '00';
			}

			$PHP_OUTPUT.= '<tr><td>'.$db->getField('player_name').'('.$db->getField('player_id').')</td>';
			$PHP_OUTPUT.= '<td class="shrink noWrap center" style="color:' . $color.'">';
			$PHP_OUTPUT.= date(DATE_FULL_SHORT, $db->getField('last_cpl_action'));
			$PHP_OUTPUT.= '</td><td class="shrink center">';

			$PHP_OUTPUT.= '<input type="checkbox" name="account_id[]" value="'.$db->getField('account_id').'"></td></tr>';

		}

	} // end of while

	$PHP_OUTPUT.= '</table><br />';

	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.= '</form>';
}
else
	$PHP_OUTPUT.= 'There is no-one to kick! You are all by yourself!';

$PHP_OUTPUT.= '</div>';
?>