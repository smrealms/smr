-- Rename `multi_checking_cookie.use` column to `shared` because "use"
-- is a reserved SQL keyword and our query wrappers don't all support
-- backtick escaping properly.
ALTER TABLE `multi_checking_cookie` RENAME COLUMN `use` TO `shared`;
