<?php

if (!Globals::isBetaOpen())
{
	create_error('Beta Applications are currently not being accepted.');
	return;
}

$smarty->assign('PageTopic','Apply for Beta');
$PHP_OUTPUT.=('The information on this page will be used by the beta team leader in choosing applicants.<br />');
$PHP_OUTPUT.=('You must fill in all fields for your application to be considered.');

$container = array();
$container['url'] = 'beta_apply_processing.php';

$PHP_OUTPUT.=create_echo_form($container);

$PHP_OUTPUT.=('<table>');
$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td style="font-weight:bold;">Login:</td>');
$PHP_OUTPUT.=('<input type="hidden" name="login" value="'.$account->login.'">');
$PHP_OUTPUT.=('<td>'.$account->login.'</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td style="font-weight:bold;">eMail:</td>');
$PHP_OUTPUT.=('<input type="hidden" name="email" value="'.$account->email.'">');
$PHP_OUTPUT.=('<td>'.$account->email.'</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td style="font-weight:bold;">Account ID:</td>');
$PHP_OUTPUT.=('<td>'.$account->account_id.'</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td style="font-weight:bold;">WebBoard Name:</td>');
$PHP_OUTPUT.=('<td><input type="text" name="webboard" id="InputFields" style="width:300px;"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td style="font-weight:bold;">IRC Nick:</td>');
$PHP_OUTPUT.=('<td><input type="text" name="ircnick" id="InputFields" style="width:300px;"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td style="font-weight:bold;">Approx. time you started playing:</td>');
$PHP_OUTPUT.=('<td><input type="text" name="started" id="InputFields" style="width:300px;"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td style="font-weight:bold;" valign="top">Why you think you should become a beta tester:</td>');
$PHP_OUTPUT.=('<td><textarea id="InputFields" name="reasons" style="width:300px;height:100px;"></textarea></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td style="font-weight:bold;" valign="top">How much time you can spend on beta per week:</td>');
$PHP_OUTPUT.=('<td><input type="text" name="time" id="InputFields" style="width:300px;"></td>');;
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td style="font-weight:bold;" valign="top">Most frequent online times (in server time):</td>');
$PHP_OUTPUT.=('<td><input type="text" name="online" id="InputFields" style="width:300px;"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td></td>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=create_submit('Submit');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('</table>');
$PHP_OUTPUT.=('</form>');

?>