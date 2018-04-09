<?php
$alliance =& $player->getAlliance();
$template->assign('PageTopic', $alliance->getAllianceName(false, true));
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

$PHP_OUTPUT.= '<h2>Exemption Requests</h2><br />';
$PHP_OUTPUT.=('Selecting a box will authorize it, leaving a box unselected will make it unauthorized after you submit.<br />');
//get rid of already approved entries
$db->query('UPDATE alliance_bank_transactions SET request_exempt = 0 WHERE exempt = 1');
//build player array
$db->query('SELECT * FROM player WHERE alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()));
while ($db->nextRecord()) {
	$players[$db->getInt('account_id')] = $db->getField('player_name');
}
$db->query('SELECT * FROM alliance_bank_transactions WHERE request_exempt = 1 ' . 
			'AND alliance_id = ' . $db->escapeNumber($alliance->getAllianceID()) . ' AND game_id = ' . $db->escapeNumber($alliance->getGameID()) . ' AND exempt = 0');
if ($db->getNumRows()) {
	$container=create_container('bank_alliance_exempt_processing.php');
	$form = create_form($container,'Make Exempt');
	$PHP_OUTPUT.= $form['form'];
	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr><th>Player Name</th><th>Type</th><th>Reason</th><th>Amount</th><th>Approve</th></tr>');
	while ($db->nextRecord()) {
		$trans = $db->getField('transaction') == 'Payment' ? 'Withdraw' : 'Deposit';
		$PHP_OUTPUT.=('<tr><td>' . $players[$db->getInt('payee_id')] . '</td><td>' . $trans . '</td><td>' . $db->getField('reason') . '</td><td>' . number_format($db->getInt('amount')) . '</td>');
		$PHP_OUTPUT.=('<td><input type="checkbox" name="exempt[' . $db->getField('transaction_id') . ']"></td>');
		$PHP_OUTPUT.=('</tr>');
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('<div align="center">');
	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.=('</div></form>');
}
else {
	$PHP_OUTPUT.=('<div align="center">Nothing to authorize.</div>');
}
