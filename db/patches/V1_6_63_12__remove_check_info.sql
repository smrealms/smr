-- We are removing the 'Check Info' admin permission
DELETE FROM account_has_permission WHERE permission_id='11';
