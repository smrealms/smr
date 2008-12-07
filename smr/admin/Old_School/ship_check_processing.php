<?

//get our variables
$game_id = $var['game_id'];
$hardware_id = $var['hardware'];
$max_amount = $var['max_amount'];
$account_id = $var['account_id'];

//update it so they arent cheating
$db->query('UPDATE ship_has_hardware ' .
		   'SET amount = '.$max_amount.' ' .
		   'WHERE game_id = '.$game_id.' AND ' .
				 'account_id = '.$account_id.' AND ' .
				 'hardware_type_id = '.$hardware_id);

//now erdirect back to page
$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'ship_check.php';
forward($container);

?>