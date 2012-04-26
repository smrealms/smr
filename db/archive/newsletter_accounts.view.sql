CREATE OR REPLACE VIEW newsletter_accounts AS
SELECT account_id, email, first_name, last_name FROM smr_new.account a
WHERE validated = 'TRUE' AND email NOT IN ('noone@smrealms.de', 'NPC@smrealms.de') AND NOT EXISTS(SELECT account_id FROM smr_new.account_is_closed WHERE account_id = a.account_id)
UNION
SELECT 100000+account_id account_id, email, first_name, last_name FROM smr_classic.account b
WHERE validated = 'TRUE' AND email NOT IN ('noone@smrealms.de', 'NPC@smrealms.de') AND NOT EXISTS(SELECT account_id FROM smr_classic.account_is_closed WHERE account_id = b.account_id) AND NOT EXISTS(SELECT email FROM smr_new.account WHERE email=b.email)
UNION
SELECT 200000+account_id account_id, email, first_name, last_name FROM smr_12.account c
WHERE validated = 'TRUE' AND email NOT IN ('noone@smrealms.de', 'NPC@smrealms.de') AND NOT EXISTS(SELECT account_id FROM smr_12.account_is_closed WHERE account_id = c.account_id) AND NOT EXISTS(SELECT email FROM smr_new.account WHERE email=c.email) AND NOT EXISTS(SELECT email FROM smr_classic.account WHERE email=c.email)