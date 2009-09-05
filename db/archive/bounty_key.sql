ALTER TABLE `bounty`
  DROP PRIMARY KEY,
   ADD PRIMARY KEY(
     `account_id`,
     `game_id`,
     `bounty_id`);