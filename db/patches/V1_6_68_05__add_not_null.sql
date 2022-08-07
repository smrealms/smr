-- Make columns NOT NULL that shouldn't be null
ALTER TABLE `alliance`
	MODIFY `alliance_account` int unsigned NOT NULL DEFAULT '0',
	MODIFY `leader_id` int unsigned NOT NULL DEFAULT '0',
	MODIFY `alliance_password` varchar(32) NOT NULL;

ALTER TABLE `hardware_type`
	MODIFY `hardware_name` varchar(32) NOT NULL,
	MODIFY `cost` int unsigned NOT NULL;

ALTER TABLE `location_type`
	MODIFY `location_name` varchar(55) NOT NULL,
	MODIFY `location_image` varchar(32) NOT NULL;

ALTER TABLE `port_has_goods`
	MODIFY `transaction_type` enum('Buy','Sell') NOT NULL,
	MODIFY `amount` int unsigned NOT NULL;

ALTER TABLE `level`
	MODIFY `level_name` varchar(32) NOT NULL,
	MODIFY `requirement` int unsigned NOT NULL;

ALTER TABLE `good`
	MODIFY `good_name` varchar(32) NOT NULL,
	MODIFY `base_price` int unsigned NOT NULL;

ALTER TABLE `weapon_type`
	MODIFY `weapon_name` varchar(32) NOT NULL,
	MODIFY `race_id` int unsigned NOT NULL,
	MODIFY `cost` int unsigned NOT NULL,
	MODIFY `shield_damage` int unsigned NOT NULL,
	MODIFY `armour_damage` int unsigned NOT NULL,
	MODIFY `accuracy` int unsigned NOT NULL,
	MODIFY `power_level` int unsigned NOT NULL,
	MODIFY `buyer_restriction` int unsigned NOT NULL;
