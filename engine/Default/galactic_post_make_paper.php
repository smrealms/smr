<?php

$template->assign('PageTopic','Making A Paper');
include(get_file_loc('menue.inc'));
$PHP_OUTPUT.=create_galactic_post_menue();
$PHP_OUTPUT.=('What is the title of this edition?<br />');
$container = array();
$container['url'] = 'galactic_post_make_paper_processing.php';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<input type="text" name="title" id="InputFields" class="center" style="width:525;"><br /><br />');
$PHP_OUTPUT.=create_submit('Make the paper');
$PHP_OUTPUT.=('</form>');

?>