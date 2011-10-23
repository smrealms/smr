<?php

$template->assign('PageTopic','Report a Bug');

$PHP_OUTPUT.=('<span style="font-size:75%;">All information you can see on this page will be sent via email to the developer team!<br />');
$PHP_OUTPUT.=('Be as accurate as possible with your bug description.</span>');

$PHP_OUTPUT.=create_echo_form(create_container('bug_report_processing.php', ''));

$PHP_OUTPUT.=('<table>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td class="bold">Login:</td>');
$PHP_OUTPUT.=('<td>'.$account->login.'</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td class="bold">eMail:</td>');
$PHP_OUTPUT.=('<td>'.$account->email.'</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td class="bold">Account ID:</td>');
$PHP_OUTPUT.=('<td>'.$account->getAccountID().'</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td class="bold">Subject:</td>');
$PHP_OUTPUT.=('<td><input type="text" name="subject" id="InputFields" style="width:300px;"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td class="bold" valign="top">Description:</td>');
$PHP_OUTPUT.=('<td><textarea id="InputFields" name="description" style="width:300px;height:100px;"></textarea></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td class="bold" valign="top">Steps to repeat:</td>');
$PHP_OUTPUT.=('<td><textarea id="InputFields" name="steps" style="width:300px;height:100px;"></textarea></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td class="bold" valign="top">Error Message:</td>');
$PHP_OUTPUT.=('<td><textarea id="InputFields" name="error_msg" style="width:300px;height:100px;"></textarea></td>');
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