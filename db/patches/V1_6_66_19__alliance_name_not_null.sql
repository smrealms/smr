-- Make alliance.alliance_name not null with no default
ALTER TABLE `alliance` MODIFY `alliance_name` varchar(36) NOT NULL;
