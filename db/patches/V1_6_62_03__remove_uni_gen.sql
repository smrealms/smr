-- Remove the 1.2 Universe Generator admin tool
DELETE FROM permission WHERE permission_id = 2;

-- Remove this permission from any admins who may have it
DELETE FROM account_has_permission WHERE permission_id = 2;
