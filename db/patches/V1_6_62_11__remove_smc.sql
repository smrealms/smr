-- We are removing the 'Create SMC File' admin permission
DELETE FROM account_has_permission WHERE permission_id='14';

-- The MGU and SMC type ID's are not used in the code
ALTER TABLE location_type DROP COLUMN smc_type_id, DROP COLUMN mgu_id;
