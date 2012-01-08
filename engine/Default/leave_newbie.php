<?php

$template->assign('PageTopic','Leave Newbie Protection');

$PHP_OUTPUT.=create_echo_form(create_container('leave_newbie_processing.php', ''));
$PHP_OUTPUT.=('Do you really want to leave Newbie Protection?<br /><br />');
$PHP_OUTPUT.=create_submit('Yes!');
$PHP_OUTPUT.=('&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('No!');
$PHP_OUTPUT.=('</form>');

?>