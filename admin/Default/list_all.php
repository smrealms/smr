<?php

$db2 = new SmrMySqlDatabase();

if(isset($_REQUEST['variable'])) SmrSession::updateVar('variable',$_REQUEST['variable']);

//split variable to get start and end
list ($start, $end) = explode(',', $var['variable']);
if(empty($start) || empty($end) || !is_numeric($start) || !is_numeric($end))
	create_error('Input was not in the correct format: "'.$var['variable'].'"');
$db->query('SELECT * FROM account WHERE account_id >= '.$db->escapeNumber($start).' AND account_id <= '.$db->escapeNumber($end).' ORDER BY account_id');
$PHP_OUTPUT.= create_table();
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<th align=center>Account_id</th>');
$PHP_OUTPUT.=('<th align=center>Login</th>');
$PHP_OUTPUT.=('<th align=center>eMail</th>');
$PHP_OUTPUT.=('<th align=center>Last IP</th>');
$PHP_OUTPUT.=('<th align=center>Exception</th>');
$PHP_OUTPUT.=('</tr>');
while ($db->nextRecord())
{

	$acc_id = $db->getField('account_id');
	$PHP_OUTPUT.=('<tr>');
	$PHP_OUTPUT.=('<td align=center>'.$acc_id.'</td>');
	$PHP_OUTPUT.=('<td align=center>' . $db->getField('login') . '</td>');
	$PHP_OUTPUT.=('<td align=center>' . $db->getField('email') . '</td>');
	$db2->query('SELECT * FROM account_has_ip WHERE account_id = '.$db2->escapeNumber($acc_id).' ORDER BY time DESC LIMIT 1');
	if ($db2->nextRecord())
		$PHP_OUTPUT.=('<td align=center>' . $db2->getField('ip') . '</td>');
	else
		$PHP_OUTPUT.=('<td align=center>No Last IP</td>');
	$db2->query('SELECT * FROM account_exceptions WHERE account_id = '.$db2->escapeNumber($acc_id));
	if ($db2->nextRecord())
		$PHP_OUTPUT.=('<td align=center>' . $db2->getField('reason') . '</td>');
	else
		$PHP_OUTPUT.=('<td align=center>No Exception</td>');
	$PHP_OUTPUT.=('</tr>');

}
$PHP_OUTPUT.=('</table>');
?>