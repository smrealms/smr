<?php

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
		$db = new SmrMySqlDatabase();
		$db2 = new SmrMySqlDatabase();

		$db->query('SELECT * FROM player WHERE player_name = ' . $db->escape_string($msg[4], true));
		if ($db->getNumRows()) {

			while($db->nextRecord()) {

				$player_name = stripslashes($db->getField('player_name'));
				$experience = $db->getField('experience');
				$game_id = $db->getField('game_id');

				$db2->query('SELECT COUNT(*) as our_rank FROM player ' .
							'WHERE game_id = '.$game_id.' AND ' .
								  '(experience > '.$experience.' OR ' .
								  '(experience = '.$experience.' AND ' .
								  'player_name <= ' . $db->escape_string($player_name, true) . ' ))');
				if ($db2->nextRecord())
					$our_rank = $db2->getField('our_rank');

				// how many players are there?
				$db2->query('SELECT COUNT(*) as total_player FROM player WHERE game_id = '.$game_id);
				if ($db2->nextRecord())
					$total_player = $db2->getField('total_player');

				$db2->query('SELECT game_name FROM game WHERE game_id = '.$game_id);
				if ($db2->nextRecord())
					$game_name = $db2->getField('game_name');

				fputs($fp, 'NOTICE '.$msg[1].' :'.$msg[1].' you are ranked '.$our_rank.' out of '.$total_player.' in '.$game_name.'!'.EOL);

			}

		} else
			fputs($fp, 'NOTICE '.$msg[1].' :No Trader found that matches your query!'.EOL);

		return true;

	}

	return false;

}
