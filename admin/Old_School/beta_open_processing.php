<?php

if ($var['open'])
	$db->query('UPDATE beta_test SET open = \'FALSE\'');
else
	$db->query('UPDATE beta_test SET open = \'TRUE\'');

forward(create_container('skeleton.php', 'game_play.php'));

?>