<?php

$template->assign('PageTopic','Delete Album Entry - Confirmation');

$PHP_OUTPUT.=('Are you sure you want to delete your photo album entry and all comments added to it?<br />');
$PHP_OUTPUT.=('This action can\'t be undone.');

$PHP_OUTPUT.=create_echo_form(create_container('album_delete_processing.php', ''));

$PHP_OUTPUT.=create_submit('Yes');
$PHP_OUTPUT.=('&nbsp;&nbsp;&nbsp;');
$PHP_OUTPUT.=create_submit('No');

$PHP_OUTPUT.=('</form>');

?>