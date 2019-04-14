-- We are removing the unused 'Search for Keywords' admin tool.
DROP TABLE mb_exceptions, mb_keywords;
DELETE FROM account_has_permission WHERE permission_id='15';
