<?php declare(strict_types=1);

//get our variables
$game_id = $var['game_id'];
$hardware_id = $var['hardware'];
$max_amount = $var['max_amount'];
$player_id = $var['player_id'];

//update it so they arent cheating
$db->query('UPDATE ship_has_hardware ' .
		   'SET amount = ' . $db->escapeNumber($max_amount) . ' ' .
		   'WHERE game_id = ' . $db->escapeNumber($game_id) . ' AND ' .
				 'player_id = ' . $db->escapeNumber($player_id) . ' AND ' .
				 'hardware_type_id = ' . $db->escapeNumber($hardware_id));

//now erdirect back to page
$container = create_container('skeleton.php', 'ship_check.php');
forward($container);
