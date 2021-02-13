-- Convert boolean column from tinyint to TRUE/FALSE enum for consistency.
ALTER TABLE `alliance_thread_topic` MODIFY `alliance_only` enum('TRUE', 'FALSE') NOT NULL DEFAULT 'FALSE';

-- The type change unsets all values, so just set everything to FALSE
-- since treaties aren't even enabled at this time.
UPDATE `alliance_thread_topic` SET `alliance_only` = 'FALSE';
