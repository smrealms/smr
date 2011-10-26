<?php

$template->assign('PageTopic','Galactic Post Application');
require_once(get_file_loc('menu.inc'));
create_galactic_post_menue();
$container = array();
$container['url'] = 'galactic_post_application_processing.php';
$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<br />Have you ever written for any type of newspaper before?<br />');
$PHP_OUTPUT.=('Yes : <input type="radio" name="exp" value="1"><br />');
$PHP_OUTPUT.=('No : <input type="radio" name="exp" value="2"><br />');
$PHP_OUTPUT.=('<br />');
$PHP_OUTPUT.=('How many articles would you write per week if you were selected?<br />');
$PHP_OUTPUT.=('<input type="text" name="amount" value="0" id="InputFields" style="text-align:right;width:25;">');
$PHP_OUTPUT.=('<br />');
$PHP_OUTPUT.=('In 255 characters or less please describe why you should be accepted<br />');
$PHP_OUTPUT.=('<textarea name="message" id="InputFields"></textarea>');
$PHP_OUTPUT.=('<br /><br />');
$PHP_OUTPUT.=create_submit('Apply');
$PHP_OUTPUT.=('</form>');

?>