<?php
$template->assign('PageTopic','Dump Cargo');

$PHP_OUTPUT.=('Enter the amount of cargo you wish to jettison.<br />');
$PHP_OUTPUT.=('Please keep in mind that you will lose experience and one turn!<br /><br />');

$db->query('SELECT * FROM ship_has_cargo JOIN good USING(good_id) ' .
		'WHERE account_id = ' . $db->escapeNumber($player->getAccountID()) . ' AND ' .
				'game_id = ' . $db->escapeNumber($player->getGameID()));
if ($db->getNumRows()) {
	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Good</th>');
	$PHP_OUTPUT.=('<th>Amount to Drop</th>');
	$PHP_OUTPUT.=('<th>Action</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->nextRecord()) {
		$good_id	= $db->getInt('good_id');
		$good_name	= $db->getField('good_name');
		$amount		= $db->getInt('amount');

		$container = create_containter('cargo_dump_processing.php');
		$container['good_id'] = $good_id;

		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center">'.$good_name.'</td>');
		$PHP_OUTPUT.=('<td align="center"><input type="text" name="amount" value="'.$amount.'" maxlength="5" size="5" id="InputFields" class="center">');
		$PHP_OUTPUT.=('<td align="center">');
		$PHP_OUTPUT.=create_submit('Dump');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');
		$PHP_OUTPUT.=('</form>');
	}
	$PHP_OUTPUT.=('</table>');
}
else {
	$PHP_OUTPUT.=('You have no cargo to dump!');
}

?>