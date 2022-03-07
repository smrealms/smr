<?php declare(strict_types=1);

create_error('Deleting games is disabled!');

$db = Smr\Database::getInstance();

$smr_db_sql = [];
$history_db_sql = [];

$action = Smr\Request::get('action');
if (Smr\Request::get('save') == 'Yes') {
	$save = true;
} else {
	$save = false;
}

if ($action == 'Yes') {
	// get game id
	$var = Smr\Session::getInstance()->getCurrentVar();
	$game_id = $var['delete_game_id'];

	if ($save) {
		$dbResult = $db->read('SELECT * FROM alliance WHERE game_id = ' . $db->escapeNumber($game_id));

		foreach ($dbResult->records() as $dbRecord) {
			$id = $dbRecord->getInt('alliance_id');
			//we need info for forces
			//populate alliance list
			$dbResult2 = $db->read('SELECT * FROM player
						WHERE alliance_id = '.$db->escapeNumber($id) . '
							AND game_id = '.$db->escapeNumber($game_id));
			$list = [0];
			foreach ($dbResult2->records() as $dbRecord2) {
				$list[] = $dbRecord2->getInt('account_id');
			}
			$dbResult2 = $db->read('SELECT sum(mines) as sum_m, sum(combat_drones) as cds, sum(scout_drones) as sds
						FROM sector_has_forces
						WHERE owner_id IN ('.$db->escapeArray($list) . ') AND game_id = ' . $db->escapeNumber($game_id));
			if ($dbResult2->hasRecord()) {
				$dbRecord2 = $dbResult2->record();
				$mines = $dbRecord2->getInt('sum_m');
				$cds = $dbRecord2->getInt('cds');
				$sds = $dbRecord2->getInt('sds');
			} else {
				$mines = 0;
				$cds = 0;
				$sds = 0;
			}

			// get info we want
			$name = $dbRecord->getField('alliance_name');
			$leader = $dbRecord->getInt('leader_id');
			$kills = $dbRecord->getInt('alliance_kills');
			$deaths = $dbRecord->getInt('alliance_deaths');
			// insert into history db
			$history_db_sql[] = 'INSERT INTO alliance (game_id, alliance_id, leader_id, kills, deaths, alliance_name, mines, cds, sds) ' .
								'VALUES (' . $db->escapeNumber($game_id) . ', ' . $db->escapeNumber($id) . ', ' . $db->escapeNumber($leader) . ', ' . $db->escapeNumber($kills) . ', ' . $db->escapeNumber($deaths) . ', ' . $db->escapeString($name) . ', ' . $db->escapeNumber($mines) . ', ' . $db->escapeNumber($cds) . ', ' . $db->escapeNumber($sds) . ')';

		}

	}

	// these table is nothing worth without the players
	//$smr_db_sql[] = 'DELETE FROM account_has_logs WHERE game_id = '.$game_id;

	$smr_db_sql[] = 'UPDATE active_session SET game_id = 0 WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM alliance WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM alliance_bank_transactions WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM alliance_has_roles WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM alliance_thread WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM alliance_thread_topic WHERE game_id = ' . $db->escapeNumber($game_id);

	if ($save) {

		$dbResult = $db->read('SELECT * FROM alliance_vs_alliance WHERE game_id = ' . $db->escapeNumber($game_id));
		foreach ($dbResult->records() as $dbRecord) {

			$alliance_1 = $dbRecord->getInt('alliance_id_1');
			$alliance_2 = $dbRecord->getInt('alliance_id_2');
			$kills = $dbRecord->getInt('kills');
			$history_db_sql[] = 'INSERT INTO alliance_vs_alliance (game_id, alliance_id_1, alliance_id_2, kills) ' .
								'VALUES (' . $game_id . ', ' . $alliance_1 . ', ' . $alliance_2 . ', ' . $kills . ')';

		}

	}


	$smr_db_sql[] = 'DELETE FROM alliance_vs_alliance WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM anon_bank WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM anon_bank_transactions WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM bar_tender WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM blackjack WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM galactic_post_applications WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM galactic_post_article WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM galactic_post_paper WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM galactic_post_paper_content WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM galactic_post_writer WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM game_galaxy WHERE game_id = ' . $db->escapeNumber($game_id);

	if ($save) {
		$game = SmrGame::getGame($game_id);
		// insert into history db
		$history_db_sql[] = 'INSERT INTO game (game_id, end_date, start_date, game_name, speed, type) VALUES ' .
								'(' . $db->escapeNumber($game_id) . ', ' . $game->getEndTime() . ', ' . $game->getStartTime() . ', ' . $db->escapeString($game->getName()) . ', ' . $game->getGameSpeed() . ', ' . $db->escapeString($game->getGameType()) . ')';
	}

	$smr_db_sql[] = 'DELETE FROM location WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM mb_exceptions WHERE value LIKE ' . $db->escapeString($game_id . '%');
	$smr_db_sql[] = 'DELETE FROM message WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM message_notify WHERE game_id = ' . $db->escapeNumber($game_id);

	if ($save) {

		$dbResult = $db->read('SELECT * FROM news WHERE game_id = ' . $game_id . ' AND type = \'regular\'');
		$id = 1;

		foreach ($dbResult->records() as $dbRecord) {

			// get info we want
			$time = $dbRecord->getInt('time');
			$msg = $dbRecord->getField('news_message');

			// insert into history db
			$history_db_sql[] = 'INSERT INTO news (game_id, news_id, time, message) VALUES (' . $game_id . ', ' . $id . ', ' . $time . ', ' . $db->escapeString($msg) . ')';
			$id++;

		}
	}

	$smr_db_sql[] = 'DELETE FROM news WHERE game_id = ' . $db->escapeNumber($game_id);

	if ($save) {

		$dbResult = $db->read('SELECT * FROM planet WHERE game_id = ' . $db->escapeNumber($game_id));

		foreach ($dbResult->records() as $dbRecord) {

			// get info we want
			$sector = $dbRecord->getInt('sector_id');
			$owner = $dbRecord->getInt('owner_id');

			$dbResult2 = $db->read('SELECT * FROM planet_has_building WHERE game_id = ' . $game_id . ' AND sector_id = ' . $sector . ' AND construction_id = 1');
			if ($dbResult2->hasRecord()) {
				$gens = $dbResult2->record()->getInt('amount');
			} else {
				$gens = 0;
			}

			$dbResult2 = $db->read('SELECT * FROM planet_has_building WHERE game_id = ' . $game_id . ' AND sector_id = ' . $sector . ' AND construction_id = 2');
			if ($dbResult2->hasRecord()) {
				$hangs = $dbResult2->record()->getInt('amount');
			} else {
				$hangs = 0;
			}

			$dbResult2 = $db->read('SELECT * FROM planet_has_building WHERE game_id = ' . $game_id . ' AND sector_id = ' . $sector . ' AND construction_id = 3');
			if ($dbResult2->hasRecord()) {
				$turs = $dbResult2->record()->getInt('amount');
			} else {
				$turs = 0;
			}

			// insert into history db
			$history_db_sql[] = 'INSERT INTO planet (game_id, sector_id, owner_id, generators, hangers, turrets) VALUES ' .
								'(' . $game_id . ', ' . $sector . ', ' . $owner . ', ' . $gens . ', ' . $hangs . ', ' . $turs . ')';

		}

	}

	$smr_db_sql[] = 'DELETE FROM planet WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM planet_is_building WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM planet_has_cargo WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM planet_has_building WHERE game_id = ' . $db->escapeNumber($game_id);

	if ($save) {

		$dbResult = $db->read('SELECT * FROM player WHERE game_id = ' . $game_id);

		foreach ($dbResult->records() as $dbRecord) {

			// get info we want
			$acc_id = $dbRecord->getInt('account_id');
			$name = $dbRecord->getString('player_name');
			$id = $dbRecord->getInt('player_id');
			$exp = $dbRecord->getInt('experience');
			$ship = $dbRecord->getInt('ship_type_id');
			$race = $dbRecord->getInt('race_id');
			$align = $dbRecord->getInt('alignment');
			$alli = $dbRecord->getInt('alliance_id');
			$kills = $dbRecord->getInt('kills');
			$deaths = $dbRecord->getInt('deaths');

			$amount = 0;
			$smrCredits = 0;
			$dbResult2 = $db->read('SELECT sum(amount) as bounty_am, sum(smr_credits) as bounty_cred FROM bounty WHERE game_id = ' . $game_id . ' AND account_id = ' . $acc_id . ' AND claimer_id = 0');
			if ($dbResult2->hasRecord()) {
				$dbRecord2 = $dbResult2->record();
				$amount = $dbRecord2->getInt('bounty_am');
				$smrCredits = $dbRecord2->getInt('bounty_cred');
			}

			$dbResult2 = $db->read('SELECT * FROM ship_has_name WHERE game_id = ' . $game_id . ' AND account_id = ' . $acc_id);
			if ($dbResult2->hasRecord()) {
				$ship_name = $dbResult2->record()->getField('ship_name');
			} else {
				$ship_name = 'None';
			}

			// insert into history db
			$history_db_sql[] = 'INSERT INTO player (account_id, game_id, player_name, player_id, experience, ship, race, alignment, alliance_id, kills, deaths, bounty, bounty_cred, ship_name) ' .
								'VALUES (' . $acc_id . ', ' . $game_id . ', ' . $db->escapeString($name) . ', ' . $id . ', ' . $exp . ', ' . $ship . ', ' . $race . ', ' . $align . ', ' . $alli . ', ' . $kills . ', ' . $deaths . ', ' . $amount . ',' . $smrCredits . ', ' . $db->escapeString($ship_name) . ')';

		}

	}

	$smr_db_sql[] = 'DELETE FROM player WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM bounty WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM player_has_ticker WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM player_has_ticket WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM player_has_alliance_role WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM player_has_drinks WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM player_has_relation WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM player_has_unread_messages WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM player_plotted_course WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM player_read_thread WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM player_visited_port WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM player_visited_sector WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM player_votes_pact WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM player_votes_relation WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM port WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM port_has_goods WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM race_has_relation WHERE game_id = ' . $db->escapeNumber($game_id);
	$smr_db_sql[] = 'DELETE FROM race_has_voting WHERE game_id = ' . $db->escapeNumber($game_id);

	if ($save) {

		$dbResult = $db->read('SELECT * FROM sector WHERE game_id = ' . $game_id);

		foreach ($dbResult->records() as $dbRecord) {

			// get info we want
			$sector = $dbRecord->getInt('sector_id');
			$kills = $dbRecord->getInt('battles');
			$gal_id = $dbRecord->getInt('galaxy_id');

			$dbResult2 = $db->read('SELECT sum(mines) as sum_mines, sum(combat_drones) as cds, sum(scout_drones) as sds FROM sector_has_forces ' .
						'WHERE sector_id = ' . $sector . ' AND game_id = ' . $game_id . ' GROUP BY sector_id');
			if ($dbResult2->hasRecord()) {

				$dbRecord2 = $dbResult2->record();
				$mines = $dbRecord2->getInt('sum_mines');
				$cds = $dbRecord2->getInt('cds');
				$sds = $dbRecord2->getInt('sds');

			} else {

				$mines = 0;
				$cds = 0;
				$sds = 0;

			}

			// insert into history db
			$history_db_sql[] = 'INSERT INTO sector (game_id, sector_id, gal_id, mines, kills, combat, scouts) ' .
								'VALUES (' . $game_id . ',' . $sector . ',' . $gal_id . ',' . $mines . ',' . $kills . ',' . $cds . ',' . $sds . ')';

		}

	}

	$smr_db_sql[] = 'DELETE FROM sector WHERE game_id = ' . $game_id;
	$smr_db_sql[] = 'DELETE FROM sector_has_forces WHERE game_id = ' . $game_id;
	$smr_db_sql[] = 'DELETE FROM ship_has_cargo WHERE game_id = ' . $game_id;
	$smr_db_sql[] = 'DELETE FROM ship_has_hardware WHERE game_id = ' . $game_id;
	$smr_db_sql[] = 'DELETE FROM ship_has_name WHERE game_id = ' . $game_id;
	$smr_db_sql[] = 'DELETE FROM ship_has_illusion WHERE game_id = ' . $game_id;
	$smr_db_sql[] = 'DELETE FROM ship_has_weapon WHERE game_id = ' . $game_id;
	$smr_db_sql[] = 'DELETE FROM ship_is_cloaked WHERE game_id = ' . $game_id;
	$smr_db_sql[] = 'UPDATE game SET end_time=' . Smr\Epoch::time() . ' WHERE game_id = ' . $game_id . ' AND end_time > ' . Smr\Epoch::time(); // Do not delete game placeholder, just make sure game is finished
	$smr_db_sql[] = 'UPDATE active_session SET game_id = 0 WHERE game_id = ' . $game_id;

	// now do the sql stuff
	foreach ($smr_db_sql as $sql) {
		$db->write($sql);
	}

	// Note that the `smr_live_history` database does not currently exist,
	// but if we decided to enable this tool to archive SMR 1.6 games,
	// we could create it.
	$db->switchDatabases('smr_live_history');
	foreach ($history_db_sql as $sql) {
		$db->write($sql);
	}
	$db->switchDatabaseToLive();

	// don't know why exactly we have to do that,
	// but it seems that the db is used globally instead kept to each object
	$db = Smr\Database::getInstance();

}
$db = Smr\Database::getInstance();
Page::create('skeleton.php', 'admin/admin_tools.php')->go();
