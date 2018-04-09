<?php

$template->assign('PageTopic','Warning');

$PHP_OUTPUT.=('<p>As you approach the warp you notice a warning beacon nearby. The beacon sends an automated message to your ship.</p>');

$PHP_OUTPUT.=('<p>"Your racial government cannot protect low-ranking traders in the galaxy you are about to enter. In this area you will be vulnerable to attack by high-ranked ships. It is not recommended that you enter this area at your current status."</p>');

$container = create_container('sector_' . $var['method'] . '_processing.php', '');
transfer('target_page');
transfer('target_sector');

$PHP_OUTPUT.=('Are you sure you want to leave the newbie galaxy?');
$PHP_OUTPUT.=create_echo_form($container);

// for jump we need a 'to' field
$PHP_OUTPUT.=('<input type="hidden" name="to" value="'.$var['target_sector'].'">');

$PHP_OUTPUT.=create_submit('Yes');
$PHP_OUTPUT.=('&nbsp;');
$PHP_OUTPUT.=create_submit('No');
$PHP_OUTPUT.=('</form>');
