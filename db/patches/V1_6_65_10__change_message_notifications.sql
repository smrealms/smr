-- Change account.message_notifications default to null
ALTER TABLE `account` MODIFY `message_notifications` varchar(100) DEFAULT NULL;
-- Set any non-array values to null
UPDATE `account` SET `message_notifications` = NULL WHERE `message_notifications` NOT LIKE 'a%';
