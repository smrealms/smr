-- Create a copy of `alliance_bank_transactions`
CREATE TABLE `alliance_bank_transactions2` LIKE `alliance_bank_transactions`;

-- Restructure copy so that it has an auto-increment column
ALTER TABLE `alliance_bank_transactions2`
  ENGINE = InnoDB,
  DROP PRIMARY KEY,
  MODIFY `transaction_id` int unsigned NOT NULL AUTO_INCREMENT,
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `alliance` (`game_id`, `alliance_id`);

-- Copy data into the new table
INSERT INTO `alliance_bank_transactions2` (
  `alliance_id`, `game_id`, `time`, `payee_id`, `reason`, `transaction`,
  `amount`, `exempt`, `request_exempt`
)
SELECT
  `alliance_id`, `game_id`, `time`, `payee_id`, `reason`, `transaction`,
  `amount`, `exempt`, `request_exempt`
FROM `alliance_bank_transactions`
ORDER BY
  `game_id`,
  `alliance_id`,
  `transaction_id`;

-- Replace the original table with the new table
DROP TABLE `alliance_bank_transactions`;
RENAME TABLE `alliance_bank_transactions2` TO `alliance_bank_transactions`;
