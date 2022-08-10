<?php declare(strict_types=1);

use Smr\Database;

		$var = Smr\Session::getInstance()->getCurrentVar();

		//get our variables
		$game_id = $var['game_id'];
		$hardware_id = $var['hardware'];
		$max_amount = $var['max_amount'];
		$account_id = $var['account_id'];

		//update it so they arent cheating
		$db = Database::getInstance();
		$db->write('UPDATE ship_has_hardware ' .
				   'SET amount = ' . $db->escapeNumber($max_amount) . ' ' .
				   'WHERE game_id = ' . $db->escapeNumber($game_id) . ' AND ' .
						 'account_id = ' . $db->escapeNumber($account_id) . ' AND ' .
						 'hardware_type_id = ' . $db->escapeNumber($hardware_id));

		//now erdirect back to page
		$container = Page::create('admin/ship_check.php');
		$container->go();
