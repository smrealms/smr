<?php

$template->assign('PageTopic','Making A Paper');
require_once(get_file_loc('menu.inc'));
create_galactic_post_menu();
$PHP_OUTPUT.=('What is the title of this edition?<br />');
$container = array();
$container['url'] = 'galactic_post_make_paper_processing.php';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<input type="text" name="title" id="InputFields" class="center" style="width:525;"><br /><br />');
$PHP_OUTPUT.=create_submit('Make the paper');
$PHP_OUTPUT.=('</form>');
