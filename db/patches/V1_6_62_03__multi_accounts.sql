-- Rename `account.account_id` to `account.login_id`
ALTER TABLE account CHANGE `account_id` `login_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT;

-- Add `account_link_login` table
CREATE TABLE account_link_login (
  `account_id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `login_id` smallint(6) unsigned NOT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1;

-- Fill in `account_link_login` from the rows of `account`,
-- setting account_id = login_id.
INSERT INTO account_link_login (account_id, login_id)
SELECT login_id, login_id as account_id FROM account;
