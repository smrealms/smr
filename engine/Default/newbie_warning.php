<?php

$player->setNewbieWarning(false);

$template->assign('PageTopic','Warning!');

$PHP_OUTPUT.=('<p>You have received this page because you are almost out of newbie turns.');
$PHP_OUTPUT.=('What does this mean? You can now do many things that you couldn\'t do while in newbie turns.</p>');

$PHP_OUTPUT.=('<ol>You can...');
$PHP_OUTPUT.=('<li>attack other players</li>');
$PHP_OUTPUT.=('<li>attack enemy planet\'s</li>');
$PHP_OUTPUT.=('<li>Port raid</li>');
$PHP_OUTPUT.=('<li>Place forces</li>');
$PHP_OUTPUT.=('</ol>');

$PHP_OUTPUT.=('<p>But remember, with the good comes the bad. In addition to being able to do the above, they can happan to you.</p>');

$PHP_OUTPUT.=('<ol>You can...</li>');
$PHP_OUTPUT.=('<li>Be Attacked</li>');
$PHP_OUTPUT.=('<li>Hit enemy forces and take damage</li>');
$PHP_OUTPUT.=('<li>die...</li>');
$PHP_OUTPUT.=('</ol>');

$PHP_OUTPUT.=('<p>Plan for your safety now. Remember to use federal Protection (Must have an attack rating of 3 or less), know where your alliances strongest planet\'s are, and watch out for the people looking for you.</p>');

$PHP_OUTPUT.=('<p>For more information visit the <a href="'.URL.'/manual.php">help files</a></p>');

?>