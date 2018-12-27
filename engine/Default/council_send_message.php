<?php

$raceName = Globals::getRaceName($var['race_id']);

$template->assign('PageTopic','Send message to ruling council of the '.$raceName);

Menu::messages();

$PHP_OUTPUT.=('<p>');

$container = create_container('council_send_message_processing.php');
transfer('race_id');

$PHP_OUTPUT.=create_echo_form($container);
$PHP_OUTPUT.=('<p><small><b>From:</b> '.$player->getPlayerName().' ('.$player->getPlayerID().')<br />');

$PHP_OUTPUT.=('<b>To:</b> Ruling Council of '.$raceName.'</small></p>');

$PHP_OUTPUT.=('<textarea spellcheck="true" name="message" id="InputFields"></textarea><br /><br />');
$PHP_OUTPUT.=create_submit('Send message');
$PHP_OUTPUT.=('</form>');
$PHP_OUTPUT.=('</p>');
