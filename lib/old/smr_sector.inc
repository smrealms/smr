<?
//
//// include file with database class
////require_once(LIB . 'smr_db.inc');
//require_once(LIB . 'global/smr_db.inc');
//
//class SMR_SECTOR extends SMR_DB {
//
//	var $sector_id;
//	var $game_id;
//	var $account_id;
//	var $link_up;
//	var $link_down;
//	var $link_left;
//	var $link_right;
//	var $warp;
//	var $battles;
//	var $galaxy_id;
//	var $galaxy_name;
//	var $visited;
//	var $port_info;
//	var $exits;
//
//	function SMR_SECTOR($current_sector_id, $game_id, $account_id) {
//
//		$this->sector_id = $current_sector_id;
//		$this->game_id = $game_id;
//		$this->account_id = $account_id;
//
//		$this->query('SELECT * FROM sector, galaxy WHERE sector.galaxy_id = galaxy.galaxy_id AND ' .
//														'sector.game_id = '.$this->game_id.' AND ' .
//														'sector_id = '.$this->sector_id.' LIMIT 1');
//		$this->next_record();
//		$this->link_up		= $this->f('link_up');
//		$this->link_down	= $this->f('link_down');
//		$this->link_left	= $this->f('link_left');
//		$this->link_right	= $this->f('link_right');
//		$this->battles		= $this->f('battles');
//		$this->galaxy_id	= $this->f('galaxy_id');
//		$this->galaxy_name	= $this->f('galaxy_name');
//
//		if (empty($this->battles)) $this->battles = 0;
//
//		$this->query('SELECT * FROM warp WHERE game_id = '.$this->game_id.' AND ' .
//											  '(sector_id_1 = '.$this->sector_id.' OR ' .
//											   'sector_id_2 = '.$this->sector_id.')');
//		if ($this->next_record())
//			$this->warp = ($this->f('sector_id_1') == $this->sector_id) ? $this->f('sector_id_2') : $this->f('sector_id_1');
//
//		$this->exits = array();
//		if($this->link_up)
//			$this->exits[]=$this->link_up;
//		if($this->link_down)
//			$this->exits[]=$this->link_down;
//		if($this->link_left)
//			$this->exits[]=$this->link_left;
//		if($this->link_right)
//			$this->exits[]=$this->link_right;
//		if($this->warp)
//			$this->exits[]=$this->warp;
//			
//		// let forces expire (simply let all expire) (refresh first)
//		$db = new SMR_DB();
////		$db2 = new SMR_DB();
////		$time = time();
////		$db->query('SELECT * FROM force_refresh WHERE refresh_at <= '.$time);
////		while ($db->next_record()) {
////			
////			$total = $db->f('num_forces');
////			$days = ceil($total / 10);
////			if ($days > 5) $days = 5;
////			$ex_time = $db->f('refresh_at') + ($days * 86400);
////			$db2->query('UPDATE sector_has_forces SET expire_time = '.$ex_time.' WHERE game_id = '.$this->game_id.' AND sector_id = ' . $db->f('sector_id') . ' AND owner_id = ' . $db->f('owner_id'));
////			$db2->query('DELETE FROM force_refresh WHERE game_id = '.$this->game_id .
////					' AND sector_id = ' . $db->f('sector_id') . ' AND owner_id = ' . $db->f('owner_id'));
////						
////		}
//		$this->query('DELETE FROM sector_has_forces WHERE expire_time < ' . TIME);
//
//		$this->query('SELECT sector_id FROM player_visited_sector ' .
//							  'WHERE account_id = '.$this->account_id.' AND ' .
//							  		'game_id = '.$this->game_id.' AND ' .
//							  		'sector_id = '.$this->sector_id.' LIMIT 1');
//		if (!$this->next_record()) {
//			$this->visited		= 1;
//		}
//		else {
//			$this->visited = 0;
//		}
//
//
//		$this->query('SELECT port_info,visited FROM player_visited_port ' .
//							  'WHERE account_id = '.$this->account_id.' AND ' .
//							  		'game_id = '.$this->game_id.' AND ' .
//							  		'sector_id = '.$this->sector_id.' LIMIT 1');
//
//		if ($this->next_record()) {
//
//			$this->port_info	= unserialize($this->f('port_info'));
//			$this->port_visited = $this->f('visited');
//
//		} else
//			$this->port_visited = 0;
//	}
//
//	function update() {
//
//		$this->query('UPDATE sector SET battles = $this->battles ' .
//								   'WHERE game_id = '.$this->game_id.' AND ' .
//										 'sector_id = '.$this->sector_id);
//	}
//
//	function mark_visited() {
//
//		global $player;
//
//		$this->port_info = array();
//
//		$this->query('SELECT * FROM port_has_goods ' .
//					 'WHERE game_id = '.$this->game_id.' AND ' .
//					 	   'sector_id = '.$this->sector_id .
//					 ' ORDER BY good_id');
//		while ($this->next_record()) {
//
//			$good_id		= $this->f('good_id');
//			$transaction	= $this->f('transaction');
//
//			$this->port_info[$good_id] = $transaction;
//
//		}
//
//		$curr_time = time();
//		$port_info = $this->escape_string(serialize($this->port_info));
//		$this->query('SELECT * FROM port WHERE sector_id = '.$this->sector_id.' AND game_id = '.$this->game_id.' LIMIT 1');
//		if ($this->next_record()) {
//
//			//give them the port info
//			$this->query('REPLACE INTO player_visited_port ' .
//						'(account_id, game_id, sector_id, visited, port_info) ' .
//						'VALUES ('.$player->getAccountID().', '.$this->game_id.', '.$this->sector_id.', '.$curr_time.', '.$this->escapeString($port_info).')');
//
//		}
//
//		//now delete the entry from visited
//		$this->query('DELETE FROM player_visited_sector ' .
//					 'WHERE game_id = '.$player->getGameID().' AND sector_id = '.$player->getSectorID().' AND ' .
//					 'account_id = '.$player->getAccountID().' LIMIT 1');
//
//	}
//
//	function has_good($good_id, $transaction) {
//
//		$this->query('SELECT * FROM port NATURAL JOIN port_has_goods ' .
//					 'WHERE port.sector_id = '.$this->sector_id.' AND ' .
//						   'port.game_id = '.$this->game_id.' AND ' .
//						   'good_id = '.$good_id.' AND ' .
//						   'transaction_type = '.$this->escapeString($transaction));
//		return $this->nf();
//
//	}
//
//	function has_port()
//	{
//		require_once(get_file_loc('SmrPort.class.inc'));
//		$port =& SmrPort::getPort($this->game_id,$this->sector_id);
//		return $port->exists();
//	}
//
//	function has_forces() {
//
//		global $account;
//
//		// get our rank
//		$rank_id = $account->get_rank();
//
//		// iterate over all scout drones in sector
//		$this->query('SELECT * FROM sector_has_forces ' .
//					 'WHERE sector_id = '.$this->sector_id.' AND ' .
//						   'game_id = '.$this->game_id);
//		while ($this->next_record()) {
//
//			// we may skip player if this is a protected gal.
//			if ($this->is_protected_gal()) {
//
//				$curr_account = new SMR_ACCOUNT();
//				$curr_account->get_by_id($this->f('owner_id'));
//
//				// if one is vet and the other is newbie we skip it
//				if (different_level($rank_id, $curr_account->get_rank(), $account->veteran, $curr_account->veteran))
//					continue;
//
//			}
//
//			return true;
//
//		}
//
//		return false;
//
//	}
//
//	function has_planet() {
//
//		$this->query('SELECT sector_id FROM planet WHERE sector_id = '.$this->sector_id.' AND ' .
//												'game_id = '.$this->game_id.' LIMIT 1');
//		return $this->nf();
//
//	}
//
//	function has_weapon_shop() {
//
//		$this->query('SELECT sector_id FROM location NATURAL JOIN location_sells_weapons ' .
//					 'WHERE sector_id = '.$this->sector_id.' AND ' .
//						   'game_id = '.$this->game_id.' LIMIT 1');
//		return $this->nf();
//
//	}
//
//	function has_hq() {
//
//		$this->query('SELECT sector_id FROM location NATURAL JOIN location_is_hq ' .
//					 'WHERE sector_id = '.$this->sector_id.' AND ' .
//						   'game_id = '.$this->game_id.' LIMIT 1');
//		return $this->nf();
//
//	}
//
//	function has_ug() {
//		// Pointless doing a natural join on a table with one entry
//		$this->query('SELECT sector_id FROM location ' .
//					 'WHERE location_type_id=102 AND sector_id = '.$this->sector_id.' AND ' .
//						   'game_id = '.$this->game_id.' LIMIT 1');
//		return $this->nf();
//
//	}
//
//	function has_ship_shop() {
//
//		$this->query('SELECT sector_id FROM location NATURAL JOIN location_sells_ships ' .
//					 'WHERE sector_id = '.$this->sector_id.' AND ' .
//						   'game_id = '.$this->game_id.' LIMIT 1');
//		return $this->nf();
//
//	}
//
//	function has_fed_beacon() {
//		// Pointless doing a natural join on a table with one entry
//		$this->query('SELECT sector_id FROM location ' .
//					 'WHERE location_type_id=201 AND sector_id = '.$this->sector_id.' AND ' .
//						   'game_id = '.$this->game_id.' LIMIT 1');
//		return $this->nf();
//
//	}
//
//	function has_bar() {
//
//		$this->query('SELECT sector_id FROM location NATURAL JOIN location_is_bar ' .
//					 'WHERE sector_id = '.$this->sector_id.' AND ' .
//						   'game_id = '.$this->game_id.' LIMIT 1');
//		return $this->nf();
//
//	}
//
//	function has_hardware_shop() {
//
//		$this->query('SELECT sector_id FROM location NATURAL JOIN location_sells_hardware ' .
//					 'WHERE sector_id = '.$this->sector_id.' AND ' .
//						   'game_id = '.$this->game_id.' LIMIT 1');
//		return $this->nf();
//
//	}
//
//	function has_bank() {
//
//		$this->query('SELECT sector_id FROM location NATURAL JOIN location_is_bank ' .
//					 'WHERE sector_id = '.$this->sector_id.' AND ' .
//						   'game_id = '.$this->game_id.' LIMIT 1');
//		return $this->nf();
//
//	}
//
//	function has_location() {
//
//		$this->query('SELECT sector_id FROM location ' .
//					 'WHERE sector_id = '.$this->sector_id.' AND ' .
//						   'game_id = '.$this->game_id.' LIMIT 1');
//		return $this->nf();
//
//	}
//
//	function entering_sector() {
//
//		global $player, $account;
//		global $SCOUTMSG;
//
//		// get our rank
//		$rank_id = $account->get_rank();
//
//		// iterate over all scout drones in sector
//		$this->query('SELECT * FROM sector_has_forces ' .
//					 'WHERE sector_id = '.$this->sector_id.' AND ' .
//						   'game_id = '.$this->game_id.' AND ' .
//						   'owner_id != '.$this->account_id.' AND ' .
//						   'scout_drones > 0');
//		while ($this->next_record()) {
//
//			$owner =& SmrPlayer::getPlayer($this->f('owner_id'), '.$this->game_id.');
//			if ($owner->alliance_id == $player->getAllianceID() && $player->getAllianceID() != 0) continue;
//
//			// we may skip player if this is a protected gal.
//			if ($this->is_protected_gal()) {
//
//				$curr_account = new SMR_ACCOUNT();
//				$curr_account->get_by_id($this->f('owner_id'));
//
//				if (different_level($rank_id, $curr_account->get_rank(), $account->veteran, $curr_account->veteran))
//				continue;
//			}
//
//			// send scout messages to user
//			$message = 'Your forces have spotted ' . $player->getDisplayName() . ' entering sector #'.$this->sector_id;
//			$player->sendMessage($owner->account_id, $SCOUTMSG, $this->escape_string($message, false));
//			//insert into ticker
//			$db = new SMR_DB();
//			$time = time();
//			$db->query('SELECT * FROM player_has_ticker WHERE account_id = $owner->account_id AND game_id = $owner->game_id AND type = \'scout\'');
//			if ($db->next_record()) {
//
//				$db->query('SELECT * FROM player_has_ticker WHERE account_id = '.$player->getAccountID().' AND type = \'block\'');
//				if (!$db->next_record()) $db->query('UPDATE player_has_ticker SET recent = ' . $db->escape_string($message, false) . ', time = $time WHERE account_id = $owner->account_id AND game_id = $owner->game_id');
//
//			}
//
//		}
//
//	}
//
//	function leaving_sector() {
//
//		global $player, $account;
//		global $SCOUTMSG;
//
//		// get our rank
//		$rank_id = $account->get_rank();
//
//		// iterate over all scout drones in sector
//		$this->query('SELECT * FROM sector_has_forces ' .
//					 'WHERE sector_id = '.$this->sector_id.' AND ' .
//						   'game_id = '.$this->game_id.' AND ' .
//						   'owner_id != '.$this->account_id.' AND ' .
//						   'scout_drones > 0');
//		while ($this->next_record()) {
//
//			$owner =& SmrPlayer::getPlayer($this->f('owner_id'), $this->game_id);
//			if ($owner->alliance_id == $player->getAllianceID() && $player->getAllianceID() != 0) continue;
//
//			// we may skip player if this is a protected gal.
//			if ($this->is_protected_gal()) {
//
//				$curr_account = new SMR_ACCOUNT();
//				$curr_account->get_by_id($this->f('owner_id'));
//				if (different_level($rank_id, $curr_account->get_rank(), $account->veteran, $curr_account->veteran)) continue;
//
//			}
//
//			// send scout messages to user
//			$message = 'Your forces have spotted ' . $player->getDisplayName() . ' leaving sector #'.$this->sector_id;
//			$player->sendMessage($owner->account_id, $SCOUTMSG, $this->escape_string($message, false));
//			//insert into ticker
//			$db = new SMR_DB();
//			$time = time();
//			$db->query('SELECT * FROM player_has_ticker WHERE account_id = '.$owner->account_id.' AND game_id = '.$owner->game_id.' AND type = \'scout\'');
//			if ($db->next_record()) {
//
//				$db->query('SELECT * FROM player_has_ticker WHERE account_id = '.$player->getAccountID().' AND type = \'block\'');
//				if (!$db->next_record()) $db->query('UPDATE player_has_ticker SET recent = ' . $db->escape_string($message, false) . ', time = '.$time.' WHERE account_id = '.$owner->account_id.' AND game_id = '.$owner->game_id);
//
//			}
//
//		}
//
//	}
//
//	function died_here() {
//
//		global $player, $account;
//		global $SCOUTMSG;
//
//		// get our rank
//		$rank_id = $account->get_rank();
//
//		// iterate over all scout drones in sector
//		$this->query('SELECT * FROM sector_has_forces ' .
//					 'WHERE sector_id = '.$this->sector_id.' AND ' .
//						   'game_id = '.$this->game_id.' AND ' .
//						   'owner_id != '.$this->account_id.' AND ' .
//						   'scout_drones > 0');
//		while ($this->next_record()) {
//
//			$owner =& SmrPlayer::getPlayer($this->f('owner_id'), $this->game_id);
//			if ($owner->getAllianceID == $player->getAllianceID() && $player->getAllianceID() != 0) continue;
//
//			// we may skip player if this is a protected gal.
//			if ($this->is_protected_gal()) {
//
//				$curr_account = new SMR_ACCOUNT();
//				$curr_account->get_by_id($this->f('owner_id'));
//
//				// if one is vet and the other is newbie we skip it
//				if (different_level($rank_id, $curr_account->get_rank(), $account->veteran, $curr_account->veteran))
//					continue;
//
//			}
//
//			// send scout messages to user
//			$message = 'Your forces have spotted that ' . $player->getDisplayName() . ' has been <font color=red>DESTROYED</font> in sector #'.$this->sector_id;
//			$player->sendMessage($owner->getAccountID(), $SCOUTMSG, $this->escape_string($message, false));
//			//insert into ticker
//			$db = new SMR_DB();
//			$time = time();
//			$db->query('SELECT * FROM player_has_ticker WHERE account_id = $owner->account_id AND game_id = $owner->game_id AND type = \'scout\'');
//			if ($db->next_record()) {
//
//				$db->query('SELECT * FROM player_has_ticker WHERE account_id = '.$player->getAccountID().' AND type = \'block\'');
//				if (!$db->next_record()) $db->query('UPDATE player_has_ticker SET recent = ' . $db->escape_string($message, false) . ', time = $time WHERE account_id = $owner->account_id AND game_id = $owner->game_id');
//
//			}
//
//		}
//
//	}
//
//	function is_protected_gal() {
//
//		return $this->galaxy_id < 9;
//
//	}
//
//}
//
//?>