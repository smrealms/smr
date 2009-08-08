<?php

$template->assign('PageTopic','SEARCH TRADER');
$PHP_OUTPUT.=('<p>&nbsp;</p>');

$PHP_OUTPUT.=create_echo_form(create_container('skeleton.php', 'trader_search_result.php'));

$PHP_OUTPUT.=('<span style="font-size:75%;">Player name:</span><br />');
$PHP_OUTPUT.=('<input type="text" name="player_name" id="InputFields" style="width:150px">&nbsp;');
$PHP_OUTPUT.=create_submit('Search');

$PHP_OUTPUT.=('<p>&nbsp;</p>');

$PHP_OUTPUT.=('<span style="font-size:75%;">Player ID:</span><br />');
$PHP_OUTPUT.=('<input type="text" name="player_id" id="InputFields" style="width:50px">&nbsp;');
$PHP_OUTPUT.=create_submit('Search');

$PHP_OUTPUT.=('</form>');

?>