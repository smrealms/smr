<?
require_once(get_file_loc('smr_history_db.inc'));

// additional db objects
$db2 = new SMR_DB();

$smr_db_sql = array();
$history_db_sql = array();

$action = $_REQUEST['action'];
if ($_REQUEST['save'] == 'Yes') $save = TRUE; else $save = FALSE;

if ($action == 'Yes') {

	// get game id
    $game_id = $var['game_id'];

	if ($save) {

		$db->query('SELECT * FROM alliance WHERE game_id = '.$game_id);
		
		while ($db->next_record()) {
		
			$id = $db->f('alliance_id');
			//we need info for forces
			//populate alliance list
			$db2->query('SELECT * FROM player WHERE alliance_id = '.$id.' ' .
					'AND game_id = '.$game_id);	
			$list = '(';
			while ($db2->next_record()) $list .= $db2->f('account_id') . ',';
			$list .= '0)';
			$db2->query('SELECT sum(mines) as sum_m, sum(combat_drones) as cds, ' .
					'sum(scout_drones) as sds ' .
					'FROM sector_has_forces ' .
					'WHERE owner_id IN '.$list.' AND game_id = '.$game_id);
			if ($db2->next_record()) {
				
				$mines = $db2->f('sum_m');
				$cds = $db2->f('cds');
				$sds = $db2->f('sds');
				if (!is_numeric($mines)) $mines = 0;
				if (!is_numeric($cds)) $cds = 0;
				if (!is_numeric($sds)) $sds = 0;
		
			} else {
		
				$mines = 0;
				$cds = 0;
				$sds = 0;
		
			}
			
			// get info we want
			$name = $db->f('alliance_name');
			$leader = $db->f('leader_id');
			$kills = $db->f('alliance_kills');
			$deaths = $db->f('alliance_deaths');
			// insert into history db
			$history_db_sql[] = 'INSERT INTO alliance (game_id, alliance_id, leader_id, kills, deaths, alliance_name, mines, cds, sds) ' .
								'VALUES ('.$game_id.', '.$id.', '.$leader.', '.$kills.', '.$deaths.', ' . $db->escape_string($name,FALSE) . ', '.$mines.', '.$cds.', '.$sds.')';
								
		}

	}

	// these table is nothing worth without the players
	//$smr_db_sql[] = 'DELETE FROM account_has_logs WHERE game_id = '.$game_id;

	$smr_db_sql[] = 'DELETE FROM alliance WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM alliance_bank_transactions WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM alliance_has_roles WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM alliance_thread WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM alliance_thread_topic WHERE game_id = '.$game_id;
	
	if ($save) {
		
		$db->query('SELECT * FROM alliance_vs_alliance WHERE game_id = '.$game_id);
		while ($db->next_record()) {
			
			$alliance_1 = $db->f('alliance_id_1');
			$alliance_2 = $db->f('alliance_id_2');
			$kills = $db->f('kills');
			$history_db_sql[] = 'INSERT INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) ' .
								'VALUES ('.$game_id.', '.$alliance_1.', '.$alliance_2.', '.$kills.')';
								
		}
		
	}
			
			
	$smr_db_sql[] = 'DELETE FROM alliance_vs_alliance WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM anon_bank WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM anon_bank_transactions WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM bar_tender WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM bar_wall WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM blackjack WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM galactic_post_applications WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM galactic_post_article WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM galactic_post_online WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM galactic_post_paper WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM galactic_post_paper_content WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM galactic_post_writer WHERE game_id = '.$game_id;

	if ($save) {

		$db->query('SELECT * FROM game WHERE game_id = '.$game_id);

		if ($db->next_record()) {

			// get info we want
			$end = $db->f('end_date');
			$start = $db->f('start_date');
			$name = $db->f('game_name');
			$speed = $db->f('game_speed');
			$type = $db->f('game_type');

			// insert into history db
			$history_db_sql[] = 'INSERT INTO game (game_id, end_date, start_date, game_name, speed, type) VALUES ' .
								'('.$game_id.', '.$db->escapeString($end).', '.$db->escapeString($start).', ' . $db->escape_string($name,FALSE) . ', '.$speed.', '.$db->escapeString($type).')';

		}

	}

	$smr_db_sql[] = 'DELETE FROM kills WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM location WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM mb_exceptions WHERE value LIKE '.$db->escapeString($game_id.'%');
	$smr_db_sql[] = 'DELETE FROM message WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM message_notify WHERE game_id = '.$game_id;

	if ($save) {

		$db->query('SELECT * FROM news WHERE game_id = '.$game_id.' AND type = \'regular\'');
		$id = 1;

		while ($db->next_record()) {

			// get info we want
			$time = $db->f('time');
			$msg = $db->f('news_message');

			// insert into history db
			$history_db_sql[] = 'INSERT INTO news (game_id, news_id, time, message) VALUES ('.$game_id.', '.$id.', '.$time.', ' . $db->escape_string($msg,FALSE) . ')';
			$id++;

		}
	}

	$smr_db_sql[] = 'DELETE FROM news WHERE game_id = '.$game_id;

	if ($save) {

		$db->query('SELECT * FROM planet WHERE game_id = '.$game_id);

		while ($db->next_record()) {

			// get info we want
			$sector = $db->f('sector_id');
			$owner = $db->f('owner_id');

			$db2->query('SELECT * FROM planet_has_construction WHERE game_id = '.$game_id.' AND sector_id = '.$sector.' AND construction_id = 1');
			if ($db2->next_record()) $gens = $db2->f('amount');
			else $gens = 0;

			$db2->query('SELECT * FROM planet_has_construction WHERE game_id = '.$game_id.' AND sector_id = '.$sector.' AND construction_id = 2');
			if ($db2->next_record()) $hangs = $db2->f('amount');
			else $hangs = 0;

			$db2->query('SELECT * FROM planet_has_construction WHERE game_id = '.$game_id.' AND sector_id = '.$sector.' AND construction_id = 3');
			if ($db2->next_record()) $turs = $db2->f('amount');
			else $turs = 0;

			// insert into history db
			$history_db_sql[] = 'INSERT INTO planet (game_id, sector_id, owner_id, generators, hangers, turrets) VALUES ' .
								'('.$game_id.', '.$sector.', '.$owner.', '.$gens.', '.$hangs.', '.$turs.')';

		}

	}

	$smr_db_sql[] = 'DELETE FROM planet WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM planet_attack WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM planet_build_construction WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM planet_has_cargo WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM planet_has_construction WHERE game_id = '.$game_id;

	if ($save) {

		$db->query('SELECT * FROM player WHERE game_id = '.$game_id);

		while ($db->next_record()) {

			// get info we want
			$acc_id = $db->f('account_id');
			$name = stripslashes($db->f('player_name'));
			$id = $db->f('player_id');
			$exp = $db->f('experience');
			$ship = $db->f('ship_type_id');
			$race = $db->f('race_id');
			$align = $db->f('alignment');
			$alli = $db->f('alliance_id');
			$kills = $db->f('kills');
			$deaths = $db->f('deaths');

			$db2->query('SELECT sum(amount) as bounty_am FROM bounty WHERE game_id = '.$game_id.' AND account_id = '.$acc_id.' AND claimer_id = 0');
			if ($db2->next_record()) {

				if (is_int($db2->f('bounty_am'))) $amount = $db2->f('bounty_am');
				else $amount = 0;

			} else $amount = 0;

			$db2->query('SELECT * FROM ship_has_name WHERE game_id = '.$game_id.' AND account_id = '.$acc_id);
			if ($db2->next_record()) $ship_name = $db2->f('ship_name');
			else $ship_name = 'None';

			// insert into history db
			$history_db_sql[] = 'INSERT INTO player (account_id, game_id, player_name, player_id, experience, ship, race, alignment, alliance_id, kills, deaths, bounty, ship_name) ' .
								'VALUES ('.$acc_id.', '.$game_id.', ' . $db->escape_string($name,FALSE) . ', '.$id.', '.$exp.', '.$ship.', '.$race.', '.$align.', '.$alli.', '.$kills.', '.$deaths.', '.$amount.', ' . $db->escape_string($ship_name,FALSE) . ')';

		}

	}

	$smr_db_sql[] = 'DELETE FROM player WHERE game_id = '.$game_id;

	if ($save) {

		$db->query('SELECT * FROM player_has_stats WHERE game_id = '.$game_id);

		while ($db->next_record()) {

			// get info we want
			$acc_id = $db->f('account_id');
			$planet_busts = $db->f('planet_busts');
			$planet_bust_levels = $db->f('planet_bust_levels');
			$port_raids = $db->f('port_raids');
			$port_raid_levels = $db->f('port_raid_levels');
			$sectors_explored = $db->f('sectors_explored');
			$deaths = $db->f('deaths');
			$kills = $db->f('kills');
			$goods_traded = $db->f('goods_traded');
			$experience_traded = $db->f('experience_traded');
			$bounties_claimed = $db->f('bounties_claimed');
			$bounty_amount_claimed = $db->f('bounty_amount_claimed');
			$military_claimed = $db->f('military_claimed');
			$bounty_amount_on = $db->f('bounty_amount_on');
			$player_damage = $db->f('player_damage');
			$port_damage = $db->f('port_damage');
			$planet_damage = $db->f('planet_damage');
			$turns_used = $db->f('turns_used');
			$kill_exp = $db->f('kill_exp');
			$traders_killed_exp = $db->f('traders_killed_exp');
			$blackjack_win = $db->f('blackjack_win');
			$blackjack_lose = $db->f('blackjack_lose');
			$lotto = $db->f('lotto');
			$drinks = $db->f('drinks');
			$trade_profit = $db->f('trade_profit');
			$trade_sales = $db->f('trade_sales');
			$mines = $db->f('mines');
			$cds = $db->f('combat_drones');
			$sds = $db->f('scout_drones');
			$money_gained = $db->f('money_gained');
			$killed_ships = $db->f('killed_ships');
			$died_ships = $db->f('died_ships');

			// insert into history db
			$history_db_sql[] = 'INSERT INTO player_has_stats (account_id,game_id,planet_busts,planet_bust_levels,port_raids,port_raid_levels,sectors_explored,deaths,kills,goods_traded,experience_traded,bounties_claimed,bounty_amount_claimed,military_claimed,bounty_amount_on,player_damage,port_damage,planet_damage,turns_used,kill_exp,traders_killed_exp,blackjack_win,blackjack_lose,lotto,drinks,trade_profit,trade_sales,mines,combat_drones,scout_drones,money_gained,killed_ships,died_ships) ' .
								'VALUES ('.$acc_id.','.$game_id.','.$planet_busts.','.$planet_bust_levels.','.$port_raids.','.$port_raid_levels.','.$sectors_explored.','.$deaths.','.$kills.','.$goods_traded.','.$experience_traded.','.$bounties_claimed.','.$bounty_amount_claimed.','.$military_claimed.','.$bounty_amount_on.','.$player_damage.','.$port_damage.','.$planet_damage.','.$turns_used.','.$kill_exp.','.$traders_killed_exp.','.$blackjack_win.','.$blackjack_lose.','.$lotto.','.$drinks.','.$trade_profit.','.$trade_sales.','.$mines.','.$cds.','.$sds.','.$money_gained.','.$killed_ships.','.$died_ships.')';

		}

	}

	$smr_db_sql[] = 'DELETE FROM player_has_stats WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM bounty WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM player_has_ticker WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM player_has_ticket WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM player_has_alliance_role WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM player_has_drinks WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM player_has_relation WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM player_has_unread_messages WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM player_is_president WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM player_plotted_course WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM player_read_thread WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM player_visited_port WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM player_visited_sector WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM player_votes_pact WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM player_votes_relation WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM plot_cache WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM port WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM port_attack_times WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM port_has_goods WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM race_has_relation WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM race_has_voting WHERE game_id = '.$game_id;

	if ($save) {

		$db->query('SELECT * FROM sector WHERE game_id = '.$game_id);

		while ($db->next_record()) {

			// get info we want
			$sector = $db->f('sector_id');
			$kills = $db->f('battles');
			$gal_id = $db->f('galaxy_id');

			$db2->query('SELECT sum(mines) as sum_mines, sum(combat_drones) as cds, sum(scout_drones) as sds FROM sector_has_forces ' .
						'WHERE sector_id = '.$sector.' AND game_id = '.$game_id.' GROUP BY sector_id');
			if ($db2->next_record()) {

				$mines = $db2->f('sum_mines');
				$cds = $db2->f('cds');
				$sds = $db2->f('sds');
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

	$smr_db_sql[] = 'DELETE FROM sector WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM sector_has_forces WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM ship_has_cargo WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM ship_has_hardware WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM ship_has_name WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM ship_has_illusion WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM ship_has_weapon WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM ship_is_cloaked WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM warp WHERE game_id = '.$game_id;
	$smr_db_sql[] = 'DELETE FROM game WHERE game_id = '.$game_id;

	// now do the sql stuff
	foreach($smr_db_sql as $sql) {

		$db->query($sql);

	}

	$db = new SMR_HISTORY_DB();
	foreach($history_db_sql as $sql) {

		$db->query($sql);

	}

	// don't know why exactly we have to do that,
	// but it seems that the db is used globally instead kept to each object
	$db = new SMR_DB();

}
$db = new SMR_DB();
//forward em
forward(create_container('skeleton.php', 'game_play.php'));

?>