-- Give every game player a stable, globally unique identity.  The old
-- per-game identifier remains the displayed player number.
ALTER TABLE `player` RENAME COLUMN `player_id` TO `player_number`;

ALTER TABLE `player`
	DROP PRIMARY KEY;

-- The smallint type should last the live server ~50 years.
ALTER TABLE `player`
	ADD COLUMN `player_id` smallint unsigned NOT NULL AUTO_INCREMENT FIRST,
	ADD PRIMARY KEY (`player_id`),
	ADD UNIQUE KEY `account_game` (`account_id`, `game_id`),
	ENGINE = InnoDB;

-- Numbers and names are visible player identities within a game. The global
-- player_id primary key remains the database identity.
ALTER TABLE `player`
	DROP KEY `player_id`,
	DROP KEY `player_name`,
	ADD UNIQUE KEY `game_player_number` (`game_id`, `player_number`),
	ADD UNIQUE KEY `game_player_name` (`game_id`, `player_name`);

-- Change all other player-specific tables to be keyed by player_id instead of
-- account_id. This makes game_id redundant, but we keep it for convenience.
-- First add the new column as NULL, then populate it, then convert it to
-- NOT NULL (this will fail for any invalid entries).

-- This applies to player_*, ship_*, and alliance_* messages.

ALTER TABLE player_attacks_planet
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE player_attacks_planet
	JOIN player USING (account_id, game_id)
	SET player_attacks_planet.player_id = player.player_id;
ALTER TABLE player_attacks_planet
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id, sector_id),
	ADD KEY game_id (game_id, sector_id),
	ENGINE = InnoDB;

ALTER TABLE player_attacks_port
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE player_attacks_port
	JOIN player USING (account_id, game_id)
	SET player_attacks_port.player_id = player.player_id;
ALTER TABLE player_attacks_port
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id, sector_id),
	ADD KEY game_id (game_id, sector_id),
	ENGINE = InnoDB;

ALTER TABLE `player_can_fed`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
UPDATE `player_can_fed`
	JOIN `player` USING (`account_id`, `game_id`)
	SET player_can_fed.player_id = player.player_id;
ALTER TABLE `player_can_fed`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`, `race_id`);

-- Older games can have an NHL role without an NHL player. The role has
-- no player reference to preserve, and must not block migration of those games.
ALTER TABLE `player_has_alliance_role`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
DELETE `player_has_alliance_role` FROM `player_has_alliance_role`
	LEFT JOIN `player` USING (`account_id`, `game_id`)
	WHERE player_has_alliance_role.account_id = 36 AND player.player_id IS NULL;
UPDATE `player_has_alliance_role`
	JOIN `player` USING (`account_id`, `game_id`)
	SET player_has_alliance_role.player_id = player.player_id;
ALTER TABLE `player_has_alliance_role`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`, `alliance_id`),
	ENGINE = InnoDB;

ALTER TABLE `player_has_drinks`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
UPDATE `player_has_drinks`
	JOIN `player` USING (`account_id`, `game_id`)
	SET player_has_drinks.player_id = player.player_id;
ALTER TABLE `player_has_drinks`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`, `drink_id`, `time`),
	ENGINE = InnoDB;

ALTER TABLE `player_has_mission`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
UPDATE `player_has_mission`
	JOIN `player` USING (`account_id`, `game_id`)
	SET player_has_mission.player_id = player.player_id;
ALTER TABLE `player_has_mission`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`, `mission_id`);

ALTER TABLE `player_has_notes`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `note_id`;
UPDATE `player_has_notes`
	JOIN `player` USING (`account_id`, `game_id`)
	SET player_has_notes.player_id = player.player_id;
ALTER TABLE `player_has_notes`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP KEY `game_id`,
	DROP COLUMN `account_id`,
	ADD KEY `player_id` (`player_id`),
	ENGINE = InnoDB;

ALTER TABLE `player_has_relation`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
UPDATE `player_has_relation`
	JOIN `player` USING (`account_id`, `game_id`)
	SET player_has_relation.player_id = player.player_id;
ALTER TABLE `player_has_relation`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`, `race_id`),
	ENGINE = InnoDB;

ALTER TABLE `player_has_ticker`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
UPDATE `player_has_ticker`
	JOIN `player` USING (`account_id`, `game_id`)
	SET player_has_ticker.player_id = player.player_id;
ALTER TABLE `player_has_ticker`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`, `type`),
	ENGINE = InnoDB;

ALTER TABLE player_has_ticket
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE player_has_ticket
	JOIN player USING (account_id, game_id)
	SET player_has_ticket.player_id = player.player_id;
ALTER TABLE player_has_ticket
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id, time),
	ADD KEY game_id (game_id, time),
	ENGINE = InnoDB;

ALTER TABLE player_has_unread_messages
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE player_has_unread_messages
	JOIN player USING (account_id, game_id)
	SET player_has_unread_messages.player_id = player.player_id;
ALTER TABLE player_has_unread_messages
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP KEY account_id,
	DROP account_id,
	ADD KEY player_id (player_id, message_type_id),
	ENGINE = InnoDB;

ALTER TABLE player_hof
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE player_hof
	JOIN player USING (account_id, game_id)
	SET player_hof.player_id = player.player_id;
ALTER TABLE player_hof
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP KEY type,
	DROP account_id,
	ADD PRIMARY KEY (player_id, type),
	ADD KEY type (type, game_id, player_id),
	ENGINE = InnoDB;

ALTER TABLE `player_plotted_course`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
UPDATE `player_plotted_course`
	JOIN `player` USING (`account_id`, `game_id`)
	SET player_plotted_course.player_id = player.player_id;
ALTER TABLE `player_plotted_course`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`),
	ENGINE = InnoDB;

ALTER TABLE player_read_thread
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE player_read_thread
	JOIN player USING (account_id, game_id)
	SET player_read_thread.player_id = player.player_id;
ALTER TABLE player_read_thread
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id, alliance_id, thread_id),
	ENGINE = InnoDB;

ALTER TABLE `player_saved_combat_logs`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
UPDATE `player_saved_combat_logs`
	JOIN `player` USING (`account_id`, `game_id`)
	SET player_saved_combat_logs.player_id = player.player_id;
ALTER TABLE `player_saved_combat_logs`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`, `log_id`);

ALTER TABLE `player_stored_sector`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
UPDATE `player_stored_sector`
	JOIN `player` USING (`account_id`, `game_id`)
	SET player_stored_sector.player_id = player.player_id;
ALTER TABLE `player_stored_sector`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`, `sector_id`);

ALTER TABLE player_visited_port
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE player_visited_port
	JOIN player USING (account_id, game_id)
	SET player_visited_port.player_id = player.player_id;
ALTER TABLE player_visited_port
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id, sector_id),
	ENGINE = InnoDB;

ALTER TABLE player_visited_sector
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE player_visited_sector
	JOIN player USING (account_id, game_id)
	SET player_visited_sector.player_id = player.player_id;
ALTER TABLE player_visited_sector
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id, sector_id),
	ENGINE = InnoDB;

ALTER TABLE player_votes_pact
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE player_votes_pact
	JOIN player USING (account_id, game_id)
	SET player_votes_pact.player_id = player.player_id;
ALTER TABLE player_votes_pact
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id, race_id_1, race_id_2),
	ADD KEY game_id (game_id, race_id_1, race_id_2),
	ENGINE = InnoDB;

ALTER TABLE player_votes_relation
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE player_votes_relation
	JOIN player USING (account_id, game_id)
	SET player_votes_relation.player_id = player.player_id;
ALTER TABLE player_votes_relation
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id),
	ENGINE = InnoDB;

ALTER TABLE `ship_has_cargo`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
UPDATE `ship_has_cargo`
	JOIN `player` USING (`account_id`, `game_id`)
	SET ship_has_cargo.player_id = player.player_id;
ALTER TABLE `ship_has_cargo`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`, `good_id`),
	ENGINE = InnoDB;

ALTER TABLE `ship_has_hardware`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
UPDATE `ship_has_hardware`
	JOIN `player` USING (`account_id`, `game_id`)
	SET ship_has_hardware.player_id = player.player_id;
ALTER TABLE `ship_has_hardware`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`, `hardware_type_id`),
	ENGINE = InnoDB;

ALTER TABLE `ship_has_illusion`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
UPDATE `ship_has_illusion`
	JOIN `player` USING (`account_id`, `game_id`)
	SET ship_has_illusion.player_id = player.player_id;
ALTER TABLE `ship_has_illusion`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`),
	ENGINE = InnoDB;

ALTER TABLE `ship_has_name`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
UPDATE `ship_has_name`
	JOIN `player` USING (`account_id`, `game_id`)
	SET ship_has_name.player_id = player.player_id;
ALTER TABLE `ship_has_name`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`),
	ENGINE = InnoDB;

ALTER TABLE `ship_has_weapon`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
UPDATE `ship_has_weapon`
	JOIN `player` USING (`account_id`, `game_id`)
	SET ship_has_weapon.player_id = player.player_id;
ALTER TABLE `ship_has_weapon`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`, `order_id`),
	ENGINE = InnoDB;

ALTER TABLE `ship_is_cloaked`
	ADD COLUMN `player_id` smallint unsigned NULL AFTER `game_id`;
UPDATE `ship_is_cloaked`
	JOIN `player` USING (`account_id`, `game_id`)
	SET ship_is_cloaked.player_id = player.player_id;
ALTER TABLE `ship_is_cloaked`
	MODIFY `player_id` smallint unsigned NOT NULL,
	DROP PRIMARY KEY,
	DROP COLUMN `account_id`,
	ADD PRIMARY KEY (`player_id`),
	ENGINE = InnoDB;

ALTER TABLE sector_message
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE sector_message
	JOIN player USING (account_id, game_id)
	SET sector_message.player_id = player.player_id;
ALTER TABLE sector_message
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id),
	ENGINE = InnoDB;

-- Older games can have a Game Admins or NHA leader without a matching player.
-- These legacy alliances have no leader to preserve, so we have to remove the
-- leader_id, effectively disbanding these alliances.
UPDATE alliance
	LEFT JOIN player
		ON player.account_id = alliance.leader_id
		AND player.game_id = alliance.game_id
	SET alliance.leader_id = 0
		WHERE alliance.leader_id IN (1, 36)
		AND player.player_id IS NULL;
ALTER TABLE alliance
	ADD leader_player_id SMALLINT UNSIGNED NULL AFTER leader_id,
	ADD flagship_player_id SMALLINT UNSIGNED NULL AFTER flagship_id;
UPDATE alliance
	LEFT JOIN player leader
		ON leader.account_id = alliance.leader_id
		AND leader.game_id = alliance.game_id
	LEFT JOIN player flagship
		ON flagship.account_id = alliance.flagship_id
		AND flagship.game_id = alliance.game_id
	SET alliance.leader_player_id = IF(alliance.leader_id = 0, 0, leader.player_id),
		alliance.flagship_player_id = IF(alliance.flagship_id = 0, 0, flagship.player_id);
ALTER TABLE alliance
	MODIFY leader_player_id SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	MODIFY flagship_player_id SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	DROP leader_id,
	DROP flagship_id;

ALTER TABLE alliance_bank_transactions
	ADD player_id SMALLINT UNSIGNED NULL AFTER payee_id;
UPDATE alliance_bank_transactions
	JOIN player
		ON player.account_id = alliance_bank_transactions.payee_id
		AND player.game_id = alliance_bank_transactions.game_id
	SET alliance_bank_transactions.player_id = player.player_id;
ALTER TABLE alliance_bank_transactions
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP payee_id,
	ENGINE = InnoDB;

ALTER TABLE alliance_has_op_response
	ADD player_id SMALLINT UNSIGNED NULL AFTER alliance_id;
UPDATE alliance_has_op_response
	JOIN player USING (account_id, game_id)
	SET alliance_has_op_response.player_id = player.player_id;
ALTER TABLE alliance_has_op_response
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (alliance_id, player_id),
	ENGINE = InnoDB;

ALTER TABLE alliance_invites_player
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id,
	ADD invited_by_player_id SMALLINT UNSIGNED NULL AFTER invited_by_id;
UPDATE alliance_invites_player
	JOIN player receiver USING (account_id, game_id)
	JOIN player sender
		ON sender.account_id = alliance_invites_player.invited_by_id
		AND sender.game_id = alliance_invites_player.game_id
	SET alliance_invites_player.player_id = receiver.player_id,
		alliance_invites_player.invited_by_player_id = sender.player_id;
ALTER TABLE alliance_invites_player
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	MODIFY invited_by_player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	DROP invited_by_id,
	ADD PRIMARY KEY (player_id, alliance_id),
	ENGINE = InnoDB;

-- Weighted random state is maintained independently for each game player.
ALTER TABLE weighted_random
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE weighted_random
	JOIN player USING (account_id, game_id)
	SET weighted_random.player_id = player.player_id;
ALTER TABLE weighted_random
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id, type, type_id),
	ENGINE = InnoDB;

ALTER TABLE draft_leaders
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE draft_leaders
	JOIN player USING (account_id, game_id)
	SET draft_leaders.player_id = player.player_id;
ALTER TABLE draft_leaders
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id),
	ADD KEY game_id (game_id),
	ENGINE = InnoDB;

ALTER TABLE draft_history
	ADD leader_player_id SMALLINT UNSIGNED NULL AFTER game_id,
	ADD picked_player_id SMALLINT UNSIGNED NULL AFTER leader_player_id;
UPDATE draft_history
	JOIN player leader
		ON leader.account_id = draft_history.leader_account_id
		AND leader.game_id = draft_history.game_id
	JOIN player picked
		ON picked.account_id = draft_history.picked_account_id
		AND picked.game_id = draft_history.game_id
	SET draft_history.leader_player_id = leader.player_id,
		draft_history.picked_player_id = picked.player_id;
ALTER TABLE draft_history
	MODIFY leader_player_id SMALLINT UNSIGNED NOT NULL,
	MODIFY picked_player_id SMALLINT UNSIGNED NOT NULL,
	DROP leader_account_id,
	DROP picked_account_id,
	ENGINE = InnoDB;

ALTER TABLE galactic_post_writer
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE galactic_post_writer
	JOIN player USING (account_id, game_id)
	SET galactic_post_writer.player_id = player.player_id;
ALTER TABLE galactic_post_writer
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id),
	ENGINE = InnoDB;

ALTER TABLE galactic_post_applications
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE galactic_post_applications
	JOIN player USING (account_id, game_id)
	SET galactic_post_applications.player_id = player.player_id;
ALTER TABLE galactic_post_applications
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id),
	ENGINE = InnoDB;

ALTER TABLE galactic_post_article
	ADD writer_player_id SMALLINT UNSIGNED NULL AFTER writer_id;
UPDATE galactic_post_article
	JOIN player
		ON player.account_id = galactic_post_article.writer_id
		AND player.game_id = galactic_post_article.game_id
	SET galactic_post_article.writer_player_id = player.player_id;
ALTER TABLE galactic_post_article
	MODIFY writer_player_id SMALLINT UNSIGNED NOT NULL,
	DROP writer_id,
	ENGINE = InnoDB;

ALTER TABLE npc_players
	ADD player_id SMALLINT UNSIGNED NULL FIRST;
UPDATE npc_players
	JOIN player USING (account_id, game_id)
	SET npc_players.player_id = player.player_id;
ALTER TABLE npc_players
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id),
	ENGINE = InnoDB;

-- bounty has a composite AUTO_INCREMENT primary key, so it remains
-- MyISAM for now.
ALTER TABLE bounty
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id,
	ADD claimer_player_id SMALLINT UNSIGNED NULL AFTER claimer_id;
UPDATE bounty
	JOIN player target USING (account_id, game_id)
	LEFT JOIN player claimer
		ON claimer.account_id = bounty.claimer_id
		AND claimer.game_id = bounty.game_id
	SET bounty.player_id = target.player_id,
		bounty.claimer_player_id = IF(bounty.claimer_id = 0, 0, claimer.player_id);
ALTER TABLE bounty
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	MODIFY claimer_player_id SMALLINT UNSIGNED NOT NULL DEFAULT 0,
	DROP PRIMARY KEY,
	DROP KEY account_id,
	DROP account_id,
	DROP claimer_id,
	ADD PRIMARY KEY (player_id, bounty_id);

ALTER TABLE message_blacklist
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id,
	ADD blacklisted_player_id SMALLINT UNSIGNED NULL AFTER blacklisted_id;
UPDATE message_blacklist
	JOIN player owner USING (account_id, game_id)
	JOIN player blocked
		ON blocked.account_id = message_blacklist.blacklisted_id
		AND blocked.game_id = message_blacklist.game_id
	SET message_blacklist.player_id = owner.player_id,
		message_blacklist.blacklisted_player_id = blocked.player_id;
ALTER TABLE message_blacklist
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	MODIFY blacklisted_player_id SMALLINT UNSIGNED NOT NULL,
	DROP KEY game_id,
	DROP account_id,
	DROP blacklisted_id,
	ADD KEY player_id (player_id, blacklisted_player_id),
	ENGINE = InnoDB;

-- Messages belong to a receiving game player. Sender IDs may be system actors,
-- so retain only reserved actor values when they have no player record.
ALTER TABLE message
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id,
	ADD sender_player_id SMALLINT UNSIGNED NULL AFTER sender_id;
UPDATE message
	JOIN player receiver USING (account_id, game_id)
	LEFT JOIN player sender
		ON sender.account_id = message.sender_id
		AND sender.game_id = message.game_id
	SET message.player_id = receiver.player_id,
		message.sender_player_id = IF(message.sender_id >= 65500, message.sender_id, sender.player_id);
ALTER TABLE message
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	MODIFY sender_player_id SMALLINT UNSIGNED NOT NULL,
	DROP KEY account_id,
	DROP account_id,
	DROP sender_id,
	ADD KEY player_id (player_id, message_type_id, receiver_delete),
	ADD KEY sender_player_id (sender_player_id, message_type_id, sender_delete);

-- message_notify has a composite AUTO_INCREMENT primary key, so it remains
-- MyISAM for now.
ALTER TABLE message_notify
	ADD from_player_id SMALLINT UNSIGNED NULL AFTER game_id,
	ADD to_player_id SMALLINT UNSIGNED NULL AFTER from_id;
UPDATE message_notify
	LEFT JOIN player sender
		ON sender.account_id = message_notify.from_id
		AND sender.game_id = message_notify.game_id
	JOIN player receiver
		ON receiver.account_id = message_notify.to_id
		AND receiver.game_id = message_notify.game_id
	SET message_notify.from_player_id = IF(message_notify.from_id >= 65500, message_notify.from_id, sender.player_id),
		message_notify.to_player_id = receiver.player_id;
ALTER TABLE message_notify
	MODIFY from_player_id SMALLINT UNSIGNED NOT NULL,
	MODIFY to_player_id SMALLINT UNSIGNED NOT NULL,
	DROP from_id,
	DROP to_id;

-- alliance_thread has a composite AUTO_INCREMENT primary key, so it remains
-- MyISAM for now.
-- Remove alliance_thread rows for the NHA from older games missing an NHL player.
DELETE alliance_thread FROM alliance_thread
	LEFT JOIN player
		ON player.account_id = 36
		AND player.game_id = alliance_thread.game_id
	WHERE player.player_id IS NULL;
ALTER TABLE alliance_thread
	ADD player_id SMALLINT UNSIGNED NULL AFTER sender_id;
UPDATE alliance_thread
	LEFT JOIN player sender
		ON sender.account_id = alliance_thread.sender_id
		AND sender.game_id = alliance_thread.game_id
	SET alliance_thread.player_id = IF(alliance_thread.sender_id >= 65500, alliance_thread.sender_id, sender.player_id);
ALTER TABLE alliance_thread
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP sender_id;

ALTER TABLE anon_bank
	ADD owner_player_id SMALLINT UNSIGNED NULL AFTER owner_id;
UPDATE anon_bank
	JOIN player
		ON player.account_id = anon_bank.owner_id
		AND player.game_id = anon_bank.game_id
	SET anon_bank.owner_player_id = player.player_id;
ALTER TABLE anon_bank
	MODIFY owner_player_id SMALLINT UNSIGNED NOT NULL,
	DROP owner_id,
	ADD KEY owner_player_id (owner_player_id);

ALTER TABLE anon_bank_transactions
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE anon_bank_transactions
	JOIN player USING (account_id, game_id)
	SET anon_bank_transactions.player_id = player.player_id;
ALTER TABLE anon_bank_transactions
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP account_id,
	ADD PRIMARY KEY (player_id, anon_id, transaction_id),
	ENGINE = InnoDB;

ALTER TABLE news
	ADD killer_player_id SMALLINT UNSIGNED NULL AFTER killer_id,
	ADD dead_player_id SMALLINT UNSIGNED NULL AFTER dead_id;
UPDATE news
	LEFT JOIN player killer
		ON killer.account_id = news.killer_id
		AND killer.game_id = news.game_id
	LEFT JOIN player dead
		ON dead.account_id = news.dead_id
		AND dead.game_id = news.game_id
	SET news.killer_player_id = IF(news.killer_id >= 65500, news.killer_id, killer.player_id),
		news.dead_player_id = IF(news.dead_id >= 65500, news.dead_id, dead.player_id);
ALTER TABLE news
	DROP killer_id,
	DROP dead_id;

ALTER TABLE sector_has_forces
	ADD owner_player_id SMALLINT UNSIGNED NULL AFTER owner_id,
	ADD refresher_player_id SMALLINT UNSIGNED NULL AFTER refresher;
UPDATE sector_has_forces
	JOIN player owner
		ON owner.account_id = sector_has_forces.owner_id
		AND owner.game_id = sector_has_forces.game_id
	LEFT JOIN player refresher
		ON refresher.account_id = sector_has_forces.refresher
		AND refresher.game_id = sector_has_forces.game_id
	SET sector_has_forces.owner_player_id = owner.player_id,
		sector_has_forces.refresher_player_id = IF(sector_has_forces.refresher = 0, 0, refresher.player_id);
ALTER TABLE sector_has_forces
	MODIFY owner_player_id SMALLINT UNSIGNED NOT NULL,
	MODIFY refresher_player_id SMALLINT UNSIGNED NOT NULL DEFAULT 0,
	DROP PRIMARY KEY,
	DROP KEY refresher,
	DROP owner_id,
	DROP refresher,
	ADD PRIMARY KEY (sector_id, owner_player_id),
	ADD KEY game_id (game_id, sector_id),
	ADD KEY refresher_player_id (refresher_player_id),
	ENGINE = InnoDB;

-- locks_queue has a composite AUTO_INCREMENT primary key, so it remains
-- MyISAM for now.
ALTER TABLE locks_queue
	ADD player_id SMALLINT UNSIGNED NULL AFTER game_id;
UPDATE locks_queue
	JOIN player USING (account_id, game_id)
	SET locks_queue.player_id = player.player_id;
ALTER TABLE locks_queue
	MODIFY player_id SMALLINT UNSIGNED NOT NULL,
	DROP KEY account_id,
	DROP account_id,
	ADD KEY player_id (player_id);

ALTER TABLE planet
	ADD owner_player_id SMALLINT UNSIGNED NULL AFTER owner_id;
UPDATE planet
	LEFT JOIN player
		ON player.account_id = planet.owner_id
		AND player.game_id = planet.game_id
	SET planet.owner_player_id = IF(planet.owner_id = 0, 0, player.player_id);
ALTER TABLE planet
	MODIFY owner_player_id SMALLINT UNSIGNED NOT NULL DEFAULT 0,
	DROP owner_id,
	ADD KEY owner_player_id (owner_player_id),
	ENGINE = InnoDB;

-- planet_is_building has a composite AUTO_INCREMENT primary key, so it
-- remains MyISAM for now.
ALTER TABLE planet_is_building
	ADD constructor_player_id SMALLINT UNSIGNED NULL AFTER constructor_id;
UPDATE planet_is_building
	JOIN player
		ON player.account_id = planet_is_building.constructor_id
		AND player.game_id = planet_is_building.game_id
	SET planet_is_building.constructor_player_id = player.player_id;
ALTER TABLE planet_is_building
	MODIFY constructor_player_id SMALLINT UNSIGNED NOT NULL,
	DROP constructor_id;

-- combat_logs has a composite AUTO_INCREMENT primary key, so it remains
-- MyISAM for now.
ALTER TABLE combat_logs
	ADD attacker_player_id SMALLINT UNSIGNED NULL AFTER attacker_id,
	ADD defender_player_id SMALLINT UNSIGNED NULL AFTER defender_id;
UPDATE combat_logs
	LEFT JOIN player attacker
		ON attacker.account_id = combat_logs.attacker_id
		AND attacker.game_id = combat_logs.game_id
	LEFT JOIN player defender
		ON defender.account_id = combat_logs.defender_id
		AND defender.game_id = combat_logs.game_id
	SET combat_logs.attacker_player_id = IF(combat_logs.attacker_id >= 65500, combat_logs.attacker_id, attacker.player_id),
		combat_logs.defender_player_id = IF(combat_logs.defender_id >= 65500, combat_logs.defender_id, defender.player_id);
ALTER TABLE combat_logs
	MODIFY attacker_player_id SMALLINT UNSIGNED NOT NULL,
	MODIFY defender_player_id SMALLINT UNSIGNED NOT NULL,
	DROP PRIMARY KEY,
	DROP attacker_id,
	DROP defender_id,
	ADD PRIMARY KEY (
		log_id,
		game_id,
		type,
		sector_id,
		timestamp,
		attacker_player_id,
		attacker_alliance_id,
		defender_player_id,
		defender_alliance_id
	);

-- Chess games created before chess became game-specific have game_id = 0
-- and cannot be propagated into the player ID framework.
DELETE chess_game_moves FROM chess_game_moves
	JOIN chess_game USING (chess_game_id)
	WHERE chess_game.game_id = 0;
DELETE FROM chess_game
	WHERE game_id = 0;
ALTER TABLE chess_game
	ADD black_player_id SMALLINT UNSIGNED NULL AFTER black_id,
	ADD white_player_id SMALLINT UNSIGNED NULL AFTER white_id,
	ADD winner_player_id SMALLINT UNSIGNED NULL AFTER winner_id;
UPDATE chess_game
	JOIN player black_player
		ON black_player.account_id = chess_game.black_id
		AND black_player.game_id = chess_game.game_id
	JOIN player white_player
		ON white_player.account_id = chess_game.white_id
		AND white_player.game_id = chess_game.game_id
	LEFT JOIN player winner_player
		ON winner_player.account_id = chess_game.winner_id
		AND winner_player.game_id = chess_game.game_id
	SET chess_game.black_player_id = black_player.player_id,
		chess_game.white_player_id = white_player.player_id,
		chess_game.winner_player_id = IF(chess_game.winner_id = 0, 0, winner_player.player_id);
ALTER TABLE chess_game
	MODIFY black_player_id SMALLINT UNSIGNED NOT NULL,
	MODIFY white_player_id SMALLINT UNSIGNED NOT NULL,
	MODIFY winner_player_id SMALLINT UNSIGNED NOT NULL DEFAULT 0,
	DROP black_id,
	DROP white_id,
	DROP winner_id,
	ENGINE = InnoDB;
