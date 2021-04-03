-- Add null default for active_session.ajax_returns
ALTER TABLE `active_session` MODIFY `ajax_returns` mediumblob DEFAULT NULL;
