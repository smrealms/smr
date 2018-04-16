-- Remove unused tables
DROP TABLE `account_sms_blacklist`;
DROP TABLE `account_sms_dlr`;
DROP TABLE `account_sms_log`;
DROP TABLE `account_sms_response`;

-- Remove `cell_phone` column from `account` table
ALTER TABLE `account` DROP COLUMN `cell_phone`;
