-- We are removing the 'Show Map' admin permission
DELETE FROM account_has_permission WHERE permission_id='13';
