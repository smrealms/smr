-- We are removing the 'Password Check' admin permission
DELETE FROM account_has_permission WHERE permission_id='10';
