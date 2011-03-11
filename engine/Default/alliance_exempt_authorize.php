<?php

$db->query('SELECT leader_id FROM alliance WHERE game_id=' . SmrSession::$game_id . ' AND alliance_id=' . $player->getAllianceID() . ' LIMIT 1');
$db->nextRecord();
$template->assign('PageTopic',$player->getAllianceName() . ' (' . $player->getAllianceID() . ')');
include(get_file_loc('menue.inc'));
create_alliance_menue($player->getAllianceID(),$db->getField('leader_id'));

$PHP_OUTPUT.= '<h2>Exemption Requests</h2><br />';
$PHP_OUTPUT.=('Selecting a box will authorize it, leaving a box unselected will make it unauthorized after you submit.<br />');
//get rid of already approved entries
$db->query('UPDATE alliance_bank_transactions SET request_exempt = 0 WHERE exempt = 1');
//build player array
$db->query('SELECT * FROM player WHERE alliance_id = '.$player->getAllianceID().' AND game_id = '.$player->getGameID());
while ($db->nextRecord()) $players[$db->getField('account_id')] = $db->getField('player_name');
$db->query('SELECT * FROM alliance_bank_transactions WHERE request_exempt = 1 ' . 
			'AND alliance_id = '.$player->getAllianceID().' AND game_id = '.$player->getGameID().' AND exempt = 0');
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