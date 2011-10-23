<?php
$template->assign('PageTopic','Newsletter');

$PHP_OUTPUT.=('This uses the last newsletter to be added to the DB!<br />Please enter an eMail address where the newsletter should be sent (* for all):');
$PHP_OUTPUT.=create_echo_form(create_container('newsletter_send_processing.php', ''));
$PHP_OUTPUT.=('<input type="text" name="to_email" value="'.htmlspecialchars($account->getEmail()).'" id="InputFields" size="25">&nbsp;');
$PHP_OUTPUT.=create_submit('Send');
$PHP_OUTPUT.=('</form>');

?>