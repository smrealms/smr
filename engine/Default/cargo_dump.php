<?

$smarty->assign('PageTopic','DUMP CARGO');

$PHP_OUTPUT.=('Enter the amount of cargo you wish to jettison.<br />');
$PHP_OUTPUT.=('Please keep in mind that you will lose experience and one turn!<br /><br />');

$db->query('SELECT * FROM ship_has_cargo NATURAL JOIN good ' .
		   'WHERE account_id = '.$player->getAccountID().' AND ' .
				 'game_id = '.$player->getGameID());
if ($db->nf()) {

	$PHP_OUTPUT.=create_table();
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<th>Good</th>');
	$PHP_OUTPUT.=('<th>Amount to Drop</th>');
	$PHP_OUTPUT.=('<th>Action</th>');
	$PHP_OUTPUT.=('</tr>');

	while ($db->next_record()) {

		$good_id	= $db->f('good_id');
		$good_name	= $db->f('good_name');
		$amount		= $db->f('amount');

		$container = array();
		$container['url'] = 'cargo_dump_processing.php';
		$container['good_id'] = $good_id;
		$container['good_name'] = $good_name;

		$PHP_OUTPUT.=create_echo_form($container);
		$PHP_OUTPUT.=('<tr>');
		$PHP_OUTPUT.=('<td align="center">'.$good_name.'</td>');
		$PHP_OUTPUT.=('<td align="center"><input type="text" name="amount" value="'.$amount.'" maxlength="5" size="5" id="InputFields" style="text-align:center;">');
		$PHP_OUTPUT.=('<td align="center">');
		$PHP_OUTPUT.=create_submit('Dump');
		$PHP_OUTPUT.=('</td>');
		$PHP_OUTPUT.=('</tr>');
		$PHP_OUTPUT.=('</form>');

	}

	$PHP_OUTPUT.=('</table>');

} else
	$PHP_OUTPUT.=('You have no cargo to dump!');

?>