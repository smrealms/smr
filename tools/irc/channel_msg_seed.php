<?php

function channel_msg_seed($fp, $rdata, $account, $player)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!seed\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[SEED] by ' . $nick . ' in ' . $channel);

		// get the seedlist from db
		$db = new SmrMySqlDatabase();
		$db->query('SELECT sector_id
			FROM alliance_has_seedlist
			WHERE alliance_id = ' . $player->getAllianceID() . '
				AND game_id = ' . $player->getGameID() . '
				AND sector_id NOT IN (
					SELECT sector_id
					FROM sector_has_forces
					WHERE game_id = ' . $player->getGameID() . '
						AND owner_id = ' . $account->getAccountID() . '
				)');
		$missing_seeds = array();
		while ($db->nextRecord()) {
			array_push($missing_seeds, $db->getField('sector_id'));
		}

		if (count($missing_seeds) == 0) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', you seeded all sectors.' . EOL);
		} else {
			$seed_list = '';
			foreach ($missing_seeds as $sector) {
				$seed_list .= $sector . ', ';
			}
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', you are missing the following seeds:' . EOL);
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . substr_replace($seed_list, ' [' . count($missing_seeds) . ' missing seed(s)]', -2, 1) . EOL);
		}

		return true;

	}

	return false;

}

function channel_msg_seedlist($fp, $rdata)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!seedlist(\s*help)?\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];

		echo_r('[SEEDLIST] by ' . $nick . ' in ' . $channel);

		fputs($fp, 'PRIVMSG ' . $channel . ' :The !seedlist command enables alliance leader to add or remove sectors to the seedlist' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :The following sub commands are available:' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :  !seedlist add <sector1> <sector2> ...       Adds <sector> to the seedlist' . EOL);
		fputs($fp, 'PRIVMSG ' . $channel . ' :  !seedlist del <sector1> <sector2> ...       Removes <sector> from seedlist' . EOL);

		return true;

	}

	return false;

}

function channel_msg_seedlist_add($fp, $rdata, $account, $player)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!seedlist add (.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$sectors = explode(' ', $msg[5]);

		echo_r('[SEEDLIST_ADD] by ' . $nick . ' in ' . $channel);

		// check if $nick is leader
		if (!$player->isAllianceLeader(true)) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', only the leader of the alliance manages the seedlist.' . EOL);
			return true;
		}

		foreach($sectors as $sector) {
			// see if the sector is numeric
			if (!is_numeric($sector)) {
				fputs($fp, 'PRIVMSG ' . $channel . ' :The sectors all need to be numeric. Example: !seedlist add 1537' . EOL);
				return true;
			}
		}

		$db = new SmrMySqlDatabase();
		foreach($sectors as $sector) {
			// check if the sector is a part of the game
			$db->query('SELECT sector_id
						FROM sector
						WHERE game_id = ' . $player->getGameID() . '
							AND  sector_id = ' . $db->escapeNumber($sector)
			);

			if (!$db->nextRecord()) {
				fputs($fp, 'PRIVMSG ' . $channel . ' :The sector ' . $sector . ' does not exist in current game.' . EOL);
				continue;
			}

			// check if the given sector is already part of the seed list
			$db->query('SELECT sector_id
						FROM alliance_has_seedlist
						WHERE alliance_id = ' . $player->getAllianceID() . '
							AND game_id = ' . $player->getGameID() . '
							AND sector_id = ' . $db->escapeNumber($sector)
			);

			if ($db->nextRecord()) {
//				fputs($fp, 'PRIVMSG ' . $channel . ' :The sector ' . $sector . ' is already in the seedlist.' . EOL);
				continue;
			}
			
			// add sector to db
			$db->query('INSERT INTO alliance_has_seedlist
						(alliance_id, game_id, sector_id)
						VALUES (' . $player->getAllianceID() . ', ' . $player->getGameID() . ', ' . $db->escapeNumber($sector) . ')');

//			fputs($fp, 'PRIVMSG ' . $channel . ' :The sector ' . $sector . ' has been added.' . EOL);
		}
		fputs($fp, 'PRIVMSG ' . $channel . ' :The sectors have been added.' . EOL);
		return true;
	}

	return false;

}

function channel_msg_seedlist_del($fp, $rdata, $account, $player)
{

	if (preg_match('/^:(.*)!(.*)@(.*)\sPRIVMSG\s(.*)\s:!seedlist del (.*)\s$/i', $rdata, $msg)) {

		$nick = $msg[1];
		$user = $msg[2];
		$host = $msg[3];
		$channel = $msg[4];
		$sectors = explode(' ', $msg[5]);

		echo_r('[SEEDLIST_DEL] by ' . $nick . ' in ' . $channel);

		// check if $nick is leader
		if (!$player->isAllianceLeader(true)) {
			fputs($fp, 'PRIVMSG ' . $channel . ' :' . $nick . ', only the leader of the alliance manages the seedlist.' . EOL);
			return true;
		}

		foreach($sectors as $sector) {
			// see if the sector is numeric
			if (!is_numeric($sector)) {
				fputs($fp, 'PRIVMSG ' . $channel . ' :The sectors all need to be numeric. Example: !seedlist del 1537' . EOL);
				return true;
			}
		}

		// add sectors to db
		$db = new SmrMySqlDatabase();
		$db->query('DELETE FROM alliance_has_seedlist
					WHERE alliance_id = ' . $player->getAllianceID() . '
						AND game_id = ' . $player->getGameID() . '
						AND sector_id IN ' . $db->escapeArray($sectors)
		);

		fputs($fp, 'PRIVMSG ' . $channel . ' :The sectors have been deleted.' . EOL);
		return true;

	}

	return false;

}

?>