<?php

$template->assign('PageTopic','Report a Bug');

$PHP_OUTPUT.=('<span style="font-size:75%;">Please use this form to either send your feedback or<br />');
$PHP_OUTPUT.=('questions to the admin team of Space Merchant Realms!</span>');

$PHP_OUTPUT.=create_echo_form(create_container('contact_processing.php', ''));

$PHP_OUTPUT.=('<table>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td class="bold">From:</td>');
$PHP_OUTPUT.=('<td>'.$account->getLogin().'</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td class="bold">To:</td>');
$PHP_OUTPUT.=('<td>');
$PHP_OUTPUT.=('<select name="receiver">');
$PHP_OUTPUT.=('<option default>support@smrealms.de</option>');
$PHP_OUTPUT.=('<option>multi@smrealms.de</option>');
$PHP_OUTPUT.=('<option>beta@smrealms.de</option>');
$PHP_OUTPUT.=('<option>chat@smrealms.de</option>');
$PHP_OUTPUT.=('</select>');
$PHP_OUTPUT.=('</td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td class="bold">Subject:</td>');
$PHP_OUTPUT.=('<td><input type="text" name="subject" id="InputFields" style="width:500px;"></td>');
$PHP_OUTPUT.=('</tr>');

$PHP_OUTPUT.=('<tr>');
$PHP_OUTPUT.=('<td class="bold" valign="top">Message:</td>');
$PHP_OUTPUT.=('<td><textarea spellcheck="true" id="InputFields" name="msg" style="width:500px;height:400px;"></textarea></td>');
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