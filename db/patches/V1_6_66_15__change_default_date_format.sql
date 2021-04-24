-- Rename format columns to remove the outdated "short" descriptor
ALTER TABLE `account` RENAME COLUMN `date_short` TO `date_format`;
ALTER TABLE `account` RENAME COLUMN `time_short` TO `time_format`;

-- Change the default date format
ALTER TABLE `account` MODIFY `date_format` varchar(20) NOT NULL DEFAULT 'Y-m-d';
