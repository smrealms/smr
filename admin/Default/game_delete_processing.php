<?php

create_error('Deleting games is disabled');

require_once(get_file_loc('SmrHistoryMySqlDatabase.class.inc'));

// additional db objects
$db2 = new SmrMySqlDatabase();

$SmrMySqlDatabase_sql = array();
$history_db_sql = array();

$action = $_REQUEST['action'];
if ($_REQUEST['save'] == 'Yes') $save = TRUE; else $save = FALSE;

if ($action == 'Yes') {

	// get game id
    $game_id = $var['game_id'];

	if ($save) {

		$db->query('SELECT * FROM alliance WHERE game_id = '.$game_id);
		
		while ($db->nextRecord()) {
		
			$id = $db->getField('alliance_id');
			//we need info for forces
			//populate alliance list
			$db2->query('SELECT * FROM player WHERE alliance_id = '.$id.' ' .
					'AND game_id = '.$game_id);	
			$list = '(';
			while ($db2->nextRecord()) $list .= $db2->getField('account_id') . ',';
			$list .= '0)';
			$db2->query('SELECT sum(mines) as sum_m, sum(combat_drones) as cds, ' .
					'sum(scout_drones) as sds ' .
					'FROM sector_has_forces ' .
					'WHERE owner_id IN '.$list.' AND game_id = '.$game_id);
			if ($db2->nextRecord()) {
				
				$mines = $db2->getField('sum_m');
				$cds = $db2->getField('cds');
				$sds = $db2->getField('sds');
				if (!is_numeric($mines)) $mines = 0;
				if (!is_numeric($cds)) $cds = 0;
				if (!is_numeric($sds)) $sds = 0;
		
			} else {
		
				$mines = 0;
				$cds = 0;
				$sds = 0;
		
			}
			
			// get info we want
			$name = $db->getField('alliance_name');
			$leader = $db->getField('leader_id');
			$kills = $db->getField('alliance_kills');
			$deaths = $db->getField('alliance_deaths');
			// insert into history db
			$history_db_sql[] = 'INSERT INTO alliance (game_id, alliance_id, leader_id, kills, deaths, alliance_name, mines, cds, sds) ' .
								'VALUES ('.$game_id.', '.$id.', '.$leader.', '.$kills.', '.$deaths.', ' . $db->escape_string($name,FALSE) . ', '.$mines.', '.$cds.', '.$sds.')';
								
		}

	}

	// these table is nothing worth without the players
	//$SmrMySqlDatabase_sql[] = 'DELETE FROM account_has_logs WHERE game_id = '.$game_id;

	$SmrMySqlDatabase_sql[] = 'UPDATE active_session SET game_id = 0 WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM alliance WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM alliance_bank_transactions WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM alliance_has_roles WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM alliance_thread WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM alliance_thread_topic WHERE game_id = '.$game_id;
	
	if ($save) {
		
		$db->query('SELECT * FROM alliance_vs_alliance WHERE game_id = '.$game_id);
		while ($db->nextRecord()) {
			
			$alliance_1 = $db->getField('alliance_id_1');
			$alliance_2 = $db->getField('alliance_id_2');
			$kills = $db->getField('kills');
			$history_db_sql[] = 'INSERT INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) ' .
								'VALUES ('.$game_id.', '.$alliance_1.', '.$alliance_2.', '.$kills.')';
								
		}
		
	}
			
			
	$SmrMySqlDatabase_sql[] = 'DELETE FROM alliance_vs_alliance WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM anon_bank WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM anon_bank_transactions WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM bar_tender WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM bar_wall WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM blackjack WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM galactic_post_applications WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM galactic_post_article WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM galactic_post_online WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM galactic_post_paper WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM galactic_post_paper_content WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM galactic_post_writer WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM game_galaxy WHERE game_id = '.$game_id;

	if ($save) {

		$db->query('SELECT * FROM game WHERE game_id = '.$game_id);

		if ($db->nextRecord()) {

			// get info we want
			$end = $db->getField('end_date');
			$start = $db->getField('start_date');
			$name = $db->getField('game_name');
			$speed = $db->getField('game_speed');
			$type = $db->getField('game_type');

			// insert into history db
			$history_db_sql[] = 'INSERT INTO game (game_id, end_date, start_date, game_name, speed, type) VALUES ' .
								'('.$game_id.', '.$end.', '.$start.', ' . $db->escape_string($name,FALSE) . ', '.$speed.', '.$db->escapeString($type).')';

		}

	}

	$SmrMySqlDatabase_sql[] = 'DELETE FROM kills WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM location WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM mb_exceptions WHERE value LIKE '.$db->escapeString($game_id.'%');
	$SmrMySqlDatabase_sql[] = 'DELETE FROM message WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM message_notify WHERE game_id = '.$game_id;

	if ($save) {

		$db->query('SELECT * FROM news WHERE game_id = '.$game_id.' AND type = \'regular\'');
		$id = 1;

		while ($db->nextRecord()) {

			// get info we want
			$time = $db->getField('time');
			$msg = $db->getField('news_message');

			// insert into history db
			$history_db_sql[] = 'INSERT INTO news (game_id, news_id, time, message) VALUES ('.$game_id.', '.$id.', '.$time.', ' . $db->escape_string($msg,FALSE) . ')';
			$id++;

		}
	}

	$SmrMySqlDatabase_sql[] = 'DELETE FROM news WHERE game_id = '.$game_id;

	if ($save) {

		$db->query('SELECT * FROM planet WHERE game_id = '.$game_id);

		while ($db->nextRecord()) {

			// get info we want
			$sector = $db->getField('sector_id');
			$owner = $db->getField('owner_id');

			$db2->query('SELECT * FROM planet_has_building WHERE game_id = '.$game_id.' AND sector_id = '.$sector.' AND construction_id = 1');
			if ($db2->nextRecord()) $gens = $db2->getField('amount');
			else $gens = 0;

			$db2->query('SELECT * FROM planet_has_building WHERE game_id = '.$game_id.' AND sector_id = '.$sector.' AND construction_id = 2');
			if ($db2->nextRecord()) $hangs = $db2->getField('amount');
			else $hangs = 0;

			$db2->query('SELECT * FROM planet_has_building WHERE game_id = '.$game_id.' AND sector_id = '.$sector.' AND construction_id = 3');
			if ($db2->nextRecord()) $turs = $db2->getField('amount');
			else $turs = 0;

			// insert into history db
			$history_db_sql[] = 'INSERT INTO planet (game_id, sector_id, owner_id, generators, hangers, turrets) VALUES ' .
								'('.$game_id.', '.$sector.', '.$owner.', '.$gens.', '.$hangs.', '.$turs.')';

		}

	}

	$SmrMySqlDatabase_sql[] = 'DELETE FROM planet WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM planet_attack WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM planet_is_building WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM planet_has_cargo WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM planet_has_building WHERE game_id = '.$game_id;

	if ($save) {

		$db->query('SELECT * FROM player WHERE game_id = '.$game_id);

		while ($db->nextRecord()) {

			// get info we want
			$acc_id = $db->getField('account_id');
			$name = stripslashes($db->getField('player_name'));
			$id = $db->getField('player_id');
			$exp = $db->getField('experience');
			$ship = $db->getField('ship_type_id');
			$race = $db->getField('race_id');
			$align = $db->getField('alignment');
			$alli = $db->getField('alliance_id');
			$kills = $db->getField('kills');
			$deaths = $db->getField('deaths');

			$amount = 0;
			$smrCredits = 0;
			$db2->query('SELECT sum(amount) as bounty_am, sum(smr_credits) as bounty_cred FROM bounty WHERE game_id = '.$game_id.' AND account_id = '.$acc_id.' AND claimer_id = 0');
			if ($db2->nextRecord())
			{
				if (is_int($db2->getField('bounty_am'))) $amount = $db2->getField('bounty_am');
				if (is_int($db2->getField('bounty_cred'))) $smrCredits = $db2->getField('bounty_cred');

			}

			$db2->query('SELECT * FROM ship_has_name WHERE game_id = '.$game_id.' AND account_id = '.$acc_id);
			if ($db2->nextRecord()) $ship_name = $db2->getField('ship_name');
			else $ship_name = 'None';

			// insert into history db
			$history_db_sql[] = 'INSERT INTO player (account_id, game_id, player_name, player_id, experience, ship, race, alignment, alliance_id, kills, deaths, bounty, bounty_cred, ship_name) ' .
								'VALUES ('.$acc_id.', '.$game_id.', ' . $db->escape_string($name,FALSE) . ', '.$id.', '.$exp.', '.$ship.', '.$race.', '.$align.', '.$alli.', '.$kills.', '.$deaths.', '.$amount.','.$smrCredits.', ' . $db->escape_string($ship_name,FALSE) . ')';

		}

	}

	$SmrMySqlDatabase_sql[] = 'DELETE FROM player WHERE game_id = '.$game_id;

	if ($save) {

		$db->query('SELECT * FROM player_has_stats WHERE game_id = '.$game_id);

		while ($db->nextRecord()) {

			// get info we want
			$acc_id = $db->getField('account_id');
			$planet_busts = $db->getField('planet_busts');
			$planet_bust_levels = $db->getField('planet_bust_levels');
			$port_raids = $db->getField('port_raids');
			$port_raid_levels = $db->getField('port_raid_levels');
			$sectors_explored = $db->getField('sectors_explored');
			$deaths = $db->getField('deaths');
			$kills = $db->getField('kills');
			$goods_traded = $db->getField('goods_traded');
			$experience_traded = $db->getField('experience_traded');
			$bounties_claimed = $db->getField('bounties_claimed');
			$bounty_amount_claimed = $db->getField('bounty_amount_claimed');
			$military_claimed = $db->getField('military_claimed');
			$bounty_amount_on = $db->getField('bounty_amount_on');
			$player_damage = $db->getField('player_damage');
			$port_damage = $db->getField('port_damage');
			$planet_damage = $db->getField('planet_damage');
			$turns_used = $db->getField('turns_used');
			$kill_exp = $db->getField('kill_exp');
			$traders_killed_exp = $db->getField('traders_killed_exp');
			$blackjack_win = $db->getField('blackjack_win');
			$blackjack_lose = $db->getField('blackjack_lose');
			$lotto = $db->getField('lotto');
			$drinks = $db->getField('drinks');
			$trade_profit = $db->getField('trade_profit');
			$trade_sales = $db->getField('trade_sales');
			$mines = $db->getField('mines');
			$cds = $db->getField('combat_drones');
			$sds = $db->getField('scout_drones');
			$money_gained = $db->getField('money_gained');
			$killed_ships = $db->getField('killed_ships');
			$died_ships = $db->getField('died_ships');

			// insert into history db
			$history_db_sql[] = 'INSERT INTO player_has_stats (account_id,game_id,planet_busts,planet_bust_levels,port_raids,port_raid_levels,sectors_explored,deaths,kills,goods_traded,experience_traded,bounties_claimed,bounty_amount_claimed,military_claimed,bounty_amount_on,player_damage,port_damage,planet_damage,turns_used,kill_exp,traders_killed_exp,blackjack_win,blackjack_lose,lotto,drinks,trade_profit,trade_sales,mines,combat_drones,scout_drones,money_gained,killed_ships,died_ships) ' .
								'VALUES ('.$acc_id.','.$game_id.','.$planet_busts.','.$planet_bust_levels.','.$port_raids.','.$port_raid_levels.','.$sectors_explored.','.$deaths.','.$kills.','.$goods_traded.','.$experience_traded.','.$bounties_claimed.','.$bounty_amount_claimed.','.$military_claimed.','.$bounty_amount_on.','.$player_damage.','.$port_damage.','.$planet_damage.','.$turns_used.','.$kill_exp.','.$traders_killed_exp.','.$blackjack_win.','.$blackjack_lose.','.$lotto.','.$drinks.','.$trade_profit.','.$trade_sales.','.$mines.','.$cds.','.$sds.','.$money_gained.','.$killed_ships.','.$died_ships.')';

		}

	}

	$SmrMySqlDatabase_sql[] = 'DELETE FROM player_has_stats WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM bounty WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM player_has_ticker WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM player_has_ticket WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM player_has_alliance_role WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM player_has_drinks WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM player_has_relation WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM player_has_unread_messages WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM player_is_president WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM player_plotted_course WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM player_read_thread WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM player_visited_port WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM player_visited_sector WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM player_votes_pact WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM player_votes_relation WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM plot_cache WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM port WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM port_attack_times WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM port_has_goods WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM race_has_relation WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM race_has_voting WHERE game_id = '.$game_id;

	if ($save) {

		$db->query('SELECT * FROM sector WHERE game_id = '.$game_id);

		while ($db->nextRecord()) {

			// get info we want
			$sector = $db->getField('sector_id');
			$kills = $db->getField('battles');
			$gal_id = $db->getField('galaxy_id');

			$db2->query('SELECT sum(mines) as sum_mines, sum(combat_drones) as cds, sum(scout_drones) as sds FROM sector_has_forces ' .
						'WHERE sector_id = '.$sector.' AND game_id = '.$game_id.' GROUP BY sector_id');
			if ($db2->nextRecord()) {

				$mines = $db2->getField('sum_mines');
				$cds = $db2->getField('cds');
				$sds = $db2->getField('sds');
				if (!is_numeric($mines)) $mines = 0;
				if (!is_numeric($cds)) $cds = 0;
				if (!is_numeric($sds)) $sds = 0;

			} else {

				$mines = 0;
				$cds = 0;
				$sds = 0;

			}

			// insert into history db
			$history_db_sql[] = 'INSERT INTO sector (game_id, sector_id, gal_id, mines, kills, combat, scouts) ' .
								'VALUES ('.$game_id.','. $sector.','. $gal_id.','. $mines.','. $kills.','. $cds.','. $sds.')';

		}

	}

	$SmrMySqlDatabase_sql[] = 'DELETE FROM sector WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM sector_has_forces WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM ship_has_cargo WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM ship_has_hardware WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM ship_has_name WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM ship_has_illusion WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM ship_has_weapon WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM ship_is_cloaked WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'DELETE FROM warp WHERE game_id = '.$game_id;
	$SmrMySqlDatabase_sql[] = 'UPDATE game SET end_date='.TIME.' WHERE game_id = '.$game_id.' AND end_date > '.TIME; // Do not delete game placeholder, just make sure game is finished
	$SmrMySqlDatabase_sql[] = 'UPDATE active_session SET game_id = 0 WHERE game_id = '.$game_id;

	// now do the sql stuff
	foreach($SmrMySqlDatabase_sql as $sql) {

		$db->query($sql);

	}

	$db = new SmrHistoryMySqlDatabase();
	foreach($history_db_sql as $sql) {

		$db->query($sql);

	}

	// don't know why exactly we have to do that,
	// but it seems that the db is used globally instead kept to each object
	$db = new SmrMySqlDatabase();

}
$db = new SmrMySqlDatabase();
//forward em
forward(create_container('skeleton.php', 'game_play.php'));

?>