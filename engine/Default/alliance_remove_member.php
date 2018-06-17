<?php
$alliance =&$player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceName(false, true));
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

$db->query('
SELECT
account_id,
player_id,
player_name,
last_cpl_action
FROM player
WHERE game_id = ' . $db->escapeNumber($alliance->getGameID()) . '
AND alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) .'
AND account_id != ' . $db->escapeNumber($player->getAccountID()) . '
ORDER BY last_cpl_action DESC
');

$PHP_OUTPUT.= '<div align="center">';

if ($db->getNumRows() != 0) {
	$container=create_container('alliance_remove_member_processing.php');
	$form = create_form($container,'Banish \'em!');
	$PHP_OUTPUT.= $form['form'];
	$PHP_OUTPUT.= '<table class="standard inset"><th>Trader Name</th><th>Last Online</th><th>Action</th>';

	while ($db->nextRecord()) {
		// we won't exile ourself!
		if ($player->getAccountID() != $db->getInt('account_id')) {
			// get the amount of time since last_active
			$diff = 864000 + max(-864000, $db->getInt('last_cpl_action') - TIME);
			$lastActive = get_colored_text_range($diff, 864000, date(DATE_FULL_SHORT, $db->getInt('last_cpl_action')));

			$PHP_OUTPUT.= '<tr><td>'.$db->getField('player_name').' ('.$db->getInt('player_id').')</td>';
			$PHP_OUTPUT.= '<td class="shrink noWrap center">';
			$PHP_OUTPUT.= $lastActive;
			$PHP_OUTPUT.= '</td><td class="shrink center">';

			$PHP_OUTPUT.= '<input type="checkbox" name="account_id[]" value="'.$db->getInt('account_id').'"></td></tr>';
		}
	} // end of while

	$PHP_OUTPUT.= '</table><br />';
	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.= '</form>';
}
else {
	$PHP_OUTPUT.= 'There is no-one to kick! You are all by yourself!';
}

$PHP_OUTPUT.= '</div>';
