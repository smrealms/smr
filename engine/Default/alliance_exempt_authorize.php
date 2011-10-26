<?php
$alliance =& $player->getAlliance();
$template->assign('PageTopic',$alliance->getAllianceName() . ' (' . $alliance->getAllianceID() . ')');
require_once(get_file_loc('menu.inc'));
create_alliance_menu($alliance->getAllianceID(),$alliance->getLeaderID());

$PHP_OUTPUT.= '<h2>Exemption Requests</h2><br />';
$PHP_OUTPUT.=('Selecting a box will authorize it, leaving a box unselected will make it unauthorized after you submit.<br />');
//get rid of already approved entries
$db->query('UPDATE alliance_bank_transactions SET request_exempt = 0 WHERE exempt = 1');
//build player array
$db->query('SELECT * FROM player WHERE alliance_id = '.$alliance->getAllianceID().' AND game_id = '.$alliance->getGameID());
while ($db->nextRecord()) $players[$db->getField('account_id')] = $db->getField('player_name');
$db->query('SELECT * FROM alliance_bank_transactions WHERE request_exempt = 1 ' . 
			'AND alliance_id = '.$alliance->getAllianceID().' AND game_id = '.$alliance->getGameID().' AND exempt = 0');
if ($db->getNumRows()) {
	$container=array();
	$container['url'] = 'bank_alliance_exempt_processing.php';
	$container['body'] = '';
	$form = create_form($container,'Make Exempt');
	$PHP_OUTPUT.= $form['form'];
	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr><th>Player Name</th><th>Type</th><th>Reason</th><th>Amount</th><th>Approve</th></tr>');
	while ($db->nextRecord()) {
		if ($db->getField('transaction') == 'Payment') $trans = 'Withdraw';
		else $trans = 'Deposit';
		$PHP_OUTPUT.=('<tr><td>' . $players[$db->getField('payee_id')] . '</td><td>' . $trans . '</td><td>' . $db->getField('reason') . '</td><td>' . $db->getField('amount') . '</td>');
		$PHP_OUTPUT.=('<td><input type="checkbox" name="exempt[' . $db->getField('transaction_id') . ']"></td>');
		$PHP_OUTPUT.=('</tr>');
		$temp[$db->getField('payee_id')] = array($db->getField('reason'), $db->getField('amount'));
	}
	$PHP_OUTPUT.=('</table>');
	$PHP_OUTPUT.=('<div align="center">');
	$PHP_OUTPUT.= $form['submit'];
	$PHP_OUTPUT.=('</div></form>');
} else $PHP_OUTPUT.=('<div align="center">Nothing to authorize.</div>');
?>