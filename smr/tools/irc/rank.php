<?

function channel_msg_rank($fp, $rdata) {

	global $channel, $nick;

	// did he gave us no parameter?
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s'.$channel.'\s:!rank\s$/i', $rdata, $msg) ||
		preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s'.$nick.'\s:rank\s$/i', $rdata, $msg)) {

		echo_r($msg);
		fputs($fp, 'NOTICE '.$msg[1].' :SYNTAX !rank <nick>'.EOL);
		return true;

	}

	// in channel we only accept !rank
	// in private msg we accept both
	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s'.$channel.'\s:!rank\s(.*)\s$/i', $rdata, $msg) ||
		preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s'.$nick.'\s:?rank\s(.*)\s$/i', $rdata, $msg)) {

		echo_r($msg);
		$db = new SMR_DB();
		$db2 = new SMR_DB();

		$db->query('SELECT * FROM player WHERE player_name = ' . $db->escape_string($msg[4], true));
		if ($db->nf()) {

			while($db->next_record()) {

				$player_name = stripslashes($db->f('player_name'));
				$experience = $db->f('experience');
				$game_id = $db->f('game_id');

				$db2->query('SELECT COUNT(*) as our_rank FROM player ' .
							'WHERE game_id = '.$game_id.' AND ' .
								  '(experience > '.$experience.' OR ' .
								  '(experience = '.$experience.' AND ' .
								  'player_name <= ' . $db->escape_string($player_name, true) . ' ))');
				if ($db2->next_record())
					$our_rank = $db2->f('our_rank');

				// how many players are there?
				$db2->query('SELECT COUNT(*) as total_player FROM player WHERE game_id = '.$game_id);
				if ($db2->next_record())
					$total_player = $db2->f('total_player');

				$db2->query('SELECT game_name FROM game WHERE game_id = '.$game_id);
				if ($db2->next_record())
					$game_name = $db2->f('game_name');

				fputs($fp, 'NOTICE '.$msg[1].' :'.$msg[1].' you are ranked '.$our_rank.' out of '.$total_player.' in '.$game_name.'!'.EOL);

			}

		} else
			fputs($fp, 'NOTICE '.$msg[1].' :No Trader found that matches your query!'.EOL);

		return true;

	}

	return false;

}

?>